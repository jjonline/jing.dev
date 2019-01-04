<?php
/**
 * 部门管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-22 22:07:29
 * @file DepartmentController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\DepartmentService;
use think\Request;

class DepartmentController extends BaseController
{
    /**
     * 部门管理-部门列表
     * @param DepartmentService $departmentService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(DepartmentService $departmentService)
    {
        $common = [
            'title'            => '部门管理 - ' . config('local.site_name'),
            'content_title'    => '部门管理',
            'content_subtitle' => '部门管理工具，设置和管理系统部门数据',
            'breadcrumb'       => [
                ['label' => '系统管理', 'url' => url('department/list')],
                ['label' => '部门管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $dept_list = $departmentService->getDeptTreeList();

        $this->assign('dept_list',$dept_list);

        return $this->fetch();
    }

    /**
     * 创建部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $departmentService->save($request);
        }
        $common = [
            'title'            => '新增部门 - ' . config('local.site_name'),
            'content_title'    => '新增部门',
            'content_subtitle' => '新增部门--即新增公司、公司下部门（系统的部门是一个抽象的概念）',
            'breadcrumb'       => [
                ['label' => '系统管理', 'url' => url('department/list')],
                ['label' => '新增部门', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $dept_list = $departmentService->getDeptTreeList();
        $this->assign('dept_list',$dept_list);

        return $this->fetch();
    }

    /**
     * 编辑部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $departmentService->save($request);
        }
        $common = [
            'title'            => '编辑部门 - ' . config('local.site_name'),
            'content_title'    => '编辑部门',
            'content_subtitle' => '编辑公司、公司下信息',
            'breadcrumb'       => [
                ['label' => '系统管理', 'url' => url('department/list')],
                ['label' => '编辑部门', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $Dept = $departmentService->Department->getDeptInfoById($request->param('id'));
        if(empty($Dept))
        {
            $this->redirect(url('department/list'));
        }
        $dept_list = $departmentService->getDeptTreeList();
        $this->assign('dept_list',$dept_list);
        $this->assign('dept',$Dept);
        return $this->fetch();
    }

    /**
     * 跳转部门排序字段
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($departmentService->sort($request));
        }
    }

    /**
     * 删除部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($departmentService->delete($request));
        }
    }
}
