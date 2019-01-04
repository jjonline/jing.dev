<?php
/**
 * AES加解密
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-12-30 22:28
 * @file AesCryptHelper.php
 */

namespace app\common\helper;

use app\common\model\SiteConfig;

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
            self::$key = (new SiteConfig())->getSitConfigValueByKey('crypt_key');
        }
        return self::$key;
    }

    /**
     * 加密
     * @param $str
     * @param $localIV
     * @param $encryptKey
     * @return string
     */
    public static function encrypt($str, $localIV = '')
    {
        return openssl_encrypt($str, 'AES-128-CBC', self::getCryptKey(),0, $localIV);
    }

    /**
     * 解密
     * @param $str
     * @param $localIV
     * @param $encryptKey
     * @return string
     */
    public static function decrypt($str, $localIV = '')
    {
        return openssl_decrypt($str, 'AES-128-CBC', self::getCryptKey(), 0, $localIV);
    }
}
