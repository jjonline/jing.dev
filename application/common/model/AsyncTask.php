<?php
/**
 * 异步任务模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-27 16:05
 * @file AsyncTask.php
 */

namespace app\common\model;

use think\Model;

class AsyncTask extends Model
{
    /**
     * @var integer 任务状态：未投递未执行等待中
     */
    const STATUS_WAITING = 0;
    /**
     * @var integer 任务状态：任务正在执行中
     */
    const STATUS_RUNNING = 1;
    /**
     * @var integer 任务状态：任务执行成功
     */
    const STATUS_SUCCESS = 2;
    /**
     * @var integer 任务状态：任务执行失败
     */
    const STATUS_FAIL    = 3;
    /**
     * 任务重启之后读取未执行、已投递执行中两种状态的任务数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnExecutedTasks()
    {
        $data = $this->where('task_status', 'IN', [self::STATUS_WAITING, self::STATUS_RUNNING])
              ->order('create_time', 'DESC')
              ->select();
        return $data->isEmpty() ? [] : $data->toArray();
    }

    /**
     * 用id获取详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetailById($id)
    {
        if (empty($id)) {
            return [];
        }
        $data = $this->alias('async_task')
            ->field([
                'async_task.*',
                'user.real_name',
                'dept.name as dept_name'
            ])
            ->where('async_task.id', $id)
            ->leftJoin('user user', 'user.id = async_task.user_id')
            ->leftJoin('department dept', 'dept.id = async_task.dept_id')
            ->find();
        return empty($data) ? [] : $data->toArray();
    }

    /**
     * 设置任务执行失败
     * @param mixed $id 任务id
     * @param string $result 任务结果描述字符串，这里就是失败原因咯
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setAsyncTaskFail($id, $result = '')
    {
        $task = [
            'result'      => $result,
            'task_status' => self::STATUS_FAIL,
            'finish_time' => date('Y-m-d H:i:s'),
        ];
        return !!$this->db()->where('id', $id)->update($task);
    }
}
