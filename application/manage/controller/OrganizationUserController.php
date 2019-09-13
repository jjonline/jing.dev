<?php
/**
 * 组织账号控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-02 20:02:00
 * @file OrganizationUserController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\UserService;
use app\manage\model\search\OrganizationUserSearch;
use app\manage\service\RoleService;

class OrganizationUserController extends BaseController
{
    /**
     * 组织账号管理
     * @param OrganizationUserSearch $organizationUserSearch
     * @param RoleService $roleService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(OrganizationUserSearch $organizationUserSearch, RoleService $roleService)
    {
        if ($this->request->isAjax()) {
            $result = $organizationUserSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '组织账号管理 - ' . config('local.site_name'),
            'content_title'    => '组织账号管理',
            'content_subtitle' => '组织账号列表和管理',
            'breadcrumb'       => [
                ['label' => '组织账号管理', 'url' => url('organizationUser/list')],
                ['label' => '组织账号列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 仅能分配当前账号所下辖的部门
        $dept_list = $this->DepartmentService->getAuthDeptTreeList($this->UserInfo['id'], false);
        $user_list = $this->UserService->getAuthUserTreeList($this->UserInfo['id']);
        $role_list = $roleService->getOrgRoleListByUserId($this->UserInfo['id']); // 角色显示所有

        // 所辖部门\用户\角色下拉选项
        $this->assign('dept_list', $dept_list);
        $this->assign('user_list', $user_list);
        $this->assign('role_list', $role_list);

        return $this->fetch();
    }

    /**
     * 新增组织账号
     * @param UserService $userService
     * @return mixed
     */
    public function createAction(UserService $userService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $userService->orgCreateUser($this->request->post('User/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑组织账号
     * @param UserService $userService
     * @return mixed
     */
    public function editAction(UserService $userService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $userService->orgEditUser($this->request->post('User/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 组织账号排序
     * @param UserService $userService
     * @return mixed
     */
    public function sortAction(UserService $userService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $userService->orgSortUser($this->request, $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 组织账号启用禁用
     * @param UserService $userService
     * @return mixed
     */
    public function enableAction(UserService $userService)
    {
        if ($this->request->isAjax()) {
            return $userService->orgEnableUser($this->request, $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }
}
