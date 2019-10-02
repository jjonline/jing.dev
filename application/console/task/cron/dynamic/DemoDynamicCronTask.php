<?php
/**
 * 动态定时任务demo
 */

namespace app\console\task\cron\dynamic;

use app\console\swoole\framework\DynamicCronTaskAbstract;

class DemoDynamicCronTask extends DynamicCronTaskAbstract
{
    /**
     * 动态定时触发的动作执行逻辑
     * @param mixed $param 任务参数
     * @return bool
     */
    public static function run($param): bool
    {
        echo 'dynamic cron task run';
        return true;
    }
}
