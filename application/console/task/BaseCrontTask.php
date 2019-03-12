<?php
/**
 * 定时任务基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-06-14 19:22
 * @file CronBaseTask.php
 */

namespace app\console\task;

use app\common\helper\GenerateHelper;
use app\common\model\AsyncTask;

class BaseCronTask
{
    /**
     * @var string cron定时任务的执行规则，子类必须重写定义子类被定时执行的cron规则
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
     */
    public static $CronExpression = '1 * * * *';
    /**
     * @var AsyncTask
     */
    public $AsyncTask;

    /**
     * @var string
     */
    protected $temp_path;

    public function __construct()
    {
        $this->AsyncTask = new AsyncTask();
        $this->temp_path = app()->getRootPath().'manage/_temp/';
    }

    /**
     * 定时任务触发异步任务执行的入口基类方法
     * @return array
     */
    public function execute()
    {
        return [];
    }

    /**
     * 定时执行的任务到时间后触发生成异步任务数据的方法
     * @param string $task      需定制异步执行的定时任务类名称
     * @param array  $task_data 传递给异步任务的参数
     * @return array|bool
     */
    public function generateTask($task, array $task_data)
    {
        try {
            // 将task即任务类名塞入task_data
            $id                         = GenerateHelper::uuid();// 任务ID
            $task_data['async_task_id'] = $id; // 异步class的execute方法的参数数组中可以拿到该值
            $data['task']               = $task;
            $data['data']               = $task_data;

            // 记录异步任务信息
            $async_task = [
                'id'        => $id,
                'user_id'   => 1,
                'dept_id'   => 1,
                'task'      => $task,
                'task_data' => json_encode($task_data, JSON_UNESCAPED_UNICODE),
                'result'    => '',// 将结果集先设置为空
            ];
            // 写入任务数据
            $this->AsyncTask->isUpdate(false)->save($async_task);

            return [$task,json_encode($data)];
        } catch (\Throwable $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
