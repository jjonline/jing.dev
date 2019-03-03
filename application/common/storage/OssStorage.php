<?php
/**
 * 阿里云Oss存储引擎
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:11
 * @file OssStorage.php
 */

namespace app\common\storage;

use app\common\helper\AttachmentHelper;
use OSS\OssClient;
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
    /**
     * @var OssClient
     */
    protected $client_frontend;
    /**
     * @var OssClient
     */
    protected $client_safe;

    public function __construct()
    {
        // 初始化各项配置参数--无鉴权资源
        $this->access_key_frontend    = Config::get('attachment.oss.frontend.access_key_id');
        $this->access_secret_frontend = Config::get('attachment.oss.frontend.access_key_secret');
        $this->domain_frontend        = Config::get('attachment.oss.frontend.domain');
        $this->bucket_frontend        = Config::get('attachment.oss.frontend.bucket');
        $this->endpoint_frontend      = Config::get('attachment.oss.frontend.endpoint');
        // 初始化各项配置参数--有鉴权安全资源
        $this->access_key_safe    = Config::get('attachment.oss.safe.access_key_id');
        $this->access_secret_safe = Config::get('attachment.oss.safe.access_key_secret');
        $this->domain_safe        = Config::get('attachment.oss.safe.domain');
        $this->bucket_safe        = Config::get('attachment.oss.safe.bucket');
        $this->endpoint_safe      = Config::get('attachment.oss.safe.endpoint');
    }

    /**
     * OSS推送单个文件
     * @param array $attachment Db表键单条数据构成的数组
     * @return bool|void
     * @throws \OSS\Core\OssException
     */
    public function put($attachment)
    {
        if ($attachment['is_safe']) {
            $this->initSafe();
            $this->client_safe->uploadFile(
                $this->bucket_safe,
                $this->generateObject($attachment['file_path']),
                $this->generateFileDir($attachment['file_path'])
            );
        } else {
            $this->initFrontend();
            $this->client_frontend->uploadFile(
                $this->bucket_frontend,
                $this->generateObject($attachment['file_path']),
                $this->generateFileDir($attachment['file_path'])
            );
        }
    }

    /**
     * 获取单个资源url
     * @param array $attachment
     * @return false|string
     * @throws \OSS\Core\OssException
     */
    public function get($attachment)
    {
        if ($attachment['is_safe']) {
            $expire_time = Config::get('attachment.attachment_expire_time', 1800);
            try {
                $this->initSafe();
                return $this->client_safe->signUrl(
                    $this->bucket_safe,
                    $this->generateObject($attachment['file_path']),
                    $expire_time
                );
            } catch (\Throwable $e) {
                /**
                 * oss安全资源链接获取失败，则降级成本地加密资源
                 */
                return AttachmentHelper::generateLocalSafeUrl($attachment['id']);
            }
        }
        // 非安全资源，直接构造
        return $this->getOssDomain($attachment['is_safe']).$attachment['file_path'];
    }

    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * 获取oss域名
     * @param bool $is_safe
     * @return string
     */
    private function getOssDomain($is_safe = false)
    {
        if ($is_safe) {
            $is_ssl = Config::get('attachment.oss.safe.is_ssl', false);
            if (empty($this->domain_safe)) {
                return ($is_ssl ? 'https://' : 'http://').$this->bucket_safe.'.'.$this->endpoint_safe;
            }
            return rtrim($this->domain_safe, '/');
        } else {
            $is_ssl = Config::get('attachment.oss.frontend.is_ssl', false);
            if (empty($this->domain_frontend)) {
                return ($is_ssl ? 'https://' : 'http://').$this->bucket_frontend.'.'.$this->endpoint_frontend;
            }
            return ($is_ssl ? 'https://' : 'http://').rtrim($this->domain_frontend, '/');
        }
    }

    /**
     * 资源本地路径生成oss的object规则
     * @param $file_path
     * @return string
     */
    protected function generateObject($file_path)
    {
        return ltrim($file_path, '/');
    }

    /**
     * Db里的file_path生成相对路径
     * @param $file_path
     * @return string
     */
    protected function generateFileDir($file_path)
    {
        return '.'.$file_path;
    }

    /**
     * 初始化前台oss存储引擎
     * 异常继承至 \Exception 调用方可以catch到
     * @return $this
     * @throws \OSS\Core\OssException
     */
    protected function initFrontend()
    {
        if (is_null($this->client_frontend)) {
            $this->client_frontend = new OssClient(
                $this->access_key_frontend,
                $this->access_secret_frontend,
                $this->domain_frontend ?: $this->endpoint_frontend,
                !empty($this->domain_frontend) // isCname -- 自定义域名情况下属于cname
            );
            $is_ssl = Config::get('attachment.oss.frontend.is_ssl', false);
            $this->client_frontend->setUseSSL($is_ssl);
        }
        return $this;
    }

    /**
     * 初始化安全需加密oss存储引擎
     * 异常继承至 \Exception 调用方可以catch到
     * @return $this
     * @throws \OSS\Core\OssException
     */
    protected function initSafe()
    {
        if (is_null($this->client_safe)) {
            $this->client_safe = new OssClient(
                $this->access_key_safe,
                $this->access_secret_safe,
                $this->domain_safe ?: $this->endpoint_safe,
                !empty($this->domain_safe) // isCname -- 自定义域名情况下属于cname
            );
            $is_ssl = Config::get('attachment.oss.safe.is_ssl', false);
            $this->client_safe->setUseSSL($is_ssl);
        }
        return $this;
    }
}
