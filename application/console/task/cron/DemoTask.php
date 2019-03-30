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
        return '*/60 * * * *';
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
     * rule规则制定的定时被执行的任务方法，定时被执行的任务在此方法中实现
     * 返回数组：
     *  1、第一个元素bool值true执行成功false执行失败
     *  2、第二个元素需要回写至Db中的结果内容，字符串或数组
     * 注意try-catch异常
     * @return array
     */
    public static function run(): array
    {
        try {
            // todo 异步执行的业务逻辑

            // WsServerManager::getInstance()->debug("DemoTask Call Run", 'debug'); // 该方法可以向swoole日志文件写入日志内容
            RedisServerManager::getInstance()->log("DemoTask Call Run", 'debug');
            return [true, ['log', 'status']];
        } catch (\Throwable $e) {
            return [false,[$e->getMessage(),$e->getCode()]];
        }
    }
}
