<?php
/**
 * 异步任务基类
 * ---
 * 异步任务执行的入口方法：execute
 * 所有异步任务均需实现该抽象类
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 13:27
 * @file BaseTask.php
 */

namespace app\console\task;

use think\Db;

class BaseTask
{
    /**
     * @var string 固定的任务标题，继承类必须重写
     */
    public $title = '';

    /**
     * 异步任务执行的入口方法
     * @param array $param
     */
    public function execute(array $param) {}

    /**
     * 标记任务已开始
     * @param $async_task_id
     * @return bool
     */
    protected function start($async_task_id)
    {
        if(!empty($async_task_id))
        {
            try{
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 1,
                    'delivery_time' => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            }catch (\Throwable $e) {}
        }
        return false;
    }

    /**
     * 任务执行成功并完毕
     * @param string $async_task_id 执行任务的ID
     * @param string $result        执行任务的结果字符串
     * @return bool
     */
    protected function finishSuccess($async_task_id,$result = '')
    {
        if(!empty($async_task_id))
        {
            try{
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 2,
                    'result'        => $result,
                    'finish_time'   => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            }catch (\Throwable $e) {}
        }
        return false;
    }

    /**
     * 任务执行失败并结束
     * @param string $async_task_id 执行任务的ID
     * @param string $result        执行任务的结果字符串
     * @return bool
     */
    protected function finishFail($async_task_id,$result = '')
    {
        if(!empty($async_task_id))
        {
            try{
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 3,
                    'result'        => $result,
                    'finish_time'   => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            }catch (\Throwable $e) {}
        }
        return false;
    }
}
