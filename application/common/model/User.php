<?php
/**
 * 用户模型|公共
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\common\model;

use app\common\helpers\GenerateHelper;
use think\exception\DbException;
use think\Model;

class User extends Model
{
    /**
     * 用户ID查找用户信息
     * @param $user_id string 用户ID
     * @throws DbException
     * @return []
     */
    public function getDataById($user_id)
    {
        $data = $this->get(['id' => $user_id]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 用户名查询用户信息
     * @param $user_name string
     * @throws
     * @return []
     */
    public function getDataByUserName($user_name)
    {
        $data = $this->get(['username' => $user_name]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 重新生成|更新用户auth_code值并保存
     * @param $User []|UserModel
     */
    public function updateUserAuthCode($User)
    {
        if(empty($User['id']))
        {
            return false;
        }
        $_user              = [];
        $_user['auth_code'] = GenerateHelper::makeNonceStr(8);
        return $this->update($_user,['id' => $User['id']]);
    }

    /**
     * 通过用户主键ID查询包含用户角色、部门的完整信息
     * ['role' => ['xx1' => '','xx2' => ''],'department' => ['yy1','yy2']]
     * @param string $user_id 用户主键ID
     * @throws
     * @return []
     */
    public function getFullUserInfoById($user_id)
    {
        /*
        $data = $this->db()->name('user user')
              ->leftJoin('user_role user_role','user_role.user_id = user.id')
              ->leftJoin('role role','role.name = user_role.role_name')
              ->leftJoin('user_department user_dept','user_dept.user_id')
              ->leftJoin('department dept','dept.id = user_dept.dept_id1 OR dept.id = user_dept.dept_id2')
              ->order(['dept.level' => 'ASC'])
              ->where(['user.id' => $user_id])
              ->field(['user.*','role.name as role_name','dept.name as dept_name','dept.id as dept_id'])
              ->select();
        */
        $user = $this->getDataById($user_id);
        if(empty($user))
        {
            return [];
        }
        $user['role'] = $this->db()->name('user_role')
            ->where(['user_id' => $user_id])
            ->field(['id','role_name'])
            ->find()->toArray();//一个用户只能有一个角色
        if(!empty($user['role']))
        {
            $user['role_name'] = $user['role']['role_name'];
        }else {
            $user['role_name'] = '[无角色]';
        }
        $user['department'] = $this->db()->name('user_department')
            ->where(['user_id' => $user_id])
            ->order(['dept_id2' => 'DESC'])
            ->field(['id','dept_id1','dept_id2'])
            ->select()->toArray();
        return $user;
    }
}
