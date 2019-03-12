<?php
/**
 * 定时异步任务生成异步任务即往异步任务写入异步数据的方法
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-12 23:57
 * @file DemoCronTask.php
 */
namespace app\console\task\cron;

use app\console\task\BaseCronTask;

class DemoCronTask extends BaseCronTask
{
    /**
     * @var string cron定时任务表达式，指定该类被执行的定时规则
     */
    public static $CronExpression = '00 */4 * * *';

    /**
     * 被swoole定时器在$CronExpression指定的时间锁执行的方法
     * @return array|bool
     */
    public function execute()
    {
        $task      = 'QueryExpressCronTask'; // 被执行的异步任务类名称--必须在task下存在
        $task_data = []; // 被执行的异步任务所需要的参数数据
        return $this->generateTask($task, $task_data); // BaseCronTask基类提供好的写入异步任务数据表的方法
    }

}
