<?php
/**
 * 文章
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-01-08 22:38
 * @file ArticleController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;

class ArticleController extends BaseController
{
    public function listAction()
    {
        if ($this->request->isAjax()) {
            // 将当前登录用户信息传递过去
            $result = $memberSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '图文管理 - ' . config('local.site_name'),
            'content_title'    => '图文管理',
            'content_subtitle' => '图文管理',
            'breadcrumb'       => [
                ['label' => '图文管理', 'url' => url('article/list')],
                ['label' => '图文管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }
}
