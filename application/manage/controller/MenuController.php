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
        $common = [
            'title'            => '菜单管理 - ' . config('local.site_name'),
            'content_title'    => '菜单管理',
            'content_subtitle' => '开发者模式下的菜单管理工具',
            'breadcrumb'       => [
                ['label' => '系统管理', 'url' => url('develop/menu')],
                ['label' => '菜单管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $MenuModel = new Menu();
        $list      = $MenuModel->getFormatMenuList();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 新增菜单
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(MenuService $menuService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 新增menu菜单后端检测和操作
            return $menuService->save($this->request);
        }
        $common = [
            'title'            => '新增菜单 - ' . config('local.site_name'),
            'content_title'    => '菜单管理',
            'content_subtitle' => '开发者模式下的菜单管理工具',
            'breadcrumb'       => [
                ['label' => '新增菜单', 'url' => url('menu/menu')],
                ['label' => '菜单管理', 'url' => 'menu/create'],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $MenuModel = new Menu();
        $list      = $MenuModel->getMenuList();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 修改菜单
     * @param MenuService $menuService
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(MenuService $menuService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 编辑menu菜单后端检测和操作
            return $menuService->save($this->request);
        }
        $common = [
            'title'            => '编辑菜单 - ' . config('local.site_name'),
            'content_title'    => '菜单管理',
            'content_subtitle' => '开发者模式下的菜单管理工具',
            'breadcrumb'       => [
                ['label' => '新增菜单', 'url' => url('menu/menu')],
                ['label' => '编辑菜单', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $MenuModel = new Menu();
        $menu      = $MenuModel->getMenuById($this->request->param('id'));
        $list      = $MenuModel->getMenuList();
        if (empty($menu)) {
            $this->redirect(url('menu/list'));
        }
        $this->assign('menu_edit', $menu);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 菜单排序
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(MenuService $menuService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($menuService->sort($this->request));
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 按层级、排序重新排列菜单数据并生成seed数组
     * ---
     * 1、按层级 + 排序排列所有菜单
     * 2、重新生成递增ID
     * ---
     * @param MenuService $menuService
     * @return mixed|\think\Response
     */
    public function reorganizeAction(MenuService $menuService)
    {
        if ($this->request->isAjax()) {
            return $this->asJson($menuService->reorganize());
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 删除菜单
     * @param MenuService $menuService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(MenuService $menuService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($menuService->delete($this->request));
        }
        return $this->renderJson('error', 500);
    }
}
