<?php
/**
 * 网站单页控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file PageController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\PageService;
use app\manage\model\search\PageSearch;

class PageController extends BaseController
{
    /**
     * 网站单页管理
     * @param PageSearch $pageSearch
     * @return mixed
     */
    public function listAction(PageSearch $pageSearch)
    {
        if ($this->request->isAjax()) {
            $result = $pageSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '网站单页管理 - ' . config('local.site_name'),
            'content_title'    => '网站单页管理',
            'content_subtitle' => '网站单页列表和管理',
            'breadcrumb'       => [
                ['label' => '网站单页管理', 'url' => url('page/list')],
                ['label' => '网站单页列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增网站单页
     * @param PageService $pageService
     * @return mixed
     */
    public function createAction(PageService $pageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $pageService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑网站单页
     * @param PageService $pageService
     * @return mixed
     */
    public function editAction(PageService $pageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $pageService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 网站单页排序
     * @param PageService $pageService
     * @return mixed
     */
    public function sortAction(PageService $pageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $pageService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 网站单页删除
     * @param PageService $pageService
     * @return mixed
     */
    public function deleteAction(PageService $pageService)
    {
        if ($this->request->isAjax()) {
            return $pageService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
