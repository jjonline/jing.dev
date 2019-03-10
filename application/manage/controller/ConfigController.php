<?php
/**
 * 站点配置
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-12-31 21:02
 * @file ConfigController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\SiteConfigService;

class ConfigController extends BaseController
{
    /**
     * 设置各配置项值界面
     * @param SiteConfigService $siteConfigService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(SiteConfigService $siteConfigService)
    {
        $common = [
            'title'            => '站点配置管理 - ' . config('local.site_name'),
            'content_title'    => '站点配置管理',
            'content_subtitle' => '站点配置管理和修改',
            'breadcrumb'       => [
                ['label' => '站点配置管理', 'url' => url('config/list')],
                ['label' => '站点配置', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 仅能分配当前账号所下辖的部门
        $lists = $siteConfigService->getSiteConfigList();
        $this->assign('lists', $lists);

        return $this->fetch();
    }

    /**
     * 按分组保存配置值
     * @param SiteConfigService $siteConfigService
     * @return array
     */
    public function saveAction(SiteConfigService $siteConfigService)
    {
        if ($this->request->isAjax()) {
            return $siteConfigService->save($this->request);
        }
        return ['error_code' => 0, 'error_msg' => '保存成功'];
    }
}
