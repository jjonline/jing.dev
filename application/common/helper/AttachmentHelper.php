<?php
/**
 * 附件资源帮助函数方法
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-13 15:28
 * @file AttachmentHelper.php
 */

namespace app\common\helper;

use app\common\service\AttachmentService;
use think\facade\Config;

class AttachmentHelper
{
    /**
     * 通过附件ID获取附件资源网址
     * @param string $attachment_id attachment表主键ID
     * @return string
     */
    public static function getAttachmentPathById($attachment_id)
    {
        $attachment = app(AttachmentService::class)->getAttachmentById($attachment_id);
        return empty($attachment['file_path']) ? '' : $attachment['file_path'];
    }

    /**
     * 通过附件ID数组获取资源外网数组
     * @param string $attachment_id attachment表主键ID
     * @return []
     */
    public static function getAttachmentByIds($attachment_ids)
    {
        return app(AttachmentService::class)->getAttachmentByIds($attachment_ids);
    }

    /**
     * 资源ID生成本地私有访问Url
     * @param string $attachment_id
     * @return string
     */
    public static function generateLocalSafeUrl($attachment_id)
    {
        $expire_time        = Config::get('attachment.attachment_expire_time', 1800);
        $auth_key           = Config::get('local.auth_key');
        $param              = [];
        $param['expire_in'] = time() + $expire_time;
        // 生成ID的加密字符串 半小时有效
        $param['access_key'] = self::transferEncrypt(
            $attachment_id,
            $auth_key,
            $expire_time
        );
        return '/manage/common/attachment?'.http_build_query($param);
    }

    /**
     * 可逆的字符串加密和解密方法-discuz中的方法
     * 该函数密文的安全性主要在于密匙并且是可逆的
     *
     * 该可逆加密主要用于一些需要时间有效性效验的数据交换中，加密强度很弱
     * 若用于密码处理建议使用password_hash和password_verfiy
     *
     *                       ###警告###
     * ********过期时间参数并不意味着过期后就无法解密出明文了********
     *
     * @param  string  $string    明文或密文
     * @param  boolean $isEncode  是否解密，true则为解密 false默认表示加密字符串
     * @param  string  $key       密钥 默认jjonline
     * @param  int     $expiry    密钥有效期 单位：秒 默认0为永不过期
     * @return string 空字符串表示解密失败|密文已过期
     */
    public static function reversibleCrypt($string, $isEncode = false, $key = 'jjonline', $expiry = 0)
    {
        $ckey_length            =   4;
        // 密匙
        $key                    =   md5($key ? $key : 'jjonline');
        // 密匙a会参与加解密
        $keya                   =   md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb                   =   md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc                   =   $ckey_length
            ? ($isEncode ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey               =   $keya.md5($keya.$keyc);
        $key_length             =   strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string                 =   $isEncode
            ? base64_decode(substr($string, $ckey_length))
            : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length          =   strlen($string);
        $result                 =   '';
        $box                    =   range(0, 255);
        $rndkey                 =   array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i]         =   ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j                  =   ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp                =   $box[$i];
            $box[$i]            =   $box[$j];
            $box[$j]            =   $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a                  =   ($a + 1) % 256;
            $j                  =   ($j + $box[$a]) % 256;
            $tmp                =   $box[$a];
            $box[$a]            =   $box[$j];
            $box[$j]            =   $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result            .=   chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($isEncode) {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0
                || substr($result, 0, 10) - time() > 0)
                && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生成不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 对时间有效性的数据进行可逆的加密，对reversible_crypt方法的可识别封装
     * @param  string  $string 待加密字符串
     * @param  string  $key    加密秘钥
     * @param  integer $expiry 加密的密文失效时间，单位：秒 0默认表示：永不失效
     * @return string
     */
    public static function transferEncrypt($string, $key = 'jjonline', $expiry = 0)
    {
        return self::reversibleCrypt($string, false, $key, $expiry);
    }

    /**
     * 对时间有效性的数据进行效验并解密
     * 由reversible_encrypt加密的密文进行解密
     *
     *                       ###警告###
     * ********过期时间参数并不意味着过期后就无法解密出明文了********
     *
     * 密文过期并不意味着无法解密出明文，只是在密文中加入了一种过期效验机制由方法体自动完成效验罢了
     *
     * @param  string $string 密文字符串
     * @param  string $key    解密秘钥
     * @return string
     */
    public static function transferDecrypt($string, $key = 'jjonline')
    {
        return self::reversibleCrypt($string, true, $key);
    }
}
