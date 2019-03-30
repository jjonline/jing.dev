<?php
/**
 * 异步任务便捷投递帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-30 14:58
 * @file AsyncTaskHelper.php
 */
namespace app\common\helper;

use app\common\service\AsyncTaskService;

class AsyncTaskHelper
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
    public static function delivery($task, array $task_data, $user_id = null, $dept_id = null)
    {
        /**
         * @var AsyncTaskService $service
         */
        $service = app()->get(AsyncTaskService::class);
        return $service->delivery($task, $task_data, $user_id, $dept_id);
    }
}
