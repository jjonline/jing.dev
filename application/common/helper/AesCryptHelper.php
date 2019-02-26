<?php
/**
 * 数据加解密
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-12-30 22:28
 * @file AesCryptHelper.php
 */

namespace app\common\helper;

use think\facade\Config;

class AesCryptHelper
{
    /**
     * @var string 加密key
     */
    public static $key;

    /**
     * 从配置库读取加解密key
     * @return mixed|string
     */
    public static function getCryptKey()
    {
        if (empty(self::$key)) {
            self::$key = Config::get('local.aes_crypt_key', '123456');
            self::$key = trim(self::$key);
        }
        return self::$key;
    }

    /**
     * 加密
     * @param $str
     * @param $localIV
     * @return string
     */
    public static function encrypt($str, $localIV = '1234567890123456')
    {
        $data = openssl_encrypt($str, 'AES-128-CBC', self::getCryptKey(), OPENSSL_RAW_DATA, $localIV);
        return base64_encode($data);
    }

    /**
     * 解密
     * @param $str
     * @param $localIV
     * @return string
     */
    public static function decrypt($str, $localIV = '1234567890123456')
    {
        $str = base64_decode($str);
        return openssl_decrypt($str, 'AES-128-CBC', self::getCryptKey(), OPENSSL_RAW_DATA, $localIV);
    }
}
