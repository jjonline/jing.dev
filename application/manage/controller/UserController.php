<?php
/**
 * 用户管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 10:19
 * @file UserController.php
 */

namespace app\manage\controller;

use app\common\model\UserLog;
use app\manage\model\Department;
use app\manage\model\Role;
use app\manage\model\search\UserSearch;
use app\manage\model\User;
use app\manage\service\UserService;
use think\Request;

class UserController extends BaseController
{

    /**
     * 用户列表管理
     * ---
     * 1、一个用户可以属于多个公司、部门
     * 2、一个用户可以拥有多个角色
     * ---
     * @param Request $request
     * @return mixed
     */
    public function listAction(Request $request , UserSearch $userSearch)
    {
        if($request->isAjax())
        {
            return $userSearch->search($request);
        }
        $this->title            = '用户管理 - '.config('local.site_name');
        $this->content_title    = '用户管理';
        $this->content_subtitle = '管理系统中各角色用户数据';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '用户列表','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        return $this->fetch();
    }

    /**
     * 新建用户
     * @param Request $request
     * @throws
     * @return mixed
     */
    public function createAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            return $userService->insertNewUser($request);
        }
        $this->title            = '新增用户 - '.config('local.site_name');
        $this->content_title    = '新增用户';
        $this->content_subtitle = '新增系统各种角色权限的用户';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '新增用户','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;
        // 顶级部门数据-次级部门数据ajax拉取
        $DeptModel   = new Department();
        $level1_dept = $DeptModel->getDepartmentLevel1List();
        $this->assign('dept',$level1_dept);
        // 角色数据
        $RoleModel   = new Role();
        $role        = $RoleModel->getRoleList();
        $this->assign('role',$role);
        return $this->fetch();
    }

    /**
     * 超级管理员编辑用户
     * @param Request $request
     * @param UserService $userService
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function EditAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            return $userService->EditUser($request);
        }
        $this->title            = '编辑用户 - '.config('local.site_name');
        $this->content_title    = '编辑用户';
        $this->content_subtitle = '编辑用户';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '编辑用户','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;
        // 顶级部门数据-次级部门数据ajax拉取
        $DeptModel   = new Department();
        $level1_dept = $DeptModel->getDepartmentLevel1List();
        $this->assign('dept',$level1_dept);
        // 角色数据
        $RoleModel   = new Role();
        $role        = $RoleModel->getRoleList();
        $this->assign('role',$role);
        // 用户信息
        $user = $userService->User->getFullUserInfoById($request->get('id'));
        if(empty($user))
        {
            $this->redirect(url('user/list'));
        }
        $user_dept = $userService->UserDepartment->getUserDeptInfoByUserId($user['id']);
        $user_role = $userService->UserRole->getRoleInfoByUserId($user['id']);
        if(empty($user_dept) || empty($user_role))
        {
            $this->redirect(url('user/list'));
        }

        $this->assign('user',$user);
        $this->assign('user_dept',$user_dept);
        $this->assign('user_role',$user_role);
        return $this->fetch();
    }

    /**
     * 启用|禁用用户
     * @param Request $request
     * @throws
     * @return mixed
     */
    public function toggleEnabledAction(Request $request)
    {
        $UserModel = new User();
        $user = $UserModel->getDataById($request->param('id'));
        if(empty($user) || $user['enabled'] == $request->param('enabled'))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '用户不存在或状态有误']);
        }
        $ret = $UserModel->isUpdate(true)->save(['enabled' => $user['enabled'] ? 0 : 1],['id' => $user['id']]);
        return $ret !== false ? ['error_code' => 0,'error_msg' => '操作成功'] : ['error_code' => -1,'error_msg' => '系统异常'];
    }

    /**
     * 为公司管理员分配设备额度
     * @param Request $request
     * @throws
     * @return
     */
    public function QuotaAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $userService->allocatedUserDeviceQuotaBySupper($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DEVICE_QUOTA_ALLOCATED,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

}