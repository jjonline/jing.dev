<?php
/**
 * 默认首页
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04 13:37
 * @file BaseController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;

class IndexController extends BaseController
{

    /**
     * 默认首页
     */
    public function indexAction()
    {
        $common = [
            'title'            => '工作台 - ' . config('local.site_name'),
            'content_title'    => '工作台',
            'content_subtitle' => '工作台',
            'breadcrumb'       => [
                ['label' => '工作台', 'url' => url('index/index')],
                ['label' => '工作台', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => false,
        ];
        $this->assign($common);

        return $this->fetch();
    }

}
