<?php
/**
 * 文章分类控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-15 21:32:00
 * @file ArticleCatController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\ArticleCatService;

class ArticleCatController extends BaseController
{
    /**
     * 文章分类管理
     * @return mixed
     */
    public function listAction()
    {
        $common = [
            'title'            => '文章分类管理 - ' . config('local.site_name'),
            'content_title'    => '文章分类管理',
            'content_subtitle' => '文章分类列表和管理',
            'breadcrumb'       => [
                ['label' => '文章分类管理', 'url' => url('articleCat/list')],
                ['label' => '文章分类列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增文章分类
     * @param ArticleCatService $articleCatService
     * @return mixed
     */
    public function createAction(ArticleCatService $articleCatService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleCatService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑文章分类
     * @param ArticleCatService $articleCatService
     * @return mixed
     */
    public function editAction(ArticleCatService $articleCatService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleCatService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 文章分类排序
     * @param ArticleCatService $articleCatService
     * @return mixed
     */
    public function sortAction(ArticleCatService $articleCatService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleCatService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 文章分类删除
     * @param ArticleCatService $articleCatService
     * @return mixed
     */
    public function deleteAction(ArticleCatService $articleCatService)
    {
        if ($this->request->isAjax()) {
            return $articleCatService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
