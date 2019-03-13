<?php
/**
 * 启动基于swoole的tcp服务器回调函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-24 21:07
 * @file SwooleService.php
 */

namespace app\console\service;

use app\common\model\AsyncTask;
use Cron\CronExpression;
use Swoole\Server;
use think\console\Output;
use think\Container;
use think\Exception;
use think\facade\Log;

class SwooleService
{
    /**
     * @var Output
     */
    protected $Output;
    protected $NameSpacePrefix = 'app\console\task\\';

    /**
     * 进程启动时做一些全局性的初始化动作
     * @param Server $server
     * @param $worker_id
     */
    public function onWorkerStart(Server $server, $worker_id)
    {
        // global $argv;
        $this->Output = new Output();
        // 仅Worker进程可调用task方法 <--> worker进程传递任务给task进程
        if (!$server->taskworker) {
            // 读取可能存在的未执行的任务
            try {
                $AsyncTaskModel   = new AsyncTask();
                $unExecuted_tasks = $AsyncTaskModel->getUnExecutedTasks();

                foreach ($unExecuted_tasks as $key => $value) {
                    // 往任务进程塞任务，先构造redis链表List传参的参数结构
                    $param0 = $value['task'];
                    $param1 = [
                        'task' => $value['task'],
                        'data' => json_decode($value['task_data'], true)
                    ];
                    $task_data = [$param0, json_encode($param1)];
                    $server->task($task_data);

                    $this->log($worker_id.'立即执行上次未执行完的任务:'.$value['id']);
                }

                // 手动清理模型对象
                $AsyncTaskModel->getConnection()->free();
                unset($AsyncTaskModel);

                // 在worker进程中每10秒执行一次指定回调函数
                $server->tick(60000, [$this,'tickTaskHandler'], $server); // Worker进程每10s自动执行一次
            } catch (\Throwable $e) {
                $this->log('初始化启动服务器时读取上次未执行完的任务出错：'.$e->getMessage());
            }
        }
    }

    /**
     * connect时触发的事件
     * @param Server $server
     * @param $fd
     */
    public function onConnect(Server $server, $fd)
    {
        $this->log($fd.' link connected,worker_id='.$server->worker_id);
    }

    /**
     * 异步线程执行任务的回调函数
     * --
     * 所有需异步执行的任务在此函数内实现调度和异步运行
     * --
     * @param Server $server
     * @param int   $task_id   任务ID，由swoole扩展内自动生成，用于区分不同的任务
     * @param int   $worker_id worker进程ID，多进程中有用
     * @param array $data []   接收任务的参数，redis server模式跑异步任务时，为1个一维数组，第一个元素为队列名
     * @return string
     */
    public function onTask(Server $server, $task_id, $worker_id, $data)
    {
        /**
         * 参数的结构
         * $data = [
         *    0 => '任务类名，对redis来说就是List列表的表名',
         *    1 => '任务参数，是一个json字符串，对redis来说就是LIst列表中的值'
         * ]
         */
        try {
            // 处理任务参数
            $task_data = json_decode($data[1], true);
            if (empty($task_data) || empty($task_data['task']) || empty($task_data['data'])) {
                throw  new Exception('任务传递的参数缺失或错误:'.json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            // 实例化任务对象类
            $task_class = $this->NameSpacePrefix.$data[0];
            $task       = Container::get($task_class);

            // 开始执行任务
            $begin_time = microtime(true);
            $task->execute($task_data['data']);
            $end_time   = microtime(true);
            $use_time   = $end_time - $begin_time;

            $this->log('任务['.$worker_id.'_'.$task_id.']执行完毕，耗时:'.$use_time.'秒');

            // 清理实例化对象
            Container::remove($task_class);
        } catch (\Throwable $e) {
            $this->log('任务执行抛出异常：'.$e->getMessage());
        }
        return 'ok';
    }

    /**
     * 定时循环执行的回调函数
     * ----
     * worker进程只能是1个，否则会导致同时有两个定时任务执行
     * ----
     * @param int $tick_id
     * @param Server $server
     */
    public function tickTaskHandler($tick_id, Server $server)
    {
        try {
            $cron_task_path = app()->getModulePath().'console/task/cron/';
            $iterator       = new \DirectoryIterator($cron_task_path);
            while ($iterator->valid()) {
                // 读取该目录下的所有定时任务文件名[亦既无命名空间的类名]
                if ($iterator->isFile() && $iterator->getExtension() == 'php') {
                    $ClassName        = $this->NameSpacePrefix.'cron\\'.$iterator->getBasename('.php');
                    $this->executeTickTask($ClassName, $server);
                }
                $iterator->next();
            }
        } catch (\Throwable $e) {
            $this->log('定时任务触发异步执行出错：'.$e->getMessage());
        }
    }

    /**
     * 生成并投递定时执行的异步任务
     * @param $className
     * @param Server $server
     * @return bool
     */
    protected function executeTickTask($className, Server $server)
    {
        try {
            $cron = CronExpression::factory($className::$CronExpression);
            if ($cron->isDue()) {
                $tick   = Container::get($className);
                $result = $tick->execute();
                if (!empty($result)) {
                    $server->task($result);
                }
                // 清理实例化对象
                Container::remove($className);
            }
            return true;
        } catch (\Throwable $e) {
            $this->log('生成定时触发的异步任务数据失败：'.$e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param Server $server
     * @param $task_id
     * @param $data
     * @return string
     */
    public function onFinish(Server $server, $task_id, $data)
    {
        return 'finish';
    }

    /**
     * 进程关闭时执行
     * @param Server $server
     * @param $fd
     */
    public function onClose(Server $server, $fd)
    {
        $this->log($fd . ' link closed, worker_id='.$server->worker_id);
    }

    /**
     * 统一输出和记录日志方法
     * @param $str
     */
    protected function log($str)
    {
        try {
            // 防止输出抛出异常终止进程
            $this->Output->writeln($str);
            Log::record($str);
        } catch (\Throwable $e) {
        }
    }
}
