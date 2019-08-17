<?php
/**
 * 部门模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:20
 * @file Department.php
 */

namespace app\common\model;

use app\common\helper\TreeHelper;
use think\Model;

class Department extends Model
{
    use PermissionsTrait;

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
        if (empty($id)) {
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
        if (empty($parent_id)) {
            return [];
        }
        $dept = $this->where('parent_id', $parent_id)->select();
        return !$dept->isEmpty() ? $dept->toArray() : [];
    }

    /**
     * 用id数组获取部门
     * @param $ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptListByIds($ids)
    {
        if ($ids instanceof \stdClass) {
            $ids = (array) $ids;
        }
        if (empty($ids) || !is_array($ids)) {
            return [];
        }
        $data = $this->where('id', 'IN', $ids)->select();
        return $data->isEmpty() ? [] : $data->toArray();
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
        if (!$dept->isEmpty()) {
            return $dept->toArray();
        }
        return [];
    }

    /**
     * 带权限获取部门列表
     * @param array $act_user
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAuthFullDeptList(array $act_user)
    {
        $query = $this->db()->alias('dept')
            ->field([
                'dept.*',
                'user.real_name',
                'department.name as dept_name','user.real_name',
                'department.name as dept_name',
            ])
            ->leftJoin('user user', 'user.id = dept.user_id')
            ->leftJoin('department department', 'department.id = dept.dept_id');

        // 数据权限限定
        $this->permissionsLimitOrDeptSearch(
            $query,
            'dept.dept_id',
            'dept.user_id',
            $act_user
        );

        return $query->select()->toArray();
    }

    /**
     * @param $keyword
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchDeptList($keyword)
    {
        $Query = $this->field([
            '*'
        ]);
        if (!empty($keyword)) {
            $Query->where('name', 'like', '%'.$keyword.'%');
        }
        $data = $Query->order(['sort' => 'ASC','create_time' => 'DESC'])
            ->limit(20)
            ->select();
        return ['error_code' => 0,'error_msg' => 'Ok','data' => $data->toArray()];
    }

    /**
     * 通过部门id 查询该部门下的所有子部门id数组
     * @param $parent_id
     * @return array [1,2,3,5,12]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChildDeptByParentId($parent_id)
    {
        if (empty($parent_id)) {
            return [];
        }

        $dept_list   = $this->getDeptList(); // 所有部门信息
        $vector_list = TreeHelper::child($dept_list, $parent_id); // 递归$parent_id的所有子部门

        $dept_ids    = [];

        // 获取所有子部门ID
        foreach ($vector_list as $key => $value) {
            $dept_ids[] = $value['id'];
        }

        return $dept_ids;
    }

    /**
     * 通过部门id 查询该部门下的所有子部门id和自己本身构成的一维数组
     * @param $parent_id
     * @return array [$parent_id,2,3,5,12]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptChildAndSelfIdArrayById($parent_id)
    {
        if (empty($parent_id)) {
            return [];
        }

        $dept_list   = $this->getDeptList(); // 所有部门信息
        $vector_list = TreeHelper::child($dept_list, $parent_id); // 递归$parent_id的所有子部门

        $dept_ids    = [];
        // 将指定部门id加入第一个元素
        $dept_ids[]  = $parent_id;
        // 获取所有子部门ID
        foreach ($vector_list as $key => $value) {
            $dept_ids[] = $value['id'];
        }

        return $dept_ids;
    }

    /**
     * 拿指定父Id 和 指定level 取到指定等级的部门id
     * @param $parent_id
     * @param $level
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParentDeptByIdAndLevel($parent_id, $level)
    {
        if (empty($parent_id) || empty($level)) {
            return '';
        }
        $parent_dept = $this->getDeptInfoById($parent_id);
        if (empty($parent_dept)) {
            return '';
        }
        if ($parent_dept['level'] != $level) {
            $dept_id = $this->getParentDeptByIdAndLevel($parent_dept['parent_id'], $level);
        } else {
            $dept_id = $parent_dept['id'];
        }
        return $dept_id;
    }

    /**
     * SQL直接查询子节点的指定层级的父节点数据[树结构，只会存在0条或1条数据]
     * @param int $child_dept_id  子节点ID
     * @param int $limit_level    要查找的该子节点的父节点层级数字
     * @return array
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function getParentInfoByChildIdAndParentLevel($child_dept_id, $limit_level)
    {
        $sql = "SELECT 
                    T2.id,
                    T2.parent_id, 
                    T2.name,
                    T2.level
                FROM
                    (
                        SELECT
                            @child_id AS _id ,
                            (
                                SELECT
                                    @child_id := parent_id
                                FROM
                                    pro_department
                                WHERE
                                    id = _id
                            ) AS parent_id ,
                            @level := @level + 1 AS lvl
                        FROM
                            (SELECT @child_id := {$child_dept_id} , @level := 0) vars ,
                            pro_department h
                        WHERE
                            @child_id <> 0
                    ) T1
                JOIN pro_department T2 ON T1._id = T2.id
                WHERE T2.level = {$limit_level}
                ORDER BY id LIMIT 0,1";
        $result = $this->db()->query($sql);
        return !empty($result[0]) ? $result[0] : [];
    }
}
