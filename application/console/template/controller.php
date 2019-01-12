<?php
/**
 * __LIST_NAME__控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date __CREATE_TIME__
 * @file __CONTROLLER__Controller.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\__CONTROLLER__Service;
use app\manage\model\search\__CONTROLLER__Search;

class __CONTROLLER__Controller extends BaseController
{
    /**
     * __LIST_NAME__管理
     * @param __CONTROLLER__Search $__CONTROLLER_LOWER__Search
     * @return mixed
     */
    public function listAction(__CONTROLLER__Search $__CONTROLLER_LOWER__Search)
    {
        if ($this->request->isAjax()) {
            $result = $__CONTROLLER_LOWER__Search->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '__LIST_NAME__管理 - ' . config('local.site_name'),
            'content_title'    => '__LIST_NAME__管理',
            'content_subtitle' => '__LIST_NAME__列表和管理',
            'breadcrumb'       => [
                ['label' => '__LIST_NAME__管理', 'url' => url('__CONTROLLER_LOWER__Search/list')],
                ['label' => '__LIST_NAME__列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增__LIST_NAME__
     * @param __CONTROLLER__Service $__CONTROLLER_LOWER__Service
     * @return mixed
     */
    public function createAction(__CONTROLLER__Service $__CONTROLLER_LOWER__Service)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $__CONTROLLER_LOWER__Service->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑__LIST_NAME__
     * @param __CONTROLLER__Service $__CONTROLLER_LOWER__Service
     * @return mixed
     */
    public function editAction(__CONTROLLER__Service $__CONTROLLER_LOWER__Service)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $__CONTROLLER_LOWER__Service->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * __LIST_NAME__排序
     * @param __CONTROLLER__Service $__CONTROLLER_LOWER__Service
     * @return mixed
     */
    public function sortAction(__CONTROLLER__Service $__CONTROLLER_LOWER__Service)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $__CONTROLLER_LOWER__Service->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * __LIST_NAME__删除
     * @param __CONTROLLER__Service $__CONTROLLER_LOWER__Service
     * @return mixed
     */
    public function deleteAction(__CONTROLLER__Service $__CONTROLLER_LOWER__Service)
    {
        if ($this->request->isAjax()) {
            return $__CONTROLLER_LOWER__Service->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
