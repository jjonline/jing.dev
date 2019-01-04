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
     * 任务重启之后读取未执行、已投递执行中两种状态的任务数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnExecutedTasks()
    {
        $data = $this->where('task_status', 'IN', [0,1])
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
}
