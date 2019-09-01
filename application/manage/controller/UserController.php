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

class UserController extends BaseController
{
    /**
     * 用户列表
     * @param UserSearch $userSearch
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(UserSearch $userSearch, UserService $userService)
    {
        if ($this->request->isAjax()) {
            // 将当前登录用户信息传递过去
            $result = $userSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '用户管理 - ' . config('local.site_name'),
            'content_title'    => '系统设置',
            'content_subtitle' => '整站后台所有用户列表和管理（特权操作）',
            'breadcrumb'       => [
                ['label' => '系统设置', 'url' => url('user/list')],
                ['label' => '用户管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 仅能分配当前账号所下辖的部门
        $dept_list = $this->DepartmentService->getAuthDeptTreeList($this->UserInfo['id']);
        $user_list = $this->UserService->getAuthUserTreeList($this->UserInfo['id']);
        $role_list = $userService->Role->getRoleList(); // 角色显示所有

        // 所辖部门\用户\角色下拉选项
        $this->assign('dept_list', $dept_list);
        $this->assign('user_list', $user_list);
        $this->assign('role_list', $role_list);

        return $this->fetch();
    }

    /**
     * 超级管理员新增用户
     * @param UserService $userService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(UserService $userService)
    {
        if ($this->request->isAjax()) {
            // 将当前登录用户信息传递过去
            $result = $userService->superUserInsertUser($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
        return $this->renderJson("error");
    }

    /**
     * 编辑后台用户
     * @param UserService $userService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(UserService $userService)
    {
        if ($this->request->isAjax()) {
            // 将当前登录用户信息传递过去
            $result = $userService->superUserUpdateUser($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
        return $this->renderJson("error");
    }

    /**
     * 启用|禁用用户
     * @param UserService $userService
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function enableAction(UserService $userService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $result = $userService->enable($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
        return $this->renderJson('请求失败', 404);
    }
}
