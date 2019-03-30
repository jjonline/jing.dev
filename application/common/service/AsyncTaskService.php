<?php
/**
 * fpm环境下的异步任务客户端服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 20:09
 * @file AsyncTaskService.php
 */

namespace app\common\service;

use app\common\helper\GenerateHelper;
use app\common\helper\UtilHelper;
use app\common\model\AsyncTask;
use think\Exception;
use think\facade\Config;
use think\facade\Log;
use think\facade\Session;
use think\Request;

class AsyncTaskService
{
    /**
     * @var \Redis
     */
    public $Client;
    /**
     * @var AsyncTask
     */
    public $AsyncTask;

    public function __construct(AsyncTask $asyncTask)
    {
        // $this->connect();
        $this->AsyncTask = $asyncTask;
    }

    /**
     * @throws Exception
     */
    protected function connect()
    {
        if (!extension_loaded('redis')) {
            throw new Exception("need redis php extension.");
        }

        $this->Client = new \Redis();

        $host    = Config::get('swoole.ip');
        $port    = Config::get('swoole.port');
        $socket  = Config::get('swoole.socket');
        $timeout = Config::get('swoole.timeout');
        // 依据配置选择连接方式，依据是否配置有unix socket优先
        if (empty($socket)) {
            $this->Client->connect($host, $port, $timeout);
        } else {
            $this->Client->connect($socket);
        }
    }

    /**
     * 投递异步任务
     * --
     * 连接报错、投递报错隐藏
     * --
     * @param string $task 投递需执行异步任务的class名，在异步服务中可以理解成一个Redis的List键名
     * @param array  $task_data 投递给$task指定的类执行的数组参数
     * @param int    $user_id 执行异步任务的用户ID，不传默认当前用户
     * @param int    $dept_id 用户所属部门ID，不传默认当前用户所属部门
     * @return bool|string 任务投递成功返回任务ID（UUID形式），任务投递失败返回false
     */
    public function delivery($task, array $task_data, $user_id = null, $dept_id = null)
    {
        // 完善任务用户和部门信息
        $user_id = $user_id ?? Session::get('user_info.id');
        $dept_id = $dept_id ?? Session::get('user_info.dept_id');

        // 将任务名称补充至任务参数中
        $task_data['task'] = $task;

        // 将任务用户和任务用户部门酌情补充到任务参数中
        if (empty($task_data['user_id'])) {
            $task_data['user_id'] = $user_id;
        }
        if (empty($task_data['dept_id'])) {
            $task_data['dept_id'] = $dept_id;
        }

        try {
            $id                   = GenerateHelper::uuid();// 任务ID
            $task_data['task_id'] = $id;

            // 记录异步任务信息
            $async_task = [
                'id'        => $id,
                'user_id'   => $user_id,
                'dept_id'   => $dept_id,
                'task'      => $task,
                'task_data' => json_encode($task_data, JSON_UNESCAPED_UNICODE),
                'result'    => '',// 将结果集先设置为空
            ];

            // 写入任务数据
            $this->AsyncTask->isUpdate(false)->save($async_task);

            // 链接异步服务器投递任务
            if (empty($this->Client)) {
                $this->connect();
            }
            $result = $this->Client->lPush($task, json_encode($task_data));

            return $result !== false ? $id : false;
        } catch (\Throwable $e) {
            Log::record('投递异步任务失败：'.$e->getMessage().'['.json_encode($task_data).']');
            return false;
        }
    }

    /**
     * 通过Id 获取任务详情
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetailById(Request $request)
    {
        $id   = $request->get('id/i');
        $data = $this->AsyncTask->getDetailById($id);
        if (!empty($data) && !empty($data['result'])) {
            $data['result'] = UtilHelper::nl2p($data['result']);
        }
        return ['error_code' => 0,'error_msg'=>'Success:请求成功','data'=>$data];
    }
}
