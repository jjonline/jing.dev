<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-29 14:34
 * @file Member.php
 */

namespace app\common\model;

use app\common\helper\FilterValidHelper;
use think\Model;

class Member extends Model
{
    /**
     * ID查询
     * @param $member_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberInfoById($member_id)
    {
        if (empty($member_id)) {
            return [];
        }
        $data = $this->find($member_id);
        return $data ? $data->toArray() : [];
    }

    /**
     * 通过用户名查找
     * @param $user_name
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberInfoByUserName($user_name)
    {
        if (empty($user_name)) {
            return [];
        }
        $data = $this->where('user_name', $user_name)->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 邮箱地址查找用户信息
     * @param $email
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberInfoByEmail($email)
    {
        if (empty($email)) {
            return [];
        }
        $data = $this->find(['email' => $email]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 手机号查找用户信息
     * @param $mobile
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberInfoByMobile($mobile)
    {
        if (empty($mobile)) {
            return [];
        }
        $data = $this->find(['mobile' => $mobile]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 智能判断参数值是用户名、邮箱还是手机号自动查找用户信息
     * @param $user_unique_key_field_value
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserInfoAutoByUniqueKey($user_unique_key_field_value)
    {
        if (FilterValidHelper::is_mail_valid($user_unique_key_field_value)) {
            return $this->getMemberInfoByEmail($user_unique_key_field_value);
        }
        if (FilterValidHelper::is_phone_valid($user_unique_key_field_value)) {
            return $this->getMemberInfoByMobile($user_unique_key_field_value);
        }
        return $this->getMemberInfoByUserName($user_unique_key_field_value);
    }
}
