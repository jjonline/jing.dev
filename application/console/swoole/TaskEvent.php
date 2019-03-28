<?php
/**
 * swoole事件定义
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-28 22:39
 * @file TaskEvent.php
 */
namespace app\console\swoole;

use app\common\helper\ArrayHelper;
use app\console\swoole\framework\SwooleHelper;
use Swoole\Coroutine;
use Swoole\Redis\Server;
use think\Exception;

class TaskEvent
{
    /**
     * master进程启动回调方法
     * @param Server $server
     */
    public static function onStart(Server $server)
    {
        RedisServerManager::getInstance()->log("onStart Event execute");

        // swoole相关文件路径
        $path = app()->getRootPath() . 'runtime/swoole';

        // 将主进程pid信息协程方式写入文件
        Coroutine::create(function () use ($path, $server) {
            Coroutine::writeFile($path . '/master.pid', $server->master_pid, null);
            RedisServerManager::getInstance()->log("write master pid=".$server->master_pid);
        });

        // 将管理进程pid信息协程方式写入文件
        Coroutine::create(function () use ($path, $server) {
            Coroutine::writeFile($path . '/manager.pid', $server->manager_pid, null);
            RedisServerManager::getInstance()->log("write manager pid=".$server->manager_pid);
        });

        // 设置主进程名称，不支持 MacOS
        $master_process_name = 'Jing.Redis.Master';
        if (SwooleHelper::setProcessName($master_process_name)) {
            RedisServerManager::getInstance()->log(
                "Master started, name={$master_process_name}, pid={$server->master_pid}"
            );
        } else {
            RedisServerManager::getInstance()->log(
                "Master started, pid={$server->master_pid}"
            );
        }
    }

    /**
     * worker/task-worker进程启动回调方法
     * @param Server $server
     * @param $worker_id
     */
    public static function onWorkStart(Server $server, $worker_id)
    {
        // task和worker进程重命名
        if ($server->taskworker) {
            $master_process_name = 'Jing.Redis.Task-'.$worker_id;
            if (SwooleHelper::setProcessName($master_process_name)) {
                RedisServerManager::getInstance()->log(
                    "TaskWorker started, name=`{$master_process_name}` pid=".$server->worker_pid
                );
            } else {
                RedisServerManager::getInstance()->log(
                    "TaskWorker started, pid=".$server->worker_pid
                );
            }
        } else {
            $master_process_name = 'Jing.Redis.Worker-'.$worker_id;
            if (SwooleHelper::setProcessName($master_process_name)) {
                RedisServerManager::getInstance()->log(
                    "Worker started, name=`{$master_process_name}` pid=".$server->worker_pid
                );
            } else {
                RedisServerManager::getInstance()->log(
                    "Worker started, pid=".$server->worker_pid
                );
            }
        }
    }

    /**
     * 新连接触发
     * @param Server  $server
     * @param integer $fd 连接的文件描述符
     * @param integer $reactor_id 来自哪个Reactor线程
     */
    public static function onConnect(Server $server, $fd, $reactor_id)
    {
        RedisServerManager::getInstance()->log(
            "new connect worker_id=".$server->worker_id.' fd='.$fd.' reactor_id='.$reactor_id
        );
    }

    /**
     * 连接关闭触发
     * @param Server  $server
     * @param integer $fd 连接的文件描述符
     * @param integer $reactor_id 来自哪个Reactor线程
     */
    public static function onClose(Server $server, $fd, $reactor_id)
    {
        RedisServerManager::getInstance()->log(
            "close connect worker_id=".$server->worker_id.' fd='.$fd.' reactor_id='.$reactor_id
        );
    }

    /**
     * 为redis-like server绑定lpush命令接收数据的处理器
     * @param mixed $fd   客户端fd
     * @param mixed $data redis-client `lpush a b c`命令推送过来的打包成数组的数据
     */
    public static function lPushReceiver($fd, $data)
    {
        /**
         * 参数的结构
         * $data = [
         *    0 => '任务类名，对redis来说就是List列表的表名',
         *    1 => '任务参数，是一个json字符串，对redis来说就是List列表中的值'
         * ]
         */
        try {
            /**
             * 确保传递给task异步执行的参数是pair形式的数组
             */
            if (!is_array($data) || count($data) < 2) {
                throw new Exception(
                    'lpush data format Error:'.json_encode($data, JSON_UNESCAPED_UNICODE)
                );
            }

            // 输出接收到的任务原始数据
            RedisServerManager::getInstance()->log(
                'receive async task: '. json_encode($data, JSON_UNESCAPED_UNICODE)
            );

            // 一次往队列里塞入多个值的情况，拆分成多个任务塞进去
            if (is_array($data) && count($data) > 2) {
                $result = ArrayHelper::segmentToPairArray($data);
            } else {
                $result = [$data];
            }

            /**
             * 循环投递任务
             */
            foreach ($result as $key => $task_pair) {
                if (empty($task_pair) || count($task_pair) != 2) {
                    throw new Exception('task param error, must pair data');
                }

                $task_name = $task_pair[0]; // 解析出的任务名称，也就是lpush过来的队列名称
                $task_data = $task_pair[1]; // 解析出的任务参数，也就是lpush过来到队列中的数据

                // 构造任务数据
                $task = [
                    'task' => $task_name,
                    'data' => $task_data,
                ];
                RedisServerManager::getInstance()->async($task);
            }

            // 向客户端返回队列长度
            RedisServerManager::getInstance()->getServer()->send(
                $fd,
                Server::format(Server::INT, count($result))
            );
        } catch (\Throwable $e) {
            RedisServerManager::getInstance()->logError(
                "receive redis-like server lpush Error, msg={$e->getMessage()}, code={$e->getCode()}",
                'lPushReceiver Exception'
            );
            // 向客户端返回错误
            RedisServerManager::getInstance()->getServer()->send(
                $fd,
                Server::format(Server::NIL)
            );
        }
    }
}
