<?php
/**
 * Dev模式的会员管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 10:08
 * @file UserController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\UserService;
use app\manage\model\search\UserSearch;
use think\Request;

class UserController extends BaseController
{
    /**
     * 用户列表
     * @param Request $request
     * @param UserSearch $userSearch
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(Request $request,UserSearch $userSearch,UserService $userService)
    {
        if($request->isAjax())
        {
            // 将当前登录用户信息传递过去
            $result = $userSearch->list($this->UserInfo);
            return $this->asJson($result);
        }
        $this->title            = '用户管理 - '.config('local.site_name');
        $this->content_title    = '用户列表';
        $this->content_subtitle = '后台用户列表管理';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '用户列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        // 仅能分配当前账号所下辖的部门
        $dept_list = $this->UserInfo['dept_auth']['dept_list_tree'];
        $role_list = $userService->Role->getRoleList(); // 角色显示所有

        $this->assign('dept_list',$dept_list);
        $this->assign('role_list',$role_list);

        return $this->fetch();
    }

    /**
     * 超级管理员新增用户
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(Request $request,UserService $userService)
    {
        if($request->isAjax())
        {
            // 将当前登录用户信息传递过去
            $result = $userService->superUserInsertUser($request);
            return $this->asJson($result);
        }
        $this->title            = '用户管理 - '.config('local.site_name');
        $this->content_title    = '新增用户';
        $this->content_subtitle = '新增后台用户';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '新增用户','url'  => url('user/create')],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        // 仅能分配当前账号所下辖的部门
        $dept_list = $this->UserInfo['dept_auth']['dept_list_tree'];
        $role_list = $userService->Role->getRoleList(); // 角色显示所有

        $this->assign('dept_list',$dept_list);
        $this->assign('role_list',$role_list);

        return $this->fetch();
    }

    /**
     * 编辑后台用户
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(Request $request,UserService $userService)
    {
        if($request->isAjax())
        {
            // 将当前登录用户信息传递过去
            $result = $userService->superUserUpdateUser($request,$this->UserInfo);
            return $this->asJson($result);
        }
        $this->title            = '用户管理 - '.config('local.site_name');
        $this->content_title    = '编辑用户';
        $this->content_subtitle = '编辑后台用户（此处不涉及职员信息的维护，仅维护后台账号信息）';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '编辑用户','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $user = $userService->User->getUserInfoById($request->get('id'));
        if(empty($user) || !in_array($user['dept_id'],$this->UserInfo['dept_auth']['dept_id_vector']))
        {
            $this->error('您无权限编辑该账户信息');
        }

        // 待编辑用户信息
        $this->assign('user',$user);

        // 仅能分配当前账号所下辖的部门
        $dept_list = $this->UserInfo['dept_auth']['dept_list_tree'];
        $role_list = $userService->Role->getRoleList(); // 角色显示所有

        $this->assign('dept_list',$dept_list);
        $this->assign('role_list',$role_list);

        return $this->fetch();
    }

    /**
     * 启用|禁用用户
     * @param Request $request
     * @param UserService $userService
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function enableToggleAction(Request $request,UserService $userService)
    {
        if($request->isPost() && $request->isAjax())
        {
            $result = $userService->enableUserToggle($request->post('id/i'),$this->UserInfo);
            return $this->asJson($result);
        }
        return $this->renderJson('请求失败',404);
    }
}
