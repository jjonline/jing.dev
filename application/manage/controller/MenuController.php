<?php
/**
 * 系统管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04 13:37
 * @file BaseController.php
 */

namespace app\manage\controller;


use app\manage\model\Menu;
use app\manage\model\Role;
use app\manage\service\MenuService;
use think\Request;

class MenuController extends BaseController
{

    /**
     * 菜单管理
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
     */
    public function createAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 新增menu菜单后端检测和操作
            return $menuService->saveMenu($request,true);
        }
        $this->title            = '新增菜单 - '.config('local.site_name');
        $this->content_title    = '新增菜单';
        $this->content_subtitle = '开发者模式下新增菜单';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('develop/menu')],
            ['label' => '菜单管理','url'  => url('develop/menu')],
            ['label' => '新增菜单','url'  => ''],
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
     */
    public function editAction(Request $request , MenuService $menuService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $menuService->saveMenu($request,false);
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
        $menu      = $MenuModel->getMenuByName($request->param('name'));
        $list      = $MenuModel->getMenuList();
        if(empty($menu))
        {
            $this->redirect(url('develop/menu'));
        }
        $this->assign('menu_edit',$menu);
        $this->assign('list',$list);
        return $this->fetch();
    }

}
