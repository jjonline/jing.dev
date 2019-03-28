<?php
/**
 * 基于swoole的定时任务实现抽象类
 * ---
 * 1、在目录app\Handlers\CronTask\下新建继承本抽象类的定时任务类
 * 2、实现
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-25
 * @file CronTaskAbstract.php
 */
namespace app\console\swoole\framework;

use Swoole\Server;

abstract class CronTaskAbstract
{
    /**
     * 设置cron形式的定时规则字符串即可，即在方法中设置定时执行的时间规则
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *
     *  *    *    *    *    *
     *  -    -    -    -    -
     *  |    |    |    |    |
     *  |    |    |    |    |
     *  |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
     *  |    |    |    +---------- month (1 - 12)
     *  |    |    +--------------- day of month (1 - 31)
     *  |    +-------------------- hour (0 - 23)
     *  +------------------------- min (0 - 59)
     *
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @return string
     */
    abstract public static function rule():string;

    /**
     * 设置cron任务的名称，字符串，同名会被覆盖，建议使用类名
     * @return string
     */
    abstract public static function name():string;

    /**
     * rule规则制定的定时被执行的任务方法，定时被执行的任务在此方法中实现
     * 执行成功返回true执行失败返回false，注意try-catch异常
     * @param Server $server
     * @param int    $task_id 当前执行任务的进程id，task_worker
     * @param int    $from_worker_id 投递任务的工作进程来源worker_id
     * @return bool
     */
    abstract public static function run():bool;
}
