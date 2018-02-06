<?php
/**
 * 用户所属部门模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use think\Exception;
use think\Model;

class UserDepartment extends Model
{

    /**
     * 获取业态的用户数
     * @param $dept_id2
     * @throws \Think\Exception
     * @return int
     */
    public function getCountUserByDeptId2($dept_id2)
    {
        if(empty($dept_id2))
        {
            throw new Exception('严重错误：业态ID不得为空');
        }
        return $this->where('dept_id2',$dept_id2)->count();
    }

    /**
     * 获取用户部门信息
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserDeptInfoByUserId($user_id)
    {
        $info = $this->where(['user_id' => $user_id])->find();
        return $info ? $info->toArray() : [];
    }

}
