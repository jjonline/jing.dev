<?php
/**
 * 定时任务|循环相关的自定义进程实现
 * ---
 * 功能：
 * 1、自定义进程内的定时循环的发生器
 * 2、自定义进程的自管理
 * ---
 * 机制：
 * 1、自定义进程被创建时触发start执行1次，完成进程重命名、信号回调注册、定时任务解析、定时解析后倒计时投递
 * 2、通过进程间通信(sendMessage)从自定义进程中投递任务到worker进程
 * 3、工作worker进程收到进程通信(onPipeMessage)之后投递任务到task-worker中具体执行
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-25
 * @file CronProcessRunner.php
 */
namespace app\console\swoole\framework;

use app\console\swoole\RedisServerManager;
use Cron\CronExpression;
use Swoole\Process;
use Swoole\Timer;

class CronProcessRunner
{
    use SingletonTrait;
    /**
     * @var array 自动parse解析出的继承CronTaskAbstract的定时任务类信息数组 ['name' => ['rule' => 'xx','task' => 'yy']]
     */
    protected $tasks;

    /**
     * 自定义Runner进程被启动后被start所触发执行1次的方法
     * @param Process $process
     * @throws \Exception
     */
    public function run(Process $process)
    {
        // 启动进程时执行一次解析所有固定定时任务
        $this->parseStaticCronTask();
        $this->staticCronProcess();

        // 启动进程时触发动态定时任务初始化，由动态定时任务管理器进行调度
        $this->dynamicCronProcess();

        RedisServerManager::getInstance()->log(
            RedisServerManager::CRON_PROCESS.' started,pid='.$process->pid
        );

        // 自定义定时任务经常内每29秒tick执行一次
        swoole_timer_tick(29 * 1000, function () {
            $this->staticCronProcess();
        });
    }

    /**
     * 动态定时任务init，无需每29秒触发1次
     */
    protected function dynamicCronProcess()
    {
        DynamicCronManager::init();
    }

    /**
     * 静态定时任务，即实现定时抽象类的脚本映射的定时任务
     * 解析定时任务并生成倒计时执行的异步信号
     */
    protected function staticCronProcess()
    {
        if (empty($this->tasks)) {
            return;
        }

        foreach ($this->tasks as $name => $task) {
            $next_run_time  = CronExpression::factory($task['rule'])->getNextRunDate();
            $distance_time  = $next_run_time->getTimestamp() - time();
            if ($distance_time < 30) {
                swoole_timer_after($distance_time * 1000, function () use ($name, $task) {
                    /**
                     * 1、定时任务通过管道从Process进程中投递到Worker进程中
                     * 2、Worker进程从管道中读取到进程任务后，再次将任务投递给随机的Task进程去具体执行
                     */
                    $send_status = RedisServerManager::getInstance()->processAsync([
                        'task' => $task['task'],
                        'data' => $task,
                    ]);
                    if ($send_status) {
                        RedisServerManager::getInstance()->logGreen(
                            RedisServerManager::CRON_PROCESS." Send `{$task['task']}` Success",
                            'cron'
                        );
                    } else {
                        RedisServerManager::getInstance()->logError(
                            RedisServerManager::CRON_PROCESS." Send `{$task['task']}` Fail.",
                            'cron'
                        );
                    }
                });
            }
        }
    }

    /**
     * 解析|分析定时任务类情况成任务数组，仅启动的时候执行1次
     * @return array
     * @throws \Exception
     */
    protected function parseStaticCronTask()
    {
        try {
            $cron_task_path = app()->getAppPath().'console/task/cron/';
            $iterator       = new \DirectoryIterator($cron_task_path);
            while ($iterator->valid()) {
                if ($iterator->isFile() && $iterator->getExtension() == 'php') {
                    /**
                     * 反射检查定时任务类是否严格继承了CronTaskAbstract抽象类
                     * @var StaticCronTaskAbstract $cron_task_class
                     */
                    $cron_task_class = 'app\console\task\cron\\'.$iterator->getBasename('.php');
                    $reflect         = new \ReflectionClass($cron_task_class);
                    if (!$reflect->isSubclassOf(StaticCronTaskAbstract::class)) {
                        throw new \Exception("the cron task class {$cron_task_class} is invalid");
                    }

                    $task_name = $cron_task_class::name();
                    $task_rule = $cron_task_class::rule();
                    if (!CronExpression::isValidExpression($task_rule)) {
                        throw new \Exception("the cron task {$task_name} rule {$task_rule} is invalid");
                    }

                    // 输出添加的异步任务日志
                    RedisServerManager::getInstance()->logGreen(
                        "added cron task: `{$cron_task_class}`"
                    );

                    // 添加到定时异步任务中
                    $this->tasks[$task_name] = [
                        'name' => $task_name,
                        'rule' => $task_rule,
                        'task' => $cron_task_class
                    ];
                }
                $iterator->next();
            }
        } catch (\Throwable $e) {
            RedisServerManager::getInstance()->logError(
                'the cron task parse fatal error: '.$e->getMessage(),
                'error'
            );
        }
        return $this->tasks;
    }

    /**
     * server中注册Process后被执行的入口
     * @param Process $process
     * @throws \Exception
     */
    public function start(Process $process)
    {
        // 自定义定时任务进程命名
        if (PHP_OS != 'Darwin') {
            $process->name(RedisServerManager::CRON_PROCESS);
        }

        /**
         * 1、某个工作进程遇到致命错误、主动退出时管理器会主动进行回收，避免出现僵尸进程
         * 2、工作进程退出后，管理器会自动拉起、创建一个新的工作进程，也就是该Runner会再次执行一遍start方法
         * 3、主进程收到SIGTERM信号时将停止fork新进程，并kill所有正在运行的工作进程
         * 4、主进程收到SIGUSR1信号时将将逐个kill正在运行的工作进程，并重新启动新的工作进程
         * ++++++++++++++++++++++++++++++++++++++++++++++++++
         * ++++++++++++++++++++++++++++++++++++++++++++++++++
         * 当主进程需要终止该Process进程时，会向此进程发送SIGTERM信号
         * 这里的监听处理一些必要的进程需要退出的收尾逻辑，当前不需要
         * 不做监听时底层会强行终止当前进程
         */
        defined('SIGTERM') || define('SIGTERM', 15);
        Process::signal(SIGTERM, function () use ($process) {
            go(function () use ($process) {
                Timer::clearAll(); // 清理所有定时任务
                Process::signal(SIGTERM, null);
                $process->exit(0);
            });
        });

        $this->run($process);
    }
}
