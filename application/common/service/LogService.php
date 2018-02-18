<?php
/**
 * 用户动作日志记录服务层且使用单例（即使用Container容器类进行实例化）
 * 所有记录用户动作的代码只能调用该服务层中的方法，便于后续性能优化
 * ----
 * 1、主要调用logRecorder方法
 * 2、也可以调用save方法直接执行存储方法，大部分时候不需要调用save方法
 * ----
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:35
 * @file LogService.php
 */

namespace app\common\service;

use app\common\helper\GenerateHelper;
use app\common\helper\NumberHelper;
use app\common\model\Log;
use think\Container;
use think\facade\Session;
use think\Request;

class LogService
{
    /**
     * @var array 用户动作日志存储数组
     */
    private $LogData = array();
    /**
     * @var Log
     */
    public $Log;

    public function __construct(Log $log)
    {
        $this->Log = $log;
    }

    /**
     * 统一记录用户动作日志的对外方法
     * ---
     * 延迟记录，具体性能优化方法再行处理
     * ---
     * @param $data
     * @return bool
     */
    public function logRecorder($data = null)
    {
        $logData = $this->generateLog();
        // 处理额外数据
        if(is_scalar($data))
        {
            $data = $data.'';
        }elseif(is_array($data) || is_object($data)) {
            $data = serialize($data);
        }else {
            $data = '';
        }
        $logData['extra_data'] = $data;
        $this->LogData         = $logData;
        // 执行日志写入方法，性能优化修改save方法的实现逻辑即可
        return $this->save();
    }

    /**
     * @return bool
     */
    protected function save()
    {
        if(empty($this->LogData))
        {
            return false;
        }
        $result = !!$this->Log->isUpdate(false)->save($this->LogData);

        // TODO 直接存储日志方法代码实现，当前方法体直接往数据库里写

        return $result;
    }

    /**
     * @return array
     */
    protected function generateLog()
    {
        /**
         * @var Request
         */
        $request                        = Container::get('request');
        $logData                        = [];
        $logData['id']                  = GenerateHelper::uuid();
        $logData['url']                 = $request->url(true);//$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $logData['ip']                  = Container::get('request')->ip();
        $logData['method']              = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'NONE';
        // 内存使用情况，单位kb
        $logData['memory_usage']        = NumberHelper::round((memory_get_usage() - Container::get('app')->getBeginMem()) / 1024,2);
        // 耗时，单位：毫秒
        $logData['execute_millisecond'] = NumberHelper::round((microtime(true) - Container::get('app')->getBeginTime()) * 1000);
        // 避免负数的情况
        $logData['execute_millisecond'] = $logData['execute_millisecond'] > 0 ? $logData['execute_millisecond'] : 0;
        // PHP序列化成字符串后存储，保留参数类型等精确信息
        $logData['request_data']        = serialize(array_merge(
            Container::get('request')->get(),
            Container::get('request')->post(),
            Container::get('request')->param()
        ));
        $logData['user_agent']          = $request->header('user-agent','');
        // 操作的模块、控制器、操作
        $logData['action']              = strtolower($request->module().'/'.$request->controller().'/'.$request->action());
        // 操作用户ID
        $logData['user_id']             = Session::get('user_id') ? Session::get('user_id') : 0;
        $logData['create_time']         = date('Y-m-d H:i:s');
        $logData['update_time']         = $logData['create_time'];

        return $logData;
    }

}
