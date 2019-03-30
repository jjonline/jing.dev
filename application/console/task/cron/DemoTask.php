<?php
/**
 * 这是1个示例的定时任务方法
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-25
 * @file DemoTask.php
 */

namespace app\console\task\cron;

use app\console\swoole\framework\CronTaskAbstract;
use app\console\swoole\RedisServerManager;

class DemoTask extends CronTaskAbstract
{
    /**
     * 设定定时规则：这里是每一分钟执行一次
     * @return string
     */
    public static function rule(): string
    {
        return '* * * * *';
    }

    /**
     * 这个定时任务的名字叫DemoTask，返回的字符串要能作为php数组下标
     * @return string
     */
    public static function name(): string
    {
        return 'DemoTask';
    }

    /**
     * 每一分钟被执行的方法
     * @return bool
     */
    public static function run(): bool
    {
        // todo 异步执行的业务逻辑

        // WsServerManager::getInstance()->debug("DemoTask Call Run", 'debug'); // 该方法可以向swoole日志文件写入日志内容
        RedisServerManager::getInstance()->log("DemoTask Call Run", 'debug');

        return true;
    }
}
