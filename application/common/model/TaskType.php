<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-18 21:06
 * @file TaskType.php
 */

namespace app\common\model;

use think\Db;
use think\Model;

class TaskType extends Model
{

    /**
     * @param $task_type_id
     * @throws
     * @return []
     */
    public function getTaskTypeById($task_type_id)
    {
        $data = $this->find($task_type_id);
        return $data ? $data->toArray() : [];
    }

    /**
     * @param $task_code
     * @throws
     * @return []
     */
    public function getTaskTypeByCode($task_code)
    {
        $data = $this->where(['code' => $task_code])->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 获取所有任务列表
     * @throws
     * @return []
     */
    public function getTaskTypeList()
    {
        $data = $this->order(['code' => 'ASC'])->select();
        return $data ? $data->toArray() : [];
    }
}
