<?php
/**
 * 七牛云存储引擎
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-03 14:16
 * @file QiniuStorage.php
 */

namespace app\common\storage;

use app\common\helper\AttachmentHelper;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Exception;
use think\facade\Config;

class QiniuStorage extends BaseStorage
{
    /**
     * @var Auth
     */
    protected $auth_frontend;
    /**
     * @var UploadManager
     */
    protected $client_frontend;
    /**
     * @var string 安全资源上传token
     */
    protected $client_frontend_token;

    /**
     * @var Auth
     */
    protected $auth_safe;
    /**
     * @var UploadManager
     */
    protected $client_safe;
    /**
     * @var string 前端资源上传token
     */
    protected $client_safe_token;

    public function __construct()
    {
        // 初始化各项配置参数--无鉴权资源
        $this->access_key_frontend    = Config::get('attachment.qiniu.frontend.access_key_id');
        $this->access_secret_frontend = Config::get('attachment.qiniu.frontend.access_key_secret');
        $this->domain_frontend        = Config::get('attachment.qiniu.frontend.domain');
        $this->bucket_frontend        = Config::get('attachment.qiniu.frontend.bucket');
        // 初始化各项配置参数--有鉴权安全资源
        $this->access_key_safe    = Config::get('attachment.qiniu.safe.access_key_id');
        $this->access_secret_safe = Config::get('attachment.qiniu.safe.access_key_secret');
        $this->domain_safe        = Config::get('attachment.qiniu.safe.domain');
        $this->bucket_safe        = Config::get('attachment.qiniu.safe.bucket');
    }

    /**
     * 推送单个文件
     * @param array $attachment
     * @return bool
     * @throws \Exception
     */
    public function put($attachment)
    {
        if ($attachment['is_safe']) {
            $this->initSafe();
            list($result, $error) = $this->client_safe->putFile(
                $this->client_safe_token,
                $this->generateObject($attachment['file_path']),
                $this->generateFileDir($attachment['file_path'])
            );
            if (!empty($result)) {
                return true;
            }
            /**
             * 失败则$error对象可以获取到失败信息，抛出
             * @var \Qiniu\Http\Error $error IDE注解
             */
            throw new Exception($error->message(), $error->code());
        } else {
            $this->initFrontend();
            list($result, $error) = $this->client_frontend->putFile(
                $this->client_frontend_token,
                $this->generateObject($attachment['file_path']),
                $this->generateFileDir($attachment['file_path'])
            );
            if (!empty($result)) {
                return true;
            }
            /**
             * 失败则$error对象可以获取到失败信息，抛出
             * @var \Qiniu\Http\Error $error IDE注解
             */
            throw new Exception($error->message(), $error->code());
        }
    }

    /**
     * 获取七牛资源访问url
     * @param array $attachment
     * @return string
     */
    public function get($attachment)
    {
        $file_path = $this->getQiniuDomain($attachment['is_safe']).$attachment['file_path'];
        if ($attachment['is_safe']) {
            $expire_time = Config::get('attachment.attachment_expire_time', 1800);
            try {
                $this->initSafe();
                return $this->auth_safe->privateDownloadUrl(
                    $file_path,
                    $expire_time
                );
            } catch (\Throwable $e) {
                /**
                 * 安全资源链接获取失败，则降级成本地加密资源
                 */
                return AttachmentHelper::generateLocalSafeUrl($attachment['id']);
            }
        }
        return $file_path;
    }

    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * 获取七牛资源带访问协议的域名
     * @param bool $is_safe
     * @return string
     */
    protected function getQiniuDomain($is_safe)
    {
        if ($is_safe) {
            $is_ssl = Config::get('attachment.qiniu.safe.is_ssl', false);
            return ($is_ssl ? 'https://' : 'http://').rtrim($this->domain_safe, '/');
        } else {
            $is_ssl = Config::get('attachment.qiniu.frontend.is_ssl', false);
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
     * 初始化前端资源存储引擎句柄
     */
    protected function initFrontend()
    {
        if (!empty($this->client_frontend)) {
            return;
        }
        // 签名token 有效期1个小时
        $this->auth_frontend = new Auth(
            $this->access_key_frontend,
            $this->access_secret_frontend
        );
        $this->client_frontend_token = $this->auth_frontend->uploadToken($this->bucket_safe, null, 3600);

        // 上传句柄
        $this->client_frontend = new UploadManager();
    }

    /**
     * 初始化安全资源存储引擎句柄
     */
    protected function initSafe()
    {
        if (!empty($this->client_safe)) {
            return;
        }
        // 签名token 有效期1个小时
        $this->auth_safe = new Auth(
            $this->access_key_safe,
            $this->access_secret_safe
        );
        $this->client_safe_token = $this->auth_safe->uploadToken($this->bucket_safe, null, 3600);

        // 上传句柄
        $this->client_safe = new UploadManager();
    }
}
