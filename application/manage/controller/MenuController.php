<?php
/**
 * 系统菜单管理页面--Developer可用，具体业务场景隐藏该权限
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-19 21:17:29
 * @file MenuController.php
 */

namespace app\manage\controller;


use app\common\controller\BaseController;
use app\common\model\Menu;
use app\common\service\MenuService;
use think\Request;

class MenuController extends BaseController
{

    /**
     * 菜单管理
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction()
    {
        $this->title            = '菜单管理 - '.config('local.site_name');
        $this->content_title    = '菜单管理';
        $this->content_subtitle = '开发者模式下的菜单管理工具';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('develop/menu')],
            ['label' => '菜单管理','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $MenuModel = new Menu();
        $list      = $MenuModel->getFormatMenuList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 新增菜单
     * @param Request $request
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 新增menu菜单后端检测和操作
            return $menuService->save($request);
        }
        $this->title            = '新增菜单 - '.config('local.site_name');
        $this->content_title    = '新增菜单';
        $this->content_subtitle = '开发者模式下新增菜单';
        $this->breadcrumb       = [
            ['label' => 'Developer','url' => url('menu/list')],
            ['label' => '新增菜单','url'  => url('menu/create')],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $MenuModel = new Menu();
        $list      = $MenuModel->getMenuList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 修改菜单
     * @param Request $request
     * @param MenuService $menuService
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $menuService->save($request);
        }
        $this->title            = '编辑菜单 - '.config('local.site_name');
        $this->content_title    = '编辑菜单';
        $this->content_subtitle = '开发者模式下修改菜单';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('develop/menu')],
            ['label' => '菜单管理','url'  => url('develop/menu')],
            ['label' => '编辑菜单','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $MenuModel = new Menu();
        $menu      = $MenuModel->getMenuById($request->param('id'));
        $list      = $MenuModel->getMenuList();
        if(empty($menu))
        {
            $this->redirect(url('develop/menu'));
        }
        $this->assign('menu_edit',$menu);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 菜单排序
     * @param Request $request
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($menuService->sort($request));
        }
    }

    /**
     * 删除菜单
     * @param Request $request
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($menuService->delete($request));
        }
    }

}
