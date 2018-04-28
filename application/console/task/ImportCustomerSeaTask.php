<?php
/**
 * 异步导入客户公海数据服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 13:25
 * @file ImportCustomerSeaService.php
 */

namespace app\console\task;

use think\facade\Log;

class ImportCustomerSeaTask extends BaseTask
{
    /**
     * @var string 固定的任务标题
     */
    public $title = '批量导入客户公海数据';
    /**
     * @var array
     */
    protected $task_data;

    /**
     * 异步任务执行的入口
     * @param array $task_data
     * @return boolean true执行成功、false执行失败或出异常
     */
    public function execute(array $task_data)
    {
        $this->task_data = $task_data;
        // 标记任务开始
        $this->start($task_data['async_task_id']);

        // TODO

        Log::record(json_encode($task_data));

        // 标记任务成功结束
        $this->finishSuccess($task_data['async_task_id']);

        return true;
    }
}
