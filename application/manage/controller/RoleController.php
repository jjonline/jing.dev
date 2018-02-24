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
        $this->title            = '角色管理 - '.config('local.site_name');
        $this->content_title    = '角色管理';
        $this->content_subtitle = '系统角色管理工具';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('role/list')],
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
     * 新增角色|仅超级管理员可添加
     * @param Request $request
     * @param RoleService $roleService
     * @return mixed
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
        $this->title            = '新增角色 - '.config('local.site_name');
        $this->content_title    = '新增角色';
        $this->content_subtitle = '新增管理角色并设置角色权限';
        $this->breadcrumb       = [
            ['label' => '角色管理','url'  => url('role/list')],
            ['label' => '新增角色','url'  => ''],
        ];
        $this->load_layout_css = true;
        $this->load_layout_js  = true;

        $MenuModel = new Menu();
        $menu_list = $MenuModel->getMenuList();
        dump($roleService->getRoleMenuList());
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
        $this->content_subtitle = '开发者模式下的角色编辑';
        $this->breadcrumb       = [
            ['label' => '角色管理','url'  => url('role/list')],
            ['label' => '编辑角色','url'  => ''],
        ];
        $this->load_layout_css = true;
        $this->load_layout_js  = true;

        // 角色数据
        $RoleModel = new Role();
        $Role      = $RoleModel->getRoleByName($request->param('name'));
        if(empty($Role))
        {
            $this->redirect(url('develop/role'));
        }
        $MenuModel = new Menu();
        $menu_list = $MenuModel->getMenuList();

        $RoleMenuModel = new RoleMenu();
        $role_menu     = $RoleMenuModel->getMenuNamesByRoleName($Role['name']);
        $this->assign('menu_list',$menu_list);
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
            return $this->asJson($roleService->delete($request));
        }
    }
}
