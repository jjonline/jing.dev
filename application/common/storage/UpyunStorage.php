<?php
/**
 * 又拍云存储引擎
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-03 17:30
 * @file UpyunStorage.php
 */

namespace app\common\storage;

use think\Exception;
use think\facade\Config;
use Upyun\Config as UpyunConfig;
use Upyun\Upyun;

class UpyunStorage extends BaseStorage
{
    /**
     * @var Upyun
     */
    protected $client_frontend;
    /**
     * @var Upyun
     */
    protected $client_safe;
    /**
     * @var string
     */
    protected $token_key;

    public function __construct()
    {
        // 初始化各项配置参数--无鉴权资源
        $this->access_key_frontend    = Config::get('attachment.upyun.frontend.operator_name');
        $this->access_secret_frontend = Config::get('attachment.upyun.frontend.operator_pwd');
        $this->domain_frontend        = Config::get('attachment.upyun.frontend.domain');
        $this->bucket_frontend        = Config::get('attachment.upyun.frontend.service_name');
        // 初始化各项配置参数--有鉴权安全资源
        $this->access_key_safe    = Config::get('attachment.upyun.safe.operator_name');
        $this->access_secret_safe = Config::get('attachment.upyun.safe.operator_pwd');
        $this->domain_safe        = Config::get('attachment.upyun.safe.domain');
        $this->bucket_safe        = Config::get('attachment.upyun.safe.service_name');
        $this->token_key          = Config::get('attachment.upyun.safe.token_key');
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
            $result = $this->client_safe->write(
                $this->generateObject($attachment['file_path']),
                $this->generateFileResource($attachment['file_path'])
            );
            if (false === $result) {
                throw new Exception('又拍云文件同步出错');
            }
            return true;
        } else {
            $this->initFrontend();
            $result = $this->client_frontend->write(
                $this->generateObject($attachment['file_path']),
                $this->generateFileResource($attachment['file_path'])
            );
            if (false === $result) {
                throw new Exception('又拍云文件同步出错');
            }
            return true;
        }
    }

    /**
     * 获取资源访问url
     * @param array $attachment
     * @return string
     */
    public function get($attachment)
    {
        $file_path = $this->getDomain($attachment['is_safe']).$attachment['file_path'];
        if ($attachment['is_safe']) {
            // 不存在加密失败降级的可能
            return $file_path.'?_upt='.$this->generateToken($attachment['file_path']);
        }
        return $file_path;
    }

    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * 生成_upt加密参数
     * @param string $file_path 原始文件路径，开头为斜杠
     * @return string
     */
    protected function generateToken($file_path)
    {
        $expire_time = Config::get('attachment.attachment_expire_time', 1800);
        $e_time      = time() + $expire_time;
        // MD5( secret & etime & URI ) 有&符号参与计算
        $token = md5($this->token_key .'&'. $e_time .'&'. $file_path);
        $_upt  = substr($token, 12, 8); // 截取中间8位字符
        return $_upt.$e_time;
    }

    /**
     * 获取七牛资源带访问协议的域名
     * @param bool $is_safe
     * @return string
     */
    protected function getDomain($is_safe)
    {
        if ($is_safe) {
            $is_ssl = Config::get('attachment.upyun.safe.is_ssl', false);
            return ($is_ssl ? 'https://' : 'http://').rtrim($this->domain_safe, '/');
        } else {
            $is_ssl = Config::get('attachment.upyun.frontend.is_ssl', false);
            return ($is_ssl ? 'https://' : 'http://').rtrim($this->domain_frontend, '/');
        }
    }

    /**
     * 资源本地路径生成object规则，即远程存储路径规则
     * @param $file_path
     * @return string
     */
    protected function generateObject($file_path)
    {
        return $file_path;
    }

    /**
     * 读取文件流
     * @param $file_path
     * @return bool|resource
     * @throws Exception
     */
    protected function generateFileResource($file_path)
    {
        $res = fopen('.'.$file_path, 'r');
        if (false === $res) {
            throw new Exception('又拍云文件同步读取文件流出错');
        }
        return $res;
    }

    /**
     * 初始化前端资源存储引擎句柄
     */
    protected function initFrontend()
    {
        if (!empty($this->client_frontend)) {
            return;
        }
        $this->client_frontend = new Upyun(new UpyunConfig(
            $this->bucket_frontend,
            $this->access_key_frontend,
            $this->access_secret_frontend
        ));
    }

    /**
     * 初始化安全资源存储引擎句柄
     */
    protected function initSafe()
    {
        if (!empty($this->client_safe)) {
            return;
        }
        $this->client_safe = new Upyun(new UpyunConfig(
            $this->bucket_safe,
            $this->access_key_safe,
            $this->access_secret_safe
        ));
    }
}
