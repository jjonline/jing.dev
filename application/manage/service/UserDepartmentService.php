<?php
/**
 * 用户所属部门服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-14 23:34
 * @file UserDepartmentService.php
 */

namespace app\manage\service;

use app\common\helpers\ArrayHelper;
use think\Db;

class UserDepartmentService
{

    /**
     * 获取用户所有公司列表|与权限无关，仅读取用户所属的公司
     * @param string $user_id
     * @throws
     * @return []
     */
    public function getUserDept1List($user_id)
    {
        $data = Db::name('user_department user_dept')
              ->join('department dept','dept.id = user_dept.dept_id1')
              ->where(['user_dept.user_id' => $user_id])
              ->field(['dept.name as dept_name','dept.id as dept_id'])
              ->order(['user_dept.create_time' => 'DESC'])
              ->cache('UserDept1List'.$user_id,3600*12)//缓存12小时
              ->group('dept.id')
              ->select()->toArray();
        return $data;
    }

    /**
     * 获取用户所有部门列表|与权限无关，仅读取用户所属的部门，公司管理员要读取该公司下所有部门
     * @param string $user_id
     * @throws
     * @return []
     */
    public function getUserDept2List($user_id)
    {
        $data = Db::name('user_department user_dept')
            ->join('department dept','(dept.parent_id = user_dept.dept_id1 AND user_dept.dept_id2 IS NULL) OR dept.id = user_dept.dept_id2 AND dept.parent_id IS NOT NULL')
            ->where(['user_dept.user_id' => $user_id])
            ->field(['dept.name as dept_name','dept.id as dept_id','user_dept.dept_id1 as parent_id'])
            ->order(['user_dept.create_time' => 'DESC'])
            ->cache('UserDept2List'.$user_id,3600*12)//缓存12小时
            ->group('dept.id')
            ->select()->toArray();
        return $data;
    }

    /**
     * 获取dept_id1即公司级别下的部门和业务员tree
     * @param string $dept_id1
     * @throws
     * @return []
     */
    public function getDeptWithUserTree($dept_id1)
    {
        $data = Db::name('user user')
              ->leftJoin('user_department user_dept','user_dept.user_id = user.id')
              ->join('department dept','dept.id = user_dept.dept_id2')
              ->where(['user_dept.dept_id1' => $dept_id1,'user.enabled' => 1])//用户没有被禁用
              ->where('user_dept.dept_id2', 'not null')
              ->field(['dept.name as dept_name','dept.id as dept_id','user.*'])
              ->select();
        $data = ArrayHelper::group($data->toArray(),'dept_name');
        $nodes = [];
        foreach ($data as $key => $datum) {
            $node         = [];
            $node['name'] = $key;
            $node['open'] = true;
            $child = [];
            foreach ($datum as $value)
            {
                $node['idKey'] = $value['dept_id'];//父节点ID，使用部门ID，即业态ID
                $_child = [];
                $_child['name']  = $value['username'].'('.$value['real_name'].')';
                $_child['idKey'] = $value['id'];//子节点的ID为用户ID
                $_child['idpIdKeyKey'] = $value['dept_id'];//子节点中的`父节点ID`为业态ID
                $_child['open']  = true;//节点默认展开
                $_child['icon']  = '/static/images/user_icon.png';//子节点icon
                $child[] = $_child;
            }
            $node['children'] = $child;

            $nodes[] = $node;
        }
        return $nodes;
    }
}
