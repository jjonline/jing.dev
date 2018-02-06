<?php
/**
 * 默认首页
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04 13:37
 * @file BaseController.php
 */

namespace app\manage\controller;


class IndexController extends BaseController
{

    /**
     * 默认首页
     */
    public function indexAction()
    {
        $this->title            = '工作台 - '.config('local.site_name');
        $this->content_title    = '工作台';
        $this->content_subtitle = '工作流概要统计';
        $this->breadcrumb       = [
            ['label' => '工作台','url' => url('index/index')],
            ['label' => '概要','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = false;

        return $this->fetch();
    }

}
