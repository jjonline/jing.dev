<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-06-16 15:28
 * @file SitConfigController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\SiteConfigService;

class SiteConfigController extends BaseController
{
    /**
     * 站内配置列表
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(SiteConfigService $siteConfigService)
    {
        $this->title            = '配置设置 - '.config('local.site_name');
        $this->content_title    = '配置设置列表';
        $this->content_subtitle = '配置设置列表';
        $this->breadcrumb       = [
            ['label' => '配置设置','url' => url('site_config/list')],
            ['label' => '配置设置','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $list = $siteConfigService->SiteConfig->getSiteConfigList();
        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 新增
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createAction(SiteConfigService $siteConfigService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $result =  $siteConfigService->save($this->request);
            return $this->asJson($result);
        }
    }

    /**
     * 修改
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(SiteConfigService $siteConfigService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $result = $siteConfigService->save($this->request);
            return $this->asJson($result);
        }
    }

    /**
     * 调整站点菜单排序
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sortAction(SiteConfigService $siteConfigService)
    {
        if($this->request->isPost() && $this->request->isAjax())
        {
            return $this->asJson($siteConfigService->sort($this->request));
        }
    }

    /**
     * 删除站点配置数据
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteAction(SiteConfigService $siteConfigService)
    {
        if($this->request->isPost() && $this->request->isAjax())
        {
            $result = $siteConfigService->delete($this->request->param('id'));
            return $this->asJson($result);
        }
    }
}
