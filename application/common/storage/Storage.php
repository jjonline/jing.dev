<?php
/**
 * 外部存储引擎统一封装
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:12
 * @file Storage.php
 */

namespace app\common\storage;

use app\common\helper\StringHelper;
use think\Exception;
use think\facade\Config;

class Storage
{
    const LOCAL = 'local';

    /**
     * @var array 实际底层操作对象方法实例对象数组
     */
    private $instance;
    /**
     * @var BaseStorage 前端使用的存储引擎单一对象
     */
    private $instance_frontend;

    /**
     * 往1个或多个存储引擎推送单一文件
     * @param array $attachment 单个资源的信息数组
     * @return bool
     * @throws \Exception
     */
    public function put($attachment)
    {
        $this->initStorageEngine();
        $ret = [];
        foreach ($this->instance as $engine => $instance) {
            /**
             * 当有多个存储引擎推送时，只要某一个推送出现异常，整个推送就会被终止
             * 若需要继续推送，修改此方法，将异常catch住
             * @var BaseStorage $instance
             */
            $ret[$engine] = $instance->put($attachment);
        }
        return $this->parsePutResult($ret);
    }

    /**
     * 从存储引擎获取单一文件前台可访问完整资源url
     * ---
     * 获取加密资源异常时各底层驱动做了自动降级为local的加密资源链接的处理
     * ---
     * @param array $attachment 单个资源的信息数组
     * @return string 获取成功字符串，获取失败或出现错误抛出异常
     * @throws \Exception
     */
    public function get($attachment)
    {
        $this->initFrontendEngine();
        return $this->instance_frontend->get($attachment);
    }

    /**
     * todo 暂不实现
     * 从存储引擎分页获取所有资源
     * @return array
     */
    public function all()
    {
        return [];
    }

    /**
     * 解析1个或多个存储引擎put资源后的整体状态
     * ---
     * 全部成功才整体成功，只要有1个失败就整体失败
     * ---
     * @param array $result
     * @return bool
     */
    protected function parsePutResult(array $result = [])
    {
        $_result = $result;
        if (count($result) != count(array_filter($_result))) {
            return false;
        }
        return true;
    }

    /**
     * 实例化一个或多个存储引擎
     * @throws Exception
     */
    protected function initStorageEngine()
    {
        if (!empty($this->instance)) {
            return;
        }
        // 1个或多个存储引擎实例化
        $engines = $this->parseStorageEngine();
        foreach ($engines as $engine) {
            if (self::LOCAL == $engine) {
                continue;
            }
            $this->instance[$engine] = $this->initOneEngine($engine);
        }
    }

    /**
     * lazy初始化前台引擎，即用到的时候才去实例化
     * @throws Exception
     */
    protected function initFrontendEngine()
    {
        if (!empty($this->instance_frontend)) {
            return;
        }
        // 分析前台引擎配置，若无则使用存储引擎的第一个值
        // 若存储引擎依然为空则使用local
        $_engine = Config::get('attachment.attachment_frontend_use', null);
        if (empty($_engine)) {
            $engines = $this->parseStorageEngine();
            $_engine = $engines[0];
        }

        // 检查是否已有实例化对象并实例化前端存储引擎
        if (!empty($this->instance[$_engine])) {
            $this->instance_frontend = $this->instance[$_engine];
        } else {
            $this->instance_frontend = $this->initOneEngine($_engine);
        }
    }

    /**
     * 从配置文件解析存储引擎
     * @return array
     */
    protected function parseStorageEngine()
    {
        // 获取配置参数中的存储引擎，1个或多个
        $_engines = Config::get('attachment.attachment_engine', self::LOCAL);

        // 半角逗号分隔字符串解析成数组
        if (is_string($_engines)) {
            $_engines = explode(',', $_engines);
        }

        // 去除空值和重复值
        return array_unique(array_filter($_engines));
    }

    /**
     * 引擎统一检测后实例化方法
     * @param $engine
     * @return mixed|\think\App
     * @throws Exception
     */
    protected function initOneEngine($engine)
    {
        $class   = StringHelper::toUcCamelCase($engine).'Storage';
        $storage = __NAMESPACE__."\\".$class;
        if (!class_exists($storage)) {
            throw new Exception('尚未实现的存储引擎：'.$engine);
        }
        return app($storage);
    }
}
