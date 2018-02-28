<?php
/**
 * 部门模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:20
 * @file Department.php
 */

namespace app\common\model;

use think\Db;
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

    /**
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptIdVectorByDeptId($id)
    {
        $data = Db::name('department dept1')
              ->leftJoin('department dept2','dept1.parent_id = dept2.id')
              ->leftJoin('department dept3','dept2.parent_id = dept3.id')
              ->leftJoin('department dept4','dept3.parent_id = dept4.id')
              ->leftJoin('department dept5','dept4.parent_id = dept5.id')
              ->leftJoin('department dept6','dept5.parent_id = dept6.id')
              ->where('dept6.id',$id)
              ->field(['dept6.id as dept_id1','dept5.id as dept_id2','dept4.id as dept_id3','dept3.id as dept_id4','dept2.id as dept_id5','dept1.id as dept_id6'])
              ->select();
    }

}
