<?php
/**
 * 部门模型
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

    /**
     * 查询子部门列表
     * @param $parent_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptInfoByParentId($parent_id)
    {
        if(empty($parent_id))
        {
            return [];
        }
        $dept = $this->where('parent_id',$parent_id)->select();
        return !$dept->isEmpty() ? $dept->toArray() : [];
    }

    /**
     * 获取所有部门列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptList()
    {
        $dept = $this->order(['level' => 'ASC','sort' => 'ASC'])->select();
        if(!$dept->isEmpty())
        {
            return $dept->toArray();
        }
        return [];
    }

}
