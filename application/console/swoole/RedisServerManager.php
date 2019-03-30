<?php
/**
 * 基于swoole实现的一套redis协议server端用于处理任务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 23:14
 * @file RedisServerManager.php
 */
namespace app\console\swoole;

use app\console\swoole\framework\AsyncTaskAbstract;
use app\console\swoole\framework\CronProcessRunner;
use app\console\swoole\framework\CronTaskAbstract;
use app\console\swoole\framework\SingletonTrait;
use Swoole\Process;
use Swoole\Redis\Server;
use think\facade\Config;

class RedisServerManager
{
    use SingletonTrait;
    /**
     * @var Server
     */
    private $server;
    /**
     * @var bool 标记是否启动
     */
    private $isStart;
    /**
     * @var Process
     */
    private $cronProcess;
    /**
     * @var string 主进程名称
     */
    const MASTER_PROCESS = 'Jing.Master';
    /**
     * @var string 工作worker进程名称[前缀]
     */
    const WORKER_PROCESS = 'Jing.Worker-';
    /**
     * @var string 任务工作task-worker进程名称[前缀]
     */
    const TASK_PROCESS   = 'Jing.Task-';
    /**
     * @var string 自定义定时任务进程名称
     */
    const CRON_PROCESS   = 'Jing.Cron';
    /**
     * @var integer 信号|消息传递的任务类型标记：普通异步任务
     */
    const TASK_ASYNC = 1;
    /**
     * @var integer 信号|消息传递的任务类型标记：定时异步任务
     */
    const TASK_CRON = 2;

    /**
     * Redis-like管理器创建服务器，警告：请使用管理器手动启动
     * @return Server
     */
    public function createServer()
    {
        $socket = Config::get('swoole.socket');
        $ip     = Config::get('swoole.ip');
        $port   = Config::get('swoole.port');
        $config = Config::get('swoole.options');

        if (!empty($socket)) {
            $this->log('Create unix socket Redis-like Server');
            $this->server = new Server($socket, $port, SWOOLE_PROCESS, SWOOLE_UNIX_STREAM);
            $this->logGreen('unix socket address:'.$socket);
        } else {
            $this->log('Create ip mode Redis-like Server');
            $this->server = new Server($ip, $port, SWOOLE_PROCESS);
            $this->logGreen("ip/tcp mode address:tcp://{$ip}:{$port}");
        }

        // server设置项
        if ($this->server) {
            $this->server->set($config);
        }

        // 绑定异步任务onTask事件
        $this->bindOnTask();

        // 绑定线程间通信事件回调
        $this->bindOnPipeMessage();

        // 添加定时任务process
        $this->addCronProcess();

        return $this->server;
    }

    /**
     * Redis-like管理器手动启动Server
     */
    public function start()
    {
        if ($this->isStart) {
            return;
        }
        $this->isStart = true;

        // 低版本task结束之后必须回调finish方法 无特别处理逻辑，这里给于一个空回调
        $this->server->on('finish', function () {
            // code
        });

        $this->log('Redis-Like Server is Starting');
        $this->server->start();
    }

    /**
     * 添加一个单独处理定时任务调度的自定义进程
     */
    private function addCronProcess()
    {
        $this->cronProcess = new Process([CronProcessRunner::getInstance(), 'start']);
        $this->server->addProcess($this->cronProcess);
        $this->log('Create cron task process '.self::CRON_PROCESS);
    }

    /**
     * worker|task_worker绑定pipe消息接收
     */
    private function bindOnPipeMessage()
    {
        $this->server->on('pipeMessage', function (Server $server, $src_worker_id, $message) {
            $worker_name  = self::WORKER_PROCESS.$server->worker_id;
            $process_name = 'process-'.$src_worker_id;
            $this->log("{$worker_name} received PipeMessage,from {$process_name}", "debug");
            try {
                $data = unserialize($message);

                // 普通异步任务直接在接收到信号的当前进程执行
                if (self::TASK_ASYNC == $data['type']) {
                    // todo
                }

                /**
                 * task_worker执行的定时任务，投递到task进程
                 * ++++++++++++++++++++++++++++++++++++
                 * 向task_worker进程投递任务数据结构限定
                 * ++++++++++++++++++++++++++++++++++++
                 * [
                 *    'type' => type,
                 *    'task' => className,
                 *    'data' => ['name' => ,'rule' =>, 'task' => ]
                 * ]
                 */
                if (self::TASK_CRON == $data['type']) {
                    $server->task($data);
                }
            } catch (\Throwable $e) {
                // worker进程接收任务出错，输出出错的详情
                $log = [
                    'worker'     => $worker_name,
                    'process'    => $process_name,
                    'error_msg'  => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'data'       => $data ?? []
                ];
                $this->logError($log, 'onPipeMessage Exception');
            }
        });

        $this->log('Bind onPipeMessage Event For Task-Worker/Worker');
    }

    /**
     * task_worker绑定异步任务
     * ++++++++++++++++++++++++++++++++++++
     * 向task_worker进程投递任务数据结构限定：
     * ++++++++++++++++++++++++++++++++++++
     * [
     *    'task' => className,
     *    'data' => mixed
     * ]
     */
    private function bindOnTask()
    {
        /**
         * @param $server
         * @param int $task_id 任务ID，用于区分不同的任务
         * @param int $from_worker_id 异步任务来源worker_id
         * @param $data ['task' => ClassName,'data' => mixedParam]
         */
        $this->server->on('task', function (Server $server, $task_id, $from_worker_id, $data) {
            $task_name   = self::TASK_PROCESS.$server->worker_id;
            $worker_name = self::WORKER_PROCESS.$from_worker_id;
            $this->log("{$task_name} received task-{$task_id} from {$worker_name}", "debug");

            try {
                $class_name = $data['task'];
                $reflect    = new \ReflectionClass($class_name);

                // 定时任务
                if ($reflect->isSubclassOf(CronTaskAbstract::class)) {
                    // 记录定时任务数据至Db返回任务ID
                    $task_id = TaskEvent::setCronTaskBegin($data);
                    if (!empty($task_id)) {
                        /**
                         * @var CronTaskAbstract $class_name
                         */
                        list($status, $result) = $class_name::run();
                        $data['task_id'] = $task_id;

                        if ($status) {
                            // 执行成功回写状态至Db
                            TaskEvent::setAsyncTaskSuccess($data, $result);
                            $this->logGreen("{$task_name} Run cron `{$class_name}` Success", 'ok');
                        } else {
                            // 执行失败回写状态至Db
                            TaskEvent::setAsyncTaskFail($data, $result);
                            $this->logWarn("{$task_name} Run cron `{$class_name}` Fail", 'warn');
                        }
                    }
                }

                // 异步任务
                if ($reflect->isSubclassOf(AsyncTaskAbstract::class)) {
                    /**
                     * @var AsyncTaskAbstract $task_obj
                     */
                    $task_obj = new $class_name();
                    $status   = $task_obj->run($data['data']);
                    $task_obj->finish();
                    if ($status) {
                        // 执行成功回写状态至Db
                        $this->logGreen("{$task_name} Run async `{$class_name}` Success", 'ok');
                    } else {
                        // 执行失败回写状态至Db
                        $this->logWarn("{$task_name} Run async `{$class_name}` Fail", 'warn');
                    }
                    unset($task_obj);
                }

                unset($reflect);
            } catch (\Throwable $e) {
                // 执行任务出错，输出出错的详情
                $log = [
                    'worker'     => $worker_name,
                    'task'       => $task_name,
                    'error_msg'  => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'data'       => $data
                ];

                // 设置异步任务执行失败到Db中，方法体自动判断是否要处理
                TaskEvent::setAsyncTaskFail($data, $log);

                $this->logError($log, 'onTask Exception');
            }
        });

        $this->log('Bind onTask Event For Task-Worker');
    }

    /**
     * worker进程内投递异步任务到task-worker中异步执行
     * @param array $task ['type' => 1, 'task' => 'className', 'data' => mixed] 传递给异步任务的参数，不易过大
     * @param int $dst_task_worker_id 指定执行异步任务的task进程，默认不指定
     * @return bool
     */
    public function async($task_data, $dst_task_worker_id = -1)
    {
        $task['type'] = self::TASK_ASYNC;
        $result = $this->server->task($task_data, $dst_task_worker_id);
        return false !== $result;
    }

    /**
     * 进程间管道投递异步任务
     * @param array $task ['type' => 1,'task' => 'className', 'data' => mixed]
     * @return bool
     */
    public function processAsync(array $task)
    {
        if (!$this->isStart) {
            return false;
        }

        $config     = $this->server->setting;
        $worker_num = $config['worker_num'];

        // 未开启task_worker进程
        if (empty($config['task_worker_num'])) {
            return false;
        }

        // 重置随机播种
        mt_srand();

        // 暂时只投递到worker进程，不直接投递到task_worker
        $worker_id = mt_rand(0, $worker_num - 1);

        $task['type'] = self::TASK_CRON;
        $this->server->sendMessage(serialize($task), $worker_id);

        $this->log(
            'Send task to '.self::WORKER_PROCESS.$worker_id,
            'debug'
        );

        return true;
    }

    /**
     * 静态短方法获取Server对象
     * @return Server
     */
    public static function server()
    {
        return self::getInstance()->getServer();
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return Process
     */
    public function getCronProcess()
    {
        return $this->cronProcess;
    }
}
