<?php
/**
 * 会员中心
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-11 00:00:41
 * @file
 */

namespace app\manage\controller;


use app\common\model\UserLog;
use app\manage\model\search\DepartmentSearch;
use app\manage\model\search\UserSearch;
use app\manage\service\DepartmentService;
use app\manage\service\UserDepartmentService;
use app\manage\service\UserService;
use think\Request;

class MineController extends BaseController
{

    /**
     * 个人中心
     * @param UserService $userService
     * @param UserDepartmentService $userDepartmentService
     * @return mixed
     */
    public function homeAction(UserService $userService,UserDepartmentService $userDepartmentService)
    {
        $this->title            = '个人中心 - '.config('local.site_name');
        $this->content_title    = '个人中心';
        $this->content_subtitle = '个人账号信息管理';
        $this->breadcrumb       = [
            ['label' => '会员中心','url' => url('mine/home')],
            ['label' => '个人中心','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $user_id   = session('user_id');
        $user_info = $userService->User->getFullUserInfoById($user_id);

        $dept1     = $userDepartmentService->getUserDept1List($user_id);
        $dept2     = $userDepartmentService->getUserDept2List($user_id);

        $this->assign('user_info',$user_info);
        $this->assign('dept1',$dept1);
        $this->assign('dept2',$dept2);

        return $this->fetch();
    }

    public function EditAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $userService->updateUserInfo($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::EDIT_USER_INFO,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 个人账号所属的部门列表管理
     */
    public function departmentAction(Request $request,DepartmentSearch $departmentSearch)
    {
        if($request->isAjax())
        {
            return $departmentSearch->searchMineDepartMent($request);
        }
        $this->title            = '部门列表 - '.config('local.site_name');
        $this->content_title    = '部门列表';
        $this->content_subtitle = '管理本公司部门信息';
        $this->breadcrumb       = [
            ['label' => '部门列表','url' => url('mine/department')],
            ['label' => '个人中心','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 新增业态部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return array|mixed
     */
    public function DepartmentCreateAction(Request $request,DepartmentService $departmentService)
    {
        if($request->isAjax())
        {
            $result = $departmentService->saveDept2($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::EDIT_DEPARTMENT,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        $this->title            = '新建业态部门 - '.config('local.site_name');
        $this->content_title    = '新建业态部门';
        $this->content_subtitle = '新建业态部门';
        $this->breadcrumb       = [
            ['label' => '部门列表','url' => url('mine/department')],
            ['label' => '新建业态部门','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 编辑业态部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return mixed
     */
    public function DepartmentEditAction(Request $request,DepartmentService $departmentService)
    {
        if($request->isAjax())
        {
            $result = $departmentService->saveDept2($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::EDIT_DEPARTMENT,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        $this->title            = '编辑业态部门 - '.config('local.site_name');
        $this->content_title    = '编辑业态部门';
        $this->content_subtitle = '编辑业态部门';
        $this->breadcrumb       = [
            ['label' => '部门列表','url' => url('mine/department')],
            ['label' => '编辑业态部门','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $dept = $departmentService->Department->getDeptById($request->get('id'));
        if(empty($dept) || empty($dept['parent_id']))
        {
            $this->redirect(url('mine/department'));
        }

        $this->assign('dept',$dept);
        return $this->fetch();
    }

    /**
     * 公司管理员删除业态
     * @param Request $request
     * @param DepartmentService $departmentService
     * @throws
     * @return mixed
     */
    public function DepartmentDeleteAction(Request $request,DepartmentService $departmentService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $departmentService->deleteDept2($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_DEPT2,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 公司下的业务员列表管理
     * @param Request $request
     * @param UserSearch $userSearch
     * @throws
     * @return mixed
     */
    public function UserListAction(Request $request,UserSearch $userSearch)
    {
        if($request->isAjax())
        {
            return $userSearch->searchMineUser($request);
        }
        $this->title            = '子账号列表 - '.config('local.site_name');
        $this->content_title    = '子账号列表';
        $this->content_subtitle = '子账号列表';
        $this->breadcrumb       = [
            ['label' => '子账号列表','url' => url('mine/userlist')],
            ['label' => '子账号列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 公司管理员新增子账号
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function UserCreateAction(Request $request,UserService $userService,DepartmentService $departmentService)
    {
        if($request->isAjax())
        {
            $result = $userService->saveDept2User($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::INSERT_DEP2USER,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新增子账号 - '.config('local.site_name');
        $this->content_title    = '新增子账号';
        $this->content_subtitle = '新增子账号';
        $this->breadcrumb       = [
            ['label' => '子账号列表','url' => url('mine/userlist')],
            ['label' => '新增子账号','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        // 业态列表
        $default_dept1 = session('default_dept1');
        $dept2_list = $departmentService->getDep2ListByDept1Id($default_dept1['dept_id']);

        $this->assign('dept2_list',$dept2_list);
        return $this->fetch();
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @param DepartmentService $departmentService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function UserEditAction(Request $request,UserService $userService,DepartmentService $departmentService)
    {
        if($request->isAjax())
        {
            $result = $userService->EditDept2User($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::EDIT_DEP2USER,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        $this->title            = '编辑子账号 - '.config('local.site_name');
        $this->content_title    = '编辑子账号';
        $this->content_subtitle = '编辑子账号';
        $this->breadcrumb       = [
            ['label' => '子账号列表','url' => url('mine/userlist')],
            ['label' => '编辑子账号','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        // 业态列表
        $default_dept1 = session('default_dept1');
        $dept2_list = $departmentService->getDep2ListByDept1Id($default_dept1['dept_id']);

        $user = $userService->User->getFullUserInfoById($request->get('id'));
        if(empty($user) || count($user['department']) > 1 || empty($user['department'][0]['dept_id2']))
        {
            $this->redirect(url('mine/userlist'));
        }

        $this->assign('user',$user);
        $this->assign('dept2',$user['department'][0]);
        $this->assign('dept2_list',$dept2_list);

        return $this->fetch();
    }

    /**
     * 删除业态用户
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     */
    public function UserDeleteAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $userService->deleteDept2User($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_DEP2USER,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 子账号设备额度分配
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function UserQuotaAction(Request $request,UserService $userService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $userService->allocatedUserDeviceQuota($request);
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
