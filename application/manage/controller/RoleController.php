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
use app\common\model\Menu;
use app\common\model\RoleMenu;
use app\common\service\RoleService;
use think\Request;

class RoleController extends BaseController
{

    /**
     * 角色列表
     * @throws
     */
    public function listAction()
    {
        $this->title            = '人事设置 - '.config('local.site_name');
        $this->content_title    = '角色管理';
        $this->content_subtitle = '系统角色管理工具';
        $this->breadcrumb       = [
            ['label' => '人事设置','url' => url('role/list')],
            ['label' => '角色管理','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $RoleModel = new Role();
        $list      = $RoleModel->getRoleList();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 新增角色
     * @param Request $request
     * @param RoleService $roleService
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(Request $request , RoleService $roleService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 保存角色
            return $roleService->save($request);
        }
        $this->title            = '人事设置 - '.config('local.site_name');
        $this->content_title    = '新增角色';
        $this->content_subtitle = '人事设置-新增角色';
        $this->breadcrumb       = [
            ['label' => '角色管理','url'  => url('role/list')],
            ['label' => '新增角色','url'  => ''],
        ];
        $this->load_layout_css = true;
        $this->load_layout_js  = true;

        $menu_list = $roleService->getRoleMenuTreeDataByRoleId();
        $this->assign('menu_list',$menu_list);
        return $this->fetch();
    }

    /**
     * 修改角色
     * @throws
     */
    public function editAction(Request $request , RoleService $roleService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 保存角色
            return $roleService->save($request);
        }
        $this->title            = '角色管理 - '.config('local.site_name');
        $this->content_title    = '编辑角色';
        $this->content_subtitle = '人事设置-角色编辑';
        $this->breadcrumb       = [
            ['label' => '角色管理','url'  => url('role/list')],
            ['label' => '编辑角色','url'  => ''],
        ];
        $this->load_layout_css = true;
        $this->load_layout_js  = true;

        // 角色数据
        $RoleModel = new Role();
        $Role      = $RoleModel->getRoleInfoById($request->get('id'));
        if(empty($Role))
        {
            $this->redirect(url('develop/role'));
        }
        // 检查编辑者的角色权限是否有权编辑该角色
        $has_edit_auth = $roleService->checkRoleEditorAuth($Role['id'],$this->UserInfo['role_id']);
        if(!$has_edit_auth)
        {
            $this->error('您的权限级无法编辑该角色数据，请联系上级进行编辑');
        }
        // 当前账号具备的所有菜单权限
        $menu_list = $roleService->getRoleMenuTreeDataByRoleId();
        $this->assign('menu_list',$menu_list);

        // 待编辑的菜单权限列表
        $RoleMenuModel = new RoleMenu();
        $role_menu     = $RoleMenuModel->getRoleMenuListByRoleId($Role['id']);
        $this->assign('role_menu',$role_menu);

        $this->assign('role',$Role);
        return $this->fetch();
    }

    /**
     * 角色排序
     * @param Request $request
     * @param RoleService $roleService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(Request $request , RoleService $roleService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $this->asJson($roleService->sort($request));
        }
    }

    /**
     * 删除角色
     * @param Request $request
     * @param RoleService $roleService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(Request $request , RoleService $roleService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 检查编辑者的角色权限是否有权编辑该角色
            $has_edit_auth = $roleService->checkRoleEditorAuth($request->post('id'),$this->UserInfo['role_id']);
            if(!$has_edit_auth)
            {
                return $this->renderJson('您的权限级无法删除该角色，请联系上级删除',400);
            }
            return $this->asJson($roleService->delete($request));
        }
    }
}
