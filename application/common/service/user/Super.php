<?php
/**
 * 用户管理超管部分
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:24
 * @file Super.php
 */

namespace app\common\service\user;

use app\common\helper\FilterValidHelper;
use app\common\helper\StringHelper;
use app\common\model\User;
use app\common\model\UserLog;
use app\common\service\LogService;
use app\common\service\UserLogService;
use think\Exception;

trait Super
{
    /**
     * @var User
     */
    public $User;
    /**
     * @var LogService
     */
    public $LogService;
    /**
     * @var UserLogService
     */
    public $UserLogService;

    /**
     * 超级管理员创建后台用户
     * @param array $_user    创建用户信息变量数组
     * @param array $act_user 当前登录用户数组
     * @return array
     */
    public function superCreateUser(array $_user, array $act_user)
    {
        try {
            // 仅超管可操作
            $this->isSuperPermissionOrFail($act_user);

            $rule = [
                'real_name' => 'require|chsAlphaNum|max:32', // 汉字、字母和数字，内置长度使用 mb_strlen
                'gender'    => 'require|in:-1,0,1',
                'user_name' => 'require|chsAlphaNum|max:32', // 汉字、字母和数字，用户名支持中文
                'password'  => 'require|length:6,18', // 密码6—18位
                'mobile'    => 'mobile',
                'email'     => 'email|max:128',
                'telephone' => 'alphaDash|length:6,12', // 字母、数字_-构成，长度6至12位
                'role_id'   => 'require|number',
                'dept_id'   => 'require|number',
                'remark'    => 'max:255',
                'is_root'   => 'accepted', // on 或 空
                'is_leader' => 'accepted', // on 或 空
                'enable'    => 'accepted', // on 或 空
            ];
            $column = [
                'real_name' => '真实姓名',
                'gender'    => '性别',
                'user_name' => '用户名',
                'password'  => '登录密码',
                'mobile'    => '手机号',
                'email'     => '邮箱',
                'telephone' => '座机号码',
                'role_id'   => '所属角色',
                'dept_id'   => '所属部门',
                'remark'    => '备注',
                'is_root'   => '是否根用户',
                'is_leader' => '是否部门领导',
                'enable'    => '是否启用',
            ];
            $this->checkRequestVariablesOrFail($_user, $rule, $column);

            // 补充检查密码
            if (!FilterValidHelper::isPasswordValid($_user['password'])) {
                throw new Exception('密码必须同时包含字母和数字，6至18位');
            }

            // 部门、角色数据存在性
            $this->isDeptExistOrFail($_user['dept_id']);
            $this->isRoleExistOrFail($_user['role_id']);

            // 重复性检查
            $this->isUserNameNotExistOrFail($_user['user_name']);
            $this->isMobileNotExistOrFail($_user['mobile']);
            $this->isEmailNotExistOrFail($_user['email']);

            // 选择了创建根用户检查
            if (!empty($_user['is_root']) && empty($act_user['is_root'])) {
                throw new Exception('只有根用户能创建根用户');
            }

            // 构建用户信息数组
            $user              = [];
            $user['real_name'] = $_user['real_name'];
            $user['gender']    = $_user['gender'];
            $user['user_name'] = $_user['user_name'];
            $user['mobile']    = $_user['mobile'];
            $user['email']     = $_user['email'];
            $user['telephone'] = $_user['telephone'];
            $user['role_id']   = $_user['role_id'];
            $user['dept_id']   = $_user['dept_id'];
            $user['remark']    = $_user['remark'];
            $user['enable']    = empty($_user['enable']) ? 0 : 1;
            $user['is_root']   = empty($_user['is_root']) ? 0 : 1;
            $user['is_leader'] = empty($_user['is_leader']) ? 0 : 1;

            // 补充用户数据
            $user['create_user_id'] = $act_user['id'];
            $user['create_dept_id'] = $_user['dept_id'];
            $user['password']       = $this->generateUserPassword($_user['password']);
            $user['auth_code']      = StringHelper::randString(8);


            // Insert new User
            $user_id = $this->User->db()->insertGetId($user);
            if (empty($user_id)) {
                throw new Exception('系统异常，写入数据失败');
            }

            // system log record
            $this->LogService->logRecorder([$_user, $user], '超管新增用户');
            // user log record
            $this->UserLogService->insert(UserLog::ACTION_CREATE, [
                'id'      => $user_id,
                'dept_id' => $user['dept_id']
            ]);

            return $this->success2Array('新增用户成功，初始密码为：'.$_user['password']);
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }
}
