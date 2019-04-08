<?php
/**
 * 会员前后台统一验证器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-08 21:58
 * @file CustomerValidate.php
 */

namespace app\common\validate;

use think\Validate;

class CustomerValidate extends Validate
{
    protected $rule = [
        'customer_name'    => ['require', 'alphaNum', 'max' => 64],
        'real_name'        => ['require', 'chsDash', 'max' => 255],
        'reveal_name'      => ['require', 'chsDash', 'max' => 255],
        'mobile'           => ['mobile'],
        'email'            => ['email'],
        'gender'           => ['require', 'in' => '-1,0,1'],
        'birthday'         => ['date'],
        'age'              => ['number', 'min' => 0, 'max' => 100],
        'province'         => ['chsDash', 'max' => 32],
        'city'             => ['chsDash', 'max' => 32],
        'district'         => ['chsDash', 'max' => 32],
        'location'         => ['chsDash', 'max' => 255],
        'job_organization' => ['chsDash', 'max' => 128],
        'job_number'       => ['alphaDash', 'max' => 32],
        'job_location'     => ['chsDash', 'max' => 255],
        'remark'           => ['chsDash', 'max' => 255],
        'motto'            => ['chsDash', 'max' => 64],
        'dept_id'          => ['number', 'gt' => 0],
        'user_id'          => ['number', 'gt' => 0],
        'enable'           => ['number', 'in' => '0,1'],
        'figure_id'        => ['alphaDash', 'length' => '36,36'],
    ];
    protected $message = [
        'customer_name.require'    => '用户名不得为空',
        'customer_name.alphaNum'   => '用户名只能是数字和字母构成',
        'customer_name.max'        => '用户名最大64字符',
        'real_name.require'        => '真实姓名不得为空',
        'real_name.chsDash'        => '真实姓名只能是中文和字母下划线构成',
        'real_name.max'            => '真实姓名最大255字符',
        'reveal_name.require'      => '昵称不得为空',
        'reveal_name.chsDash'      => '昵称只能是中文和字母下划线构成',
        'reveal_name.max'          => '昵称最大255字符',
        'mobile.mobile'            => '手机号格式有误',
        'email.email'              => '邮箱格式有误',
        'gender.require'           => '性别不得为空',
        'gender.in'                => '性别数据格式有误',
        'birthday.date'            => '生日格式为年月日',
        'age.number'               => '年龄只能是数字',
        'age.min'                  => '年龄数字范围仅支持0至100',
        'age.max'                  => '年龄数字范围仅支持0至100',
        'province.chsDash'         => '省份只能是中文',
        'province.max'             => '省份不得大于32字符',
        'city.chsDash'             => '市只能是中文',
        'city.max'                 => '市不得大于32字符',
        'district.chsDash'         => '县只能是中文',
        'district.max'             => '县不得大于32字符',
        'location.chsDash'         => '归属地详细地址只能是中文',
        'location.max'             => '归属地详细地址不得大于255字符',
        'job_organization.chsDash' => '工作单位只能是中文',
        'job_organization.max'     => '工作单位不得大于255字符',
        'job_number.alphaDash'     => '工作电话只能是数字和中划线构成',
        'job_number.max'           => '工作电话不得大于32字符',
        'job_location.chsDash'     => '工作单位地址只能是中文',
        'job_location.max'         => '工作单位地址不得大于255字符',
        'remark.chsDash'           => '备注只能是中文',
        'remark.max'               => '备注不得大于255字符',
        'motto.chsDash'            => '一句话简介只能是中文',
        'motto.max'                => '一句话简介不得大于64字符',
        'dept_id.number'           => '管理者所属部门有误',
        'dept_id.gt'               => '管理者所属部门有误',
        'user_id.number'           => '管理者有误',
        'user_id.gt'               => '管理者有误',
        'enable'                   => '启用禁用有误',
        'figure_id'                => '头像ID有误',

    ];
    protected $scene = [];
}
