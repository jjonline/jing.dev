<?php
/**
 * 存储引擎抽象类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:40
 * @file BaseStorage.php
 */

namespace app\common\storage;

abstract class BaseStorage
{
    /**
     * @var string 授权key1
     */
    protected $access_key_frontend;
    /**
     * @var string 授权secret1
     */
    protected $access_secret_frontend;
    /**
     * @var string 存储引擎访问域，带协议
     */
    protected $domain_frontend;
    /**
     * @var string 存储bucket-标识符等标记1
     */
    protected $bucket_frontend;
    /**
     * @var string 授权key2
     */
    protected $access_key_safe;
    /**
     * @var string 授权secret2
     */
    protected $access_secret_safe;
    /**
     * @var string 存储引擎访问域，带协议2
     */
    protected $domain_safe;
    /**
     * @var string 存储bucket-标识符等标记2
     */
    protected $bucket_safe;

    /**
     * 往存储引擎推送单一文件
     * @param array $attachment 单个资源的信息数组
     * @return bool 推送成功true，推送失败false
     * @throws \Exception
     */
    abstract public function put($attachment);

    /**
     * 从存储引擎获取单一文件前台可访问完整资源url
     * @param array $attachment 单个资源的信息数组
     * @return string 获取成功字符串，获取失败抛出异常
     * @throws \Exception
     */
    abstract public function get($attachment);

    /**
     * 从存储引擎分页获取所有资源
     * @return array
     */
    abstract public function all();
}
