<?php
/**
 * 阿里云Oss存储引擎
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:11
 * @file OssStorage.php
 */

namespace app\common\storage;

use think\facade\Config;

class OssStorage extends BaseStorage
{
    /**
     * @var string 阿里oss区域1
     */
    protected $endpoint_frontend;
    /**
     * @var string 阿里oss区域2
     */
    protected $endpoint_safe;

    public function __construct()
    {
        // 初始化各项配置参数--无鉴权资源
        $this->access_key_frontend    = Config::get('attachment.oss.frontend.access_key_id');
        $this->access_secret_frontend = Config::get('attachment.oss.frontend.access_key_secret');
        $this->domain_frontend        = Config::get('attachment.oss.frontend.domain');
        $this->bucket_frontend        = Config::get('attachment.oss.frontend.bucket');
        $this->endpoint_frontend      = Config::get('attachment.oss.frontend.endpoint');
        // 初始化各项配置参数--有鉴权安全资源
        $this->access_key_frontend    = Config::get('attachment.oss.safe.access_key_id');
        $this->access_secret_frontend = Config::get('attachment.oss.safe.access_key_secret');
        $this->domain_frontend        = Config::get('attachment.oss.safe.domain');
        $this->bucket_frontend        = Config::get('attachment.oss.safe.bucket');
        $this->endpoint_frontend      = Config::get('attachment.oss.safe.endpoint');
    }

    public function put($local_dir, $remote_dir = '', $param = [])
    {
        // TODO: Implement get() method.
    }

    public function get($attachment)
    {
        // TODO: Implement get() method.
    }

    public function all()
    {
        // TODO: Implement all() method.
    }
}
