<?php
/**
 * 网站会员控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file CustomerController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\CustomerService;
use app\manage\model\search\CustomerSearch;

class CustomerController extends BaseController
{
    /**
     * 网站会员管理
     * @param CustomerSearch $customerSearch
     * @return mixed
     */
    public function listAction(CustomerSearch $customerSearch)
    {
        if ($this->request->isAjax()) {
            $result = $customerSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '网站会员管理 - ' . config('local.site_name'),
            'content_title'    => '网站会员管理',
            'content_subtitle' => '网站会员列表和管理',
            'breadcrumb'       => [
                ['label' => '网站会员管理', 'url' => url('customer/list')],
                ['label' => '网站会员列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增网站会员
     * @param CustomerService $customerService
     * @return mixed
     */
    public function createAction(CustomerService $customerService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $customerService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑网站会员
     * @param CustomerService $customerService
     * @return mixed
     */
    public function editAction(CustomerService $customerService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $customerService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 网站会员排序
     * @param CustomerService $customerService
     * @return mixed
     */
    public function sortAction(CustomerService $customerService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $customerService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 网站会员删除
     * @param CustomerService $customerService
     * @return mixed
     */
    public function deleteAction(CustomerService $customerService)
    {
        if ($this->request->isAjax()) {
            return $customerService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
