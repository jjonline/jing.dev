<?php
/**
 * 部门模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use think\Model;
use think\model\concern\SoftDelete;

class Department extends Model
{
//    use SoftDelete;
//    protected $deleteTime = 'delete_time';

    /**
     * 读取部门列表数据
     */
    public function getDepartmentList()
    {

    }

    /**
     * 获取顶级部门即公司列表
     * @throws
     */
    public function getDepartmentLevel1List()
    {
        return $this->where(['level' => 1])->order(['sort' => 'ASC'])->select();
    }

    /**
     * 读取公司下业态列表
     * @param string $dept_id1 顶级部门ID 即公司
     * @throws
     */
    public function getDepartmentLevel2ListByDept1ID($dept_id1)
    {
        return $this->where(['parent_id' => $dept_id1,'level' => 2,'delete_time' => NULL])->select();
    }

    /**
     * ID获取部门数据
     * @param $dept_id
     * @throws
     * @return []
     */
    public function getDeptById($dept_id)
    {
        $data = $this->find(['id' => $dept_id]);
        return $data ? $data->toArray() : [];
    }
}
