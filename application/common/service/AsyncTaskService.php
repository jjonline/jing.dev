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
        if(!extension_loaded('redis'))
        {
            throw new Exception("need redis php extension.");
        }

        $this->Client = new \Redis();

        $host    = Config::get('swoole.ip');
        $port    = Config::get('swoole.port');
        $socket  = Config::get('swoole.socket');
        $timeout = Config::get('swoole.timeout');
        // 依据配置选择tcp连接方式
        if($host)
        {
            $this->Client->connect($host,$port,$timeout);
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
     * @param int    $user_id 执行异步任务的用户ID
     * @param int    $dept_id 用户所属部门ID
     * @return bool|string 任务投递成功返回任务ID（UUID形式），任务投递失败返回false
     */
    public function delivery($task,array $task_data,$user_id = 0,$dept_id = 0)
    {
        try{
            // 将task即任务类名塞入task_data
            $id                         = GenerateHelper::uuid();// 任务ID
            $task_data['async_task_id'] = $id; // 异步class的execute方法的参数数组中可以拿到该值
            $data['task']               = $task;
            $data['data']               = $task_data;

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
            if(empty($this->Client))
            {
                $this->connect();
            }
            $result = $this->Client->lPush($task,json_encode($data));

            return $result !== false ? $id : false;

        }catch (\Throwable $e) {
            Log::record('投递异步任务失败：'.$e->getMessage().'['.json_encode($data).']');
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
        if(!empty($data) && !empty($data['result']))
        {
            $data['result'] = UtilHelper::nl2p($data['result']);
        }
        return ['error_code' => 0,'error_msg'=>'Success:请求成功','data'=>$data];
    }

}
