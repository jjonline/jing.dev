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
    /**
     * @var BaseStorage 实际底层操作对象方法实例
     */
    private $instance;

    /**
     * Storage constructor.
     * @throws Exception
     */
    public function __construct()
    {
        // 获取配置参数中的存储引擎
        $engine  = Config::get('attachment.attachment_engine', 'local');

        // 引擎实例化
        $class   = StringHelper::toUcCamelCase($engine).'Storage';
        $storage = __NAMESPACE__."\\".$class;
        if (!class_exists($storage)) {
            throw new Exception('尚未实现的存储引擎：'.$engine);
        }
        $this->instance = app($storage);
    }

    /**
     * 往存储引擎推送单一文件
     * @param array $attachment 单个资源的信息数组
     * @return bool 推送成功true，推送失败false
     */
    public function put($attachment)
    {
        return $this->instance->put($attachment);
    }

    /**
     * 从存储引擎获取单一文件前台可访问完整资源url
     * @param array $attachment 单个资源的信息数组
     * @return string|false 获取成功字符串，获取失败false
     */
    public function get($attachment)
    {
        return $this->instance->get($attachment);
    }

    /**
     * 从存储引擎分页获取所有资源
     * @return array
     */
    public function all()
    {
        return $this->instance->all();
    }
}
