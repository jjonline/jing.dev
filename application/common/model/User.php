<?php
/**
 * 用户模型|公共
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\common\model;

use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use think\Db;
use think\exception\DbException;
use think\Model;

class User extends Model
{
    /**
     * 用户ID查找用户信息
     * @param $user_id
     * @return array
     * @throws DbException
     */
    public function getUserInfoById($user_id)
    {
        if (empty($user_id)) {
            return [];
        }
        $data = $this->get(['id' => $user_id]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 用户名查询用户信息
     * @param $user_name
     * @return array
     * @throws DbException
     */
    public function getUserInfoByUserName($user_name)
    {
        if (empty($user_name)) {
            return [];
        }
        $data = $this->get(['user_name' => $user_name]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 邮箱地址查找用户信息
     * @param $email
     * @return array
     * @throws DbException
     */
    public function getUserInfoByEmail($email)
    {
        if (empty($email)) {
            return [];
        }
        $data = $this->get(['email' => $email]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 手机号查找用户信息
     * @param $mobile
     * @return array
     * @throws DbException
     */
    public function getUserInfoByMobile($mobile)
    {
        if (empty($mobile)) {
            return [];
        }
        $data = $this->get(['mobile' => $mobile]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 智能判断参数值是用户名、邮箱还是手机号自动查找用户信息
     * @param $user_unique_key_field_value
     * @return array
     * @throws DbException
     */
    public function getUserInfoAutoByUniqueKey($user_unique_key_field_value)
    {
        if (FilterValidHelper::is_mail_valid($user_unique_key_field_value)) {
            return $this->getUserInfoByEmail($user_unique_key_field_value);
        }
        if (FilterValidHelper::is_phone_valid($user_unique_key_field_value)) {
            return $this->getUserInfoByMobile($user_unique_key_field_value);
        }
        return $this->getUserInfoByUserName($user_unique_key_field_value);
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
        $data = Db::name('user u')
              ->join('department dept', 'dept.id = u.dept_id')
              ->join('role r', 'r.id = u.role_id')
              ->field(['u.*','dept.name as dept_name','r.name as role_name'])
              ->where('u.id', $user_id)
              ->find();
        return $data ? $data : [];
    }

    /**
     * 重新生成|更新用户auth_code值并保存
     * @param mixed $user_id
     * @return bool|static
     */
    public function updateUserAuthCode($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $_user              = [];
        $_user['auth_code'] = GenerateHelper::makeNonceStr(8);
        return $this->update($_user, ['id' => $user_id]);
    }
}
