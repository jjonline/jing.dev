<?php
/**
 * 后台角色管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-23 11:09:29
 * @file RoleController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\model\Role;
use app\manage\service\RoleService;

class RoleController extends BaseController
{

    /**
     * 角色列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction()
    {
        $common = [
            'title'            => '人事设置 - ' . config('local.site_name'),
            'content_title'    => '角色管理',
            'content_subtitle' => '系统角色管理工具',
            'breadcrumb'       => [
                ['label' => '人事设置', 'url' => url('role/list')],
                ['label' => '角色管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $RoleModel = new Role();
        $list      = $RoleModel->getRoleList();

        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 新增角色
     * @param RoleService $roleService
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(RoleService $roleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 保存角色
            return $roleService->save($this->request);
        }
        $common = [
            'title'            => '人事设置 - ' . config('local.site_name'),
            'content_title'    => '新增角色',
            'content_subtitle' => '人事设置-新增角色',
            'breadcrumb'       => [
                ['label' => '人事设置', 'url' => url('role/list')],
                ['label' => '角色管理', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $menu_list = $roleService->getRoleMenuTreeDataByUserId($this->UserInfo['id']);
        $this->assign('menu_list', $menu_list);
        return $this->fetch();
    }

    /**
     * 修改角色
     * @param RoleService $roleService
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(RoleService $roleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 保存角色
            return $roleService->save($this->request);
        }
        $common = [
            'title'            => '角色管理 - ' . config('local.site_name'),
            'content_title'    => '编辑角色',
            'content_subtitle' => '人事设置-角色编辑',
            'breadcrumb'       => [
                ['label' => '角色管理', 'url' => url('role/list')],
                ['label' => '编辑角色', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $role_id   = $this->request->get('id');

        // 当前登录用户所具备的角色菜单权限--内部自动处理根用户权限情况
        $menu_list = $roleService->getRoleMenuTreeDataByUserId($this->UserInfo['id']);
        $this->assign('menu_list', $menu_list);

        // 待编辑的菜单权限
        $role_menu = $roleService->RoleMenu->getRoleMenuListByRoleId($role_id);
        $this->assign('role_menu', $role_menu);

        // 角色本身数据
        $this->assign('role', $roleService->Role->getRoleInfoById($role_id));
        return $this->fetch();
    }

    /**
     * 角色排序
     * @param RoleService $roleService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(RoleService $roleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $this->asJson($roleService->sort($this->request));
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 删除角色
     * @param RoleService $roleService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(RoleService $roleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 检查编辑者的角色权限是否有权编辑该角色
            $has_edit_auth = $roleService->checkRoleEditorAuth($this->request->post('id'), $this->UserInfo['role_id']);
            if (!$has_edit_auth) {
                return $this->renderJson('您的权限级无法删除该角色，请联系上级删除', 400);
            }
            return $this->asJson($roleService->delete($this->request));
        }
        return $this->renderJson('error', 500);
    }
}
