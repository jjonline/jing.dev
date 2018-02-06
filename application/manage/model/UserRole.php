<?php
/**
 * 用户所属角色模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use think\Model;

class UserRole extends Model
{

    /**
     * 获取用户角色信息
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleInfoByUserId($user_id)
    {
        $info = $this->where(['user_id' => $user_id])->find();
        return $info ? $info->toArray() : [];
    }

}
