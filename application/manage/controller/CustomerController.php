<?php
/**
 * 网站会员控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file CustomerController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\helper\MenuColumnsHelper;
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

        // 自定义字段
        list($html, $js) = MenuColumnsHelper::toFrontendStructure($this->UserInfo['menu_auth']['show_columns']);
        $this->assign('html', $html);
        $this->assign('js', $js);

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
            return $customerService->create($this->request);
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
     * 快速启用|禁用
     * @param CustomerService $customerService
     * @return array|\think\Response
     */
    public function enableAction(CustomerService $customerService)
    {
        if ($this->request->isAjax()) {
            return $customerService->enable($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 网站会员等级设置界面
     * @param CustomerService $customerService
     * @return mixed
     */
    public function configAction(CustomerService $customerService)
    {
        $common = [
            'title'            => '会员等级配置 - ' . config('local.site_name'),
            'content_title'    => '会员等级配置',
            'content_subtitle' => '会员等级参数设置',
            'breadcrumb'       => [
                ['label' => '会员管理', 'url' => url('customer/list')],
                ['label' => '会员等级参数设置', 'url' => url('customer/config')],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 已有等级配置
        $level = $customerService->getCustomerLevelConfig();
        $this->assign('level', $level);

        return $this->fetch();
    }

    /**
     * 保存前台会员等级配置
     * @param CustomerService $customerService
     * @return array|\think\Response
     */
    public function configSaveAction(CustomerService $customerService)
    {
        if ($this->request->isAjax()) {
            return $customerService->configSave($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
