<?php
/**
 * 组织部门控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 19:58:00
 * @file OrganizationController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\DepartmentService;
use app\manage\service\OrganizationService;

class OrganizationController extends BaseController
{
    /**
     * 组织部门管理
     * @param DepartmentService $organizationSearch
     * @return mixed
     */
    public function listAction(DepartmentService $departmentService)
    {
        $common = [
            'title'            => '组织部门管理 - ' . config('local.site_name'),
            'content_title'    => '组织部门管理',
            'content_subtitle' => '组织部门列表和管理',
            'breadcrumb'       => [
                ['label' => '组织部门管理', 'url' => url('organization/list')],
                ['label' => '组织部门列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        list($dept_list, $parent_dept) = $departmentService->orgList($this->UserInfo);
        $this->assign('dept_list', $dept_list);
        $this->assign('parent_dept', $parent_dept);

        return $this->fetch();
    }

    /**
     * 新增组织部门
     * @param OrganizationService $organizationService
     * @return mixed
     */
    public function createAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $departmentService->orgSave($this->request->post('Dept/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑组织部门
     * @param OrganizationService $organizationService
     * @return mixed
     */
    public function editAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $departmentService->orgSave($this->request->post('Dept/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 组织部门排序
     * @param OrganizationService $organizationService
     * @return mixed
     */
    public function sortAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $departmentService->orgSort($this->request, $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 组织部门删除
     * @param OrganizationService $organizationService
     * @return mixed
     */
    public function deleteAction(DepartmentService $departmentService)
    {
        if ($this->request->isAjax()) {
            return $departmentService->orgDelete($this->request, $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }
}
