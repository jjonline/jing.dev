<?php
/**
 * 任务便捷投递帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-30 14:58
 * @file TaskHelper.php
 */
namespace app\common\helper;

use app\common\service\AsyncTaskService;
use app\console\swoole\framework\DynamicCronManager;
use think\facade\Cache;

class TaskHelper
{
    /**
     * fpm进程中快捷投递异步任务的入口方法
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 使用方法：AsyncTaskHelper::delivery(className, task_param_array);
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @param string  $task 异步执行的任务名称，即带命名空间的完整类名
     * @param array   $task_data 异步任务类run方法传递的参数
     * @param integer $user_id 执行异步任务的用户ID，不传默认当前用户
     * @param integer $dept_id 用户所属部门ID，不传默认当前用户所属部门
     * @return bool|string
     */
    public static function deliveryAsyncTask($task, array $task_data, $user_id = null, $dept_id = null)
    {
        /**
         * @var AsyncTaskService $service
         */
        $service = app()->get(AsyncTaskService::class);
        return $service->delivery($task, $task_data, $user_id, $dept_id);
    }

    /**
     * 投递动态定时任务
     * ---
     * 为保障取消定时任务的准确性，请自主保障 $task + `定时业务id` 的全局唯一性
     * ---
     * @param string $task 实现抽象类DynamicCronTaskAbstract的完整类名
     * @param mixed  $task_data 定时任务被触发执行时的参数
     * @param mixed  $cron_expression 定时规则，可以是cron表达式，也可以是日期时间(日期时间字符串、时间戳均可)
     * @param mixed  $business_id 定时业务id，用于取消定时任务，请自主保证 $task + $business_id 全局唯一
     * @return bool|string
     */
    public static function deliveryCronTask($task, $task_param, $cron_expression, $business_id)
    {
        /**
         * @var \Redis $Redis
         */
        $Redis = Cache::handler();

        $task = [$task, $task_param, $cron_expression, $business_id];
        return $Redis->lPush(DynamicCronManager::CRON_QUEUE_NAME, json_encode($task));
    }

    /**
     * 取消|清除指定`定时业务id`的定时任务
     * @param string $task 实现抽象类DynamicCronTaskAbstract的完整类名
     * @param mixed  $business_id 定时业务id，参照`deliveryCronTask`方法的说明
     * @return bool
     */
    public static function clearCronTask($task, $business_id)
    {
        /**
         * @var \Redis $Redis
         */
        $Redis = Cache::handler();

        $task = [$task, $business_id];
        return $Redis->lPush(DynamicCronManager::CRON_MANAGE_NAME, json_encode($task));
    }
}
