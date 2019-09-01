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
            'content_subtitle' => '整站所有部门和列表管理（特权操作）',
            'breadcrumb'       => [
                ['label' => '系统管理', 'url' => url('department/list')],
                ['label' => '部门管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        $dept_list = $departmentService->superList($this->UserInfo);

        $this->assign('dept_list', $dept_list);

        return $this->fetch();
    }

    /**
     * 创建部门
     * @param DepartmentService $departmentService
     * @return array|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $departmentService->superSave($this->request->post('Dept/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑部门
     * @param DepartmentService $departmentService
     * @return array|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $departmentService->superSave($this->request->post('Dept/a'), $this->UserInfo);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 跳转部门排序字段
     * @param DepartmentService $departmentService
     * @return mixed
     */
    public function sortAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($departmentService->superSort($this->request, $this->UserInfo));
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 删除部门
     * @param DepartmentService $departmentService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(DepartmentService $departmentService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            // 编辑menu菜单后端检测和操作
            return $this->asJson($departmentService->superDelete($this->request, $this->UserInfo));
        }
        return $this->renderJson('error', 500);
    }
}
