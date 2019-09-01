<?php
/**
 * 用户管理超管部分
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:24
 * @file Super.php
 */

namespace app\common\service\user;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\StringHelper;
use app\common\model\User;
use app\common\model\UserLog;
use app\common\service\LogService;
use app\common\service\UserLogService;
use think\Exception;
use think\Request;

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
            if (!empty($_user['mobile'])) {
                $this->isMobileNotExistOrFail($_user['mobile']);
            }
            if (!empty($_user['email'])) {
                $this->isEmailNotExistOrFail($_user['email']);
            }

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

    /**
     * 超级管理员编辑后台用户
     * @param array $_user    编辑用户的信息变量数组
     * @param array $act_user 当前登录用户数组
     * @return array
     */
    public function superEditUser(array $_user, array $act_user)
    {
        try {
            // 仅超管可操作
            $this->isSuperPermissionOrFail($act_user);

            $rule = [
                'id'        => 'require|number',
                'real_name' => 'require|chsAlphaNum|max:32', // 汉字、字母和数字，内置长度使用 mb_strlen
                'gender'    => 'require|in:-1,0,1',
                'user_name' => 'require|chsAlphaNum|max:32', // 汉字、字母和数字，用户名支持中文
                'password'  => 'length:6,18', // 密码6—18位
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
                'id'        => '编辑用户ID',
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

            // 部门、角色数据存在性
            $this->isDeptExistOrFail($_user['dept_id']);
            $this->isRoleExistOrFail($_user['role_id']);

            // 被编辑用户信息
            $edit_user = $this->User->getFullUserInfoById($_user['id']);
            if (empty($edit_user)) {
                throw new Exception('待编辑用户不存在');
            }

            // 需唯一的值有变更时重复性检查
            if ($edit_user['user_name'] != $_user['user_name']) {
                $this->isUserNameNotExistOrFail($_user['user_name']);
            }
            if (!empty($_user['mobile']) && $edit_user['mobile'] != $_user['mobile']) {
                $this->isMobileNotExistOrFail($_user['mobile']);
            }
            if (!empty($_user['email']) && $edit_user['email'] != $_user['email']) {
                $this->isEmailNotExistOrFail($_user['email']);
            }

            // 只有根用户能编辑根用户的信息
            if (!empty($edit_user['is_root']) && empty($act_user['is_root'])) {
                throw new Exception('您无权限编辑根用户信息');
            }
            // 试图创建根用户检查 -- 只有根用户可指定根用户
            if (!empty($_user['is_root']) && empty($act_user['is_root'])) {
                throw new Exception('您无权限指定根用户');
            }

            // 有修改密码时补充检查密码
            if (!empty($_user['password']) && !FilterValidHelper::isPasswordValid($_user['password'])) {
                throw new Exception('密码必须同时包含字母和数字，6至18位');
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

            // 有更新密码时
            if (!empty($_user['password'])) {
                $user['password']  = $this->generateUserPassword($_user['password']);
                $user['auth_code'] = StringHelper::randString(8);
            }

            // Insert new User
            $affected_rows = $this->User->db()->where('id', $edit_user['id'])->update($user);
            if (empty($affected_rows)) {
                throw new Exception('系统异常，写入数据失败');
            }

            // system log record
            $this->LogService->logRecorder([$_user, $user], '超管编辑用户');
            // user log record
            $this->UserLogService->insert(UserLog::ACTION_UPDATE, [
                'id'      => $edit_user['id'],
                'dept_id' => $user['dept_id']
            ]);

            return $this->success2Array('编辑用户成功');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }

    /**
     * 带部门权限判断的启用|禁用用户
     * @param Request $request
     * @param array   $act_user_info 控制器中的包含菜单、部门权限信息的UserInfo属性数组
     * @return array
     */
    public function superEnableUser(Request $request, $act_user_info = array())
    {
        try {
            // 仅超管可操作
            $this->isSuperPermissionOrFail($act_user_info);

            $user_id       = $request->post('id');
            $multi_user_id = $request->post('multi_id/a'); // 批量启用禁用
            $enable        = $request->post('enable');
            if (empty($user_id) && empty($multi_user_id)) {
                throw new Exception('参数缺失');
            }

            // 单个启用禁用
            if (!empty($user_id) && is_numeric($user_id)) {
                $user            = $this->User->getUserInfoById($user_id);
                $act_dept_vector = $act_user_info['dept_auth']['dept_id_vector'] ?? [];
                if (!in_array($user['dept_id'], $act_dept_vector)) {
                    throw new Exception('您无权限启用或禁用该用户');
                }

                // 启用或禁用用户写入
                $_enable           = [];
                $_enable['id']     = $user['id'];
                $_enable['enable'] = $user['enable'] ? 0 : 1;
                $result            = $this->User->isUpdate(true)->save($_enable);

                if (false !== $result) {
                    $this->LogService->logRecorder($user, '启用或禁用用户');
                    return ['error_code' => 0,'error_msg' => $user['enable'] ? '禁用完成' : '启用完成'];
                }
            }

            // 批量启用禁用--仅根用户可用
            if (!empty($multi_user_id) && in_array($enable, ['0', '1'])) {
                if (!$act_user_info['is_root']) {
                    return ['error_code' => 500,'error_msg' => '操作失败：批量操作仅根用户可用'];
                }
                $multi_user_id = ArrayHelper::filterByCallableThenUnique($multi_user_id, 'intval');
                $result = $this->User->where('id', 'IN', $multi_user_id)->update(['enable' => $enable]);

                if (false !== $result) {
                    $this->LogService->logRecorder([$user_id, $multi_user_id, $enable], '批量启用或禁用用户');
                    return ['error_code' => 0,'error_msg' => empty($enable) ? '批量禁用完成' : '批量启用完成'];
                }
            }
            throw new Exception('操作失败');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }

    /**
     * 超管执行用户排序
     * @param Request $request
     * @param array $act_user
     * @return mixed
     */
    public function superSortUser(Request $request, array $act_user)
    {
        try {
            // 仅超管可操作
            $this->isSuperPermissionOrFail($act_user);

            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }
            $user = $this->User->getUserInfoById($id);
            if (empty($user)) {
                throw new Exception('拟排序的用户数据不存在');
            }
            $effect_rows = $this->User->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder($user, "用户快速排序");

            return $this->success2Array('排序调整成功');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }
}
