<?php
/**
 * 部门管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-11 18:27
 * @file DepartmentController.php
 */

namespace app\manage\controller;

use app\manage\model\Department;
use app\manage\model\search\DepartmentSearch;
use app\manage\service\DepartmentService;
use think\Request;

class DepartmentController extends BaseController
{
    /**
     * 部门管理-部门列表
     */
    public function listAction(Request $request , DepartmentSearch $departmentSearch)
    {
        if($request->isAjax())
        {
            return $departmentSearch->search($request);
        }
        $this->title            = '部门管理 - '.config('local.site_name');
        $this->content_title    = '部门管理';
        $this->content_subtitle = '开发者模式下的部门管理工具';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('department/list')],
            ['label' => '部门管理','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 创建部门
     * @param Request $request
     * @param DepartmentService $departmentService
     * @throws
     */
    public function createAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $departmentService->saveData($request);
        }
        $this->title            = '新增部门 - '.config('local.site_name');
        $this->content_title    = '新增部门';
        $this->content_subtitle = '新增部门--即新增公司、公司下业态';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('department/list')],
            ['label' => '新增部门','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        if($request->has('dept_id'))
        {
            $dept_id  = $request->param('dept_id');
            $top_dept = $departmentService->getTopDeptById($dept_id);
            $top_dept && $this->assign('top_dept',$top_dept);
        }
        return $this->fetch();
    }

    /**
     * 编辑部门
     * @param Request $request
     * @param DepartmentService $departmentService
     */
    public function editAction(Request $request , DepartmentService $departmentService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $departmentService->saveData($request);
        }
        $this->title            = '编辑部门 - '.config('local.site_name');
        $this->content_title    = '编辑部门';
        $this->content_subtitle = '编辑公司、公司下信息';
        $this->breadcrumb       = [
            ['label' => '系统管理','url' => url('department/list')],
            ['label' => '编辑部门','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $Dept = (new Department())->getDeptById($request->param('id'));
        if(empty($Dept))
        {
            $this->redirect(url('department/list'));
        }
        if($Dept['level'] == 2)
        {
            $top_dept = $departmentService->getTopDeptById($Dept['parent_id']);
            $top_dept && $this->assign('top_dept',$top_dept);
        }
        $this->assign('dept',$Dept);
        return $this->fetch();
    }
}
