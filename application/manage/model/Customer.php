<?php
/**
 * 网站会员模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file Customer.php
 */

namespace app\manage\model;

use app\common\helper\FilterValidHelper;
use think\Model;

class Customer extends Model
{
    /**
     * 主键查询
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->where('id', $id)->find();
        return empty($result) ? [] : $result->toArray();
    }

    /**
     * 用户名查询用户信息
     * @param $user_name
     * @return array
     */
    public function getCustomerInfoByUserName($user_name)
    {
        if (empty($user_name)) {
            return [];
        }
        $data = $this->get(['customer_name' => $user_name]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 邮箱地址查找用户信息
     * @param $email
     * @return array
     */
    public function getCustomerInfoByEmail($email)
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
     */
    public function getCustomerInfoByMobile($mobile)
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
     */
    public function getCustomerInfoAutoByUniqueKey($user_unique_key_field_value)
    {
        if (FilterValidHelper::is_mail_valid($user_unique_key_field_value)) {
            return $this->getCustomerInfoByEmail($user_unique_key_field_value);
        }
        if (FilterValidHelper::is_phone_valid($user_unique_key_field_value)) {
            return $this->getCustomerInfoByMobile($user_unique_key_field_value);
        }
        return $this->getCustomerInfoByUserName($user_unique_key_field_value);
    }
}
