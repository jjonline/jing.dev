<?php
/**
 * 字符串、UUID、GUID生成器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04
 *
 */

namespace app\common\helper;


class GenerateHelper
{

    /**
     * 生成36位UUID
     * @param bool $isUpper 是否转换为大写字母，默认不转换
     * @return string
     */
    public static function uuid($isUpper = false)
    {
        list($usec, $sec) = explode(" ", microtime(false));
        $usec             = (string)($usec * 10000000);
        $timestamp        = bcadd(bcadd(bcmul($sec, "10000000"), (string)$usec), "621355968000000000");
        $ticks            = bcdiv($timestamp, 10000);
        $maxUint          = 4294967295;
        $high             = bcdiv($ticks, $maxUint) + 0;
        $low              = bcmod($ticks, $maxUint) - $high;
        $highBit          = (pack("N*", $high));
        $lowBit           = (pack("N*", $low));
        $guid             = str_pad(dechex(ord($highBit[2])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($highBit[3])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[0])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[1])), 2, "0", STR_PAD_LEFT) . "-" . str_pad(dechex(ord($lowBit[2])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[3])), 2, "0", STR_PAD_LEFT) . "-";
        $chars = "abcdef0123456789";
        for ($i = 0; $i < 4; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }
        $guid .= "-";
        for ($i = 0; $i < 4; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }
        $guid .= "-";
        for ($i = 0; $i < 12; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }
        return $isUpper ? strtoupper($guid) : $guid;
    }

    /**
     * 生成36GUID
     * @param bool $opt 是否完整GUID格式，默认否 即不带大括号
     * @return string
     */
    public static function guid($opt = false)
    {
        // 获取服务器IP作为uuid一个变量因子
        $server_ip   = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        // PHP7.1下可使用内置生成session_id唯一性相当好的方法生成一个session_id使用
        $session_id  = function_exists('session_create_id') ? session_create_id() : uniqid($server_ip.mt_rand());
        $charid      = md5(uniqid($session_id.mt_rand().$server_ip, true));
        $hyphen      = chr(45); // "-"
        $left_curly  = $opt ? chr(123) : ""; //  "{"
        $right_curly = $opt ? chr(125) : ""; //  "}"
        $uuid        = $left_curly
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . $right_curly;
        return $uuid;
    }

    /**
     * 生成32位商户系统内部的订单号
     * @return string
     */
    public static function tradeNo()
    {
        return strtoupper(md5(uniqid(mt_rand(), true)));
    }

    /**
     * 生成16位唯一ID
     * @return string
     */
    public static function tradeNo16()
    {
        return uniqid(mt_rand(100, 999));
    }

    /**
     * 产生随机字符串,默认32位
     * @param int $length
     * @return string
     */
    public static function makeNonceStr($length = 32)
    {
        $chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str    = '';
        $strLen = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, $strLen), 1);
        }
        return $str;
    }
}
