<?php
/**
 * 常用表单过滤和检测函数
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2017-08-03
 *
 */

namespace app\common\helper;

class FilterValidHelper
{

    /**
     * 检测传入的变量是否为合法邮箱 提供两种方法 可选内置fliter函数
     * 默认正则[邮箱用户名(即@符号之前的部分)构成部分为数字、字母、下划线、中划线和点均可，且开头必须是数字或字母]
     * @param  string $mail
     * @return boolean
     */
    public static function is_mail_valid($mail)
    {
        # PHP内置filter_var方式较为宽泛 不予采用
        /* !"#$%&'*+-/0123456789=?@ABCDEFGHIJKLMNOPQRSTUVWXYZ^_ `abcdefghijklmnopqrstuvwxyz{|}~ 的类型均正确
         也就是说 这种格式的邮箱 JJon#?`!#$%&'*+-/line@JJonline.Cn 也会被filter_var认为是合法邮箱 不符合人类认知 暂不采用
         详见：http://www.cs.tut.fi/~jkorpela/rfc/822addr.html
        */
        #return !!filter_var($mail,FILTER_VALIDATE_EMAIL);
        #正则方式 '/^\w+(?:[-+.]\w+)*@\w+(?:[-.]\w+)*\.\w+(?:[-.]\w+)*$/' 邮箱域名顶级后缀至少两个字符
        return preg_match('/^\w+(?:[-+.]\w+)*@\w+(?:[-.]\w+)*\.\w{2,}$/', $mail) === 1;
    }

    /**
     * 检测传入的变量是否为天朝手机号
     * @param  mixed $phone
     * @return boolean
     */
    public static function is_phone_valid($phone)
    {
        #Fixed 171 170x
        #详见：http://digi.163.com/15/0812/16/B0R42LSH00162OUT.html
        #2017-8-13新增196、198、199号段 http://www.miinac.gov.cn/components/Notice.action?doType=view&id=150951658150000389527
        return preg_match('/^13[\d]{9}$|14^[0-9]\d{8}$|^15[0-9]\d{8}$|^166\d{8}$|^170[015789]\d{7}$|^171[89]\d{7}$|^17[345678]\d{8}$|^18[0-9]\d{8}$|^198\d{8}$|^199\d{8}$/', $phone) === 1;
    }

    /**
     * 检测Url是否为合法的http或https链接
     * --------
     * 1、仅检测http、https打头的网址字符串
     * 2、网址中可带端口号
     * 3、网址中可带get变量
     * 4、网址中可带锚点
     * --------
     * @param  mixed $url
     * @return boolean
     */
    public static function is_url_valid($url)
    {
        return preg_match('/^http[s]?:\/\/(?:(?:[0-9]{1,3}\.){3}[0-9]{1,3}|(?:[0-9a-z_!~*\'()-]+\.)*(?:[0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(?::[0-9]{1,4})?(?:(?:\/\?)|(?:\/[0-9a-zA-Z_!~\*\'\(?:\)\.;\?:@&=\+\$,%#-\/]*)?)$/i', $url) === 1;
    }

    /**
     * 检测传入的变量是否为一个合法的账户id
     * ---------
     * 提供两种方法:函数方法和正则方法
     * 默认数字ID长度为4至11位
     * ---------
     * @param  mixed $uid       待检测的数字ID
     * @param  int   $minLength 允许的uid最短位数 默认4
     * @param  int   $maxLength 允许的uid最长位数 默认11
     * @return boolean
     */
    public static function is_uid_valid($uid, $minLength = 4, $maxLength = 11)
    {
        #正则方式
        return preg_match('/^[1-9]\d{'.($minLength - 1).','.($maxLengt - 1).'}$/', $uid) === 1;
        #函数方式 可能未编译ctype扩展不存在ctype_digit内置函数
        // return strlen($uid)>=$minLength && strlen($uid)<=$maxLength && ctype_digit((string)$uid);
    }

    /**
     * 检测传入的变量是否为一个合法的账户密码
     * ---------
     * 1、必须同时包含数字和字母
     * 2、通过第二个参数指定最小长度，默认值6
     * 3、通过第三个可选参数指定最大长度，默认值18
     * ---------
     * @param  string $password 需要被判断的字符串
     * @param  int $minLength 允许的账户密码最短位数 默认6
     * @param  int $maxLength 允许的账户密码最长位数 默认16
     * @return boolean
     */
    public static function is_password_valid($password, $minLength = 6, $maxLength = 18)
    {
        if (strlen($password) > $maxLength || strlen($password) < $minLength) {
            return false;
        }
        return preg_match('/\d{1,'.$maxLength.'}/', $password) === 1 && preg_match('/[a-zA-Z]{1,'.$maxLength.'}/', $password) === 1;
    }

    /**
     * 检查字符串是否是UTF8编码下的中文
     * @param  string $string 字符串
     * @return boolean
     */
    public static function is_chinese_valid($str)
    {
        return preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str) === 1;
    }

    /**
     * 检查字符串是否是UTF8编码
     * @param  string $string 字符串
     * @return boolean
     */
    public static function is_utf8_valid($str)
    {
        $c    = 0;
        $b    = 0;
        $bits = 0;
        $len  = strlen($str);
        for ($i = 0; $i<$len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                } elseif ($c >= 252) {
                    $bits=6;
                } elseif ($c >= 248) {
                    $bits=5;
                } elseif ($c >= 240) {
                    $bits=4;
                } elseif ($c >= 224) {
                    $bits=3;
                } elseif ($c >= 192) {
                    $bits=2;
                } else {
                    return false;
                }
                if (($i+$bits) > $len) {
                    return false;
                }
                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bits--;
                }
            }
        }
        return true;
    }

    /**
     * 检测传入的变量是否为一个合法的天朝身份证号（15位、18位兼容）
     * @param  mixed $citizen_id
     * @return bool | array
     */
    public static function is_citizen_id_valid($citizen_id)
    {
        $id                 =   strtoupper($citizen_id);
        if (!(preg_match('/^\d{17}(\d|X)$/', $id) || preg_match('/^\d{15}$/', $id))) {
            return false;
        }
        # 15位老号码转换为18位
        $Wi                 =   array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
        $Ai                 =   array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $cardNoSum          =   0;
        if (strlen($id)==16) {
            $id             =   substr(0, 6).'19'.substr(6, 9);
            for ($i = 0; $i < 17; $i++) {
                $cardNoSum +=   substr($id, $i, 1) * $Wi[$i];
            }
            $seq            =   $cardNoSum % 11;
            $id             =   $id.$Ai[$seq];
        }
        # 效验18位身份证最后一位字符的合法性
        $cardNoSum          =   0;
        $id17               =   substr($id, 0, 17);
        $lastString         =   substr($id, 17, 1);
        for ($i = 0; $i < 17; $i++) {
            $cardNoSum     +=   substr($id, $i, 1) * $Wi[$i];
        }
        $seq                =   $cardNoSum % 11;
        $realString         =   $Ai[$seq];
        # 最后一位效验失败 不是合法身份证号
        if ($lastString     !=   $realString) {
            return false;
        }
        # 地域仅能精确到省、自治区信息，再往下就需大量数据支撑才能精确
        $oProvice   = array(
            11 => "北京",
            12 => "天津",
            13 => "河北",
            14 => "山西",
            15 => "内蒙古",
            21 => "辽宁",
            22 => "吉林",
            23 => "黑龙江",
            31 => "上海",
            32 => "江苏",
            33 => "浙江",
            34 => "安徽",
            35 => "福建",
            36 => "江西",
            37 => "山东",
            41 => "河南",
            42 => "湖北 ",
            43 => "湖南",
            44 => "广东",
            45 => "广西",
            46 => "海南",
            50 => "重庆",
            51 => "四川",
            52 => "贵州",
            53 => "云南",
            54 => "西藏",
            61 => "陕西",
            62 => "甘肃",
            63 => "青海",
            64 => "宁夏",
            65 => "新疆",
            71 => "台湾",
            81 => "香港",
            82 => "澳门",
            91 => "国外"
        );
        $Province   = substr($id, 0, 2);
        $BirthYear  = substr($id, 6, 4);
        $BirthMonth = substr($id, 10, 2);
        $BirthDay   = substr($id, 12, 2);
        $Sex        = substr($id, 16, 1) % 2 ;//男1 女0
        # 省份数据
        if (!isset($oProvice[$Province])) {
            return false;
        }
        # 年份超限
        if ($BirthYear > 2078 || $BirthYear < 1900) {
            return false;
        }
        # 年月日是否合法
        $RealDate           =   strtotime($BirthYear.'-'.$BirthMonth.'-'.$BirthDay);
        if (date('Y', $RealDate) != $BirthYear || date('m', $RealDate) != $BirthMonth || date('d', $RealDate) != $BirthDay) {
            return false;
        }
        # 效验成功 返回关联数组，便于从身份证号中提取基本信息 boolean判断为true
        return array('id'=>$id,'location'=>$oProvice[$Province],'Y'=>$BirthYear,'m'=>$BirthMonth,'d'=>$BirthDay,'sex'=>$Sex);
    }
}
