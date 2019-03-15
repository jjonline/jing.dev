<?php
/**
 * 图文文章控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-13 22:53:00
 * @file ArticleController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\ArticleCatService;
use app\manage\service\ArticleService;
use app\manage\model\search\ArticleSearch;

class ArticleController extends BaseController
{
    /**
     * 图文文章管理
     * @param ArticleSearch $articleSearch
     * @param ArticleCatService $articleCatService
     * @return mixed
     */
    public function listAction(ArticleSearch $articleSearch, ArticleCatService $articleCatService)
    {
        if ($this->request->isAjax()) {
            $result = $articleSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '文章管理 - ' . config('local.site_name'),
            'content_title'    => '文章管理',
            'content_subtitle' => '文章列表和管理',
            'breadcrumb'       => [
                ['label' => '文章管理', 'url' => url('article/list')],
                ['label' => '文章列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 文章分类
        $article_cat = $articleCatService->getArticleCatTreeList();
        $this->assign('article_cat', $article_cat);

        // 所有部门
        $this->assign('dept', $this->DepartmentService->getDeptTreeList());

        // 所有用户筛选
        $this->assign('user', $this->UserService->getUserTreeList());

        return $this->fetch();
    }

    /**
     * 新增图文文章
     * @param ArticleService $articleService
     * @return mixed
     */
    public function createAction(ArticleService $articleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑图文文章
     * @param ArticleService $articleService
     * @return mixed
     */
    public function editAction(ArticleService $articleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 图文文章排序
     * @param ArticleService $articleService
     * @return mixed
     */
    public function sortAction(ArticleService $articleService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $articleService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 图文文章删除
     * @param ArticleService $articleService
     * @return mixed
     */
    public function deleteAction(ArticleService $articleService)
    {
        if ($this->request->isAjax()) {
            return $articleService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 快速启用|禁用文章
     * @param ArticleService $articleService
     * @return array|\think\Response
     */
    public function enableAction(ArticleService $articleService)
    {
        if ($this->request->isAjax()) {
            return $articleService->enable($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
