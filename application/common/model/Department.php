<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:20
 * @file Department.php
 */

namespace app\common\model;

use think\Model;

class Department extends Model
{

    /**
     * 部门ID查找部门信息
     * @param $id mixed 数字类型的部门ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptInfoById($id)
    {
        if(empty($id))
        {
            return [];
        }
        $dept = $this->find($id);
        return $dept ? $dept->toArray() : [];
    }

}
