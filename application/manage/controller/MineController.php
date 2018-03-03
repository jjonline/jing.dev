<?php
/**
 * 个人中心
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-02 17:02
 * @file MineController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;


class MineController extends BaseController
{

    public function profileAction()
    {
        $this->title            = '个人中心 - '.config('local.site_name');
        $this->content_title    = '个人中心';
        $this->content_subtitle = '个人中心-个人资料概要';
        $this->breadcrumb       = [
            ['label' => '个人中心','url' => url('mine/profile')],
            ['label' => '个人资料概要','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = false;

        return $this->fetch();
    }
}
