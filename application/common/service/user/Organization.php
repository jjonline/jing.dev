<?php
/**
 * 用户管理组织部门权限部分
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:28
 * @file Organization.php
 */

namespace app\common\service\user;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\StringHelper;
use app\common\model\User;
use app\common\model\UserLog;
use app\common\service\DepartmentService;
use app\common\service\LogService;
use app\common\service\UserLogService;
use app\manage\service\RoleService;
use think\Exception;
use think\Request;

trait Organization
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
     * 组织管理员创建后台用户
     * @param array $_user    创建用户信息变量数组
     * @param array $act_user 当前登录用户数组
     * @return array
     */
    public function orgCreateUser(array $_user, array $act_user)
    {
        try {
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

            // 检查org用户是否有权限操作对应部门、角色的权限
            if (!$this->isOrgUserCanDealDept($_user['dept_id'], $act_user['id'], true)) {
                // 不能新建平级部门
                throw new Exception('您仅能分配您所属部门的子部门');
            }
            if (!$this->isOrgUserCanDealRole($_user['role_id'], $act_user['id'])) {
                // 不能新建平级部门
                throw new Exception('您无权限分配该角色');
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
            $user['is_root']   = 0;
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
            $this->LogService->logRecorder([$_user, $user], '新增用户');
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
    public function orgEditUser(array $_user, array $act_user)
    {
        try {
            $rule = [
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
                'is_leader' => '是否部门领导',
                'enable'    => '是否启用',
            ];
            $this->checkRequestVariablesOrFail($_user, $rule, $column);

            if ($_user['id'] == $act_user['id']) {
                throw new Exception('若需要修改您自己的个人信息请前往个人中心操作');
            }

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

            // 检查org用户是否有权限操作对应部门、角色的权限
            if (!$this->isOrgUserCanDealDept($_user['dept_id'], $act_user['id'], true)) {
                // 不能编辑自己账号外的账号的平级部门
                throw new Exception('您仅能分配您所属部门的子部门');
            }
            if (!$this->isOrgUserCanDealRole($_user['role_id'], $act_user['id'])) {
                // 不能新建平级部门
                throw new Exception('您无权限分配该角色');
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
            $user['is_root']   = 0;
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
            $this->LogService->logRecorder([$_user, $user], '编辑用户');
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
    public function orgEnableUser(Request $request, $act_user_info = array())
    {
        try {
            $user_id       = $request->post('id');
            $multi_user_id = $request->post('multi_id/a'); // 批量启用禁用
            $enable        = $request->post('enable');

            $multi_user_id[] = $user_id;
            $multi_user_id   = ArrayHelper::filterByCallableThenUnique($multi_user_id, 'intval');
            if (empty($multi_user_id)) {
                throw new Exception('参数缺失');
            }

            if (!$this->isOrgUserCanDealUsers($multi_user_id, $act_user_info)) {
                throw new Exception('您无权限操作该用户');
            }
            $enable = $enable ? 1 : 0;
            $result = $this->User->where('id', 'IN', $multi_user_id)->update(['enable' => $enable]);
            if (false !== $result) {
                $this->LogService->logRecorder([$multi_user_id, $enable], '启用或禁用用户');
                return ['error_code' => 0,'error_msg' => empty($enable) ? '禁用完成' : '启用完成'];
            }
            throw new Exception('操作失败');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }

    /**
     * 超管执行用户排序
     * @param Request $request
     * @param array $act_user_info
     * @return mixed
     */
    public function orgSortUser(Request $request, array $act_user_info)
    {
        try {
            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }
            $user = $this->User->getUserInfoById($id);
            if (empty($user)) {
                throw new Exception('拟排序的用户数据不存在');
            }

            // 是否有权限操作该用户
            if (!$this->isOrgUserCanDealUsers([$id], $act_user_info)) {
                throw new Exception('您无权限操作该用户');
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

    /**
     * 检查指定用户id数组是否能被指定用户所操作管理
     * @param array $users 待检查的用户id数组
     * @param array $act_user_info 操作用户的操作信息
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isOrgUserCanDealUsers(array $users, array $act_user_info)
    {
        $can_users = $this->User->getAuthFullUserList($act_user_info);
        $can_manage_multi_id = ArrayHelper::arrayColumnThenUnique($can_users, 'id');
        foreach ($users as $user_id) {
            if (!in_array($user_id, $can_manage_multi_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查指定用户是否有操作指定部门的权限
     * @param integer $dept_id     待检查的部门id
     * @param integer $act_user_id 被检查的用户id
     * @param bool $without_self   检查时是否包含该用户所属的部门
     * @return bool
     */
    protected function isOrgUserCanDealDept($dept_id, $act_user_id, $without_self = false)
    {
        $can_manage_dept = app(DepartmentService::class)->getAuthDeptTreeList($act_user_id, $without_self);
        $can_manage_multi_id = ArrayHelper::arrayColumnThenUnique($can_manage_dept, 'id');

        return in_array($dept_id, $can_manage_multi_id);
    }

    /**
     * 检查指定用户是否有操作指定角色的权限
     * @param integer $role_id     待检查的角色id
     * @param integer $act_user_id 被检查的用户id
     * @return bool
     */
    protected function isOrgUserCanDealRole($role_id, $act_user_id)
    {
        $can_manage_role = app(RoleService::class)->getOrgRoleListByUserId($act_user_id);
        $can_manage_multi_id = ArrayHelper::arrayColumnThenUnique($can_manage_role, 'id');

        return in_array($role_id, $can_manage_multi_id);
    }
}
