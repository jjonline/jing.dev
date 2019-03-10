<?php
/**
 * 组件系统开发样例
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-10 18:28
 * @file DeveloperController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;

class DeveloperController extends BaseController
{
    /**
     * 组件系统开发样例
     * @return mixed
     */
    public function sampleAction()
    {
        $common = [
            'title'            => '开发样例 - ' . config('local.site_name'),
            'content_title'    => '开发样例',
            'content_subtitle' => '组件系统开发样例，演示一些常用的组件功能使用说明',
            'breadcrumb'       => [
                ['label' => '开发样例', 'url' => url('developer/list')],
                ['label' => '开发样例', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }
}
