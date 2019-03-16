<?php
/**
 * 关键词控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-16 17:57:00
 * @file TagController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\TagService;
use app\manage\model\search\TagSearch;

class TagController extends BaseController
{
    /**
     * 关键词管理
     * @param TagSearch $tagSearch
     * @return mixed
     */
    public function listAction(TagSearch $tagSearch)
    {
        if ($this->request->isAjax()) {
            $result = $tagSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '关键词管理 - ' . config('local.site_name'),
            'content_title'    => '关键词管理',
            'content_subtitle' => '关键词列表和管理',
            'breadcrumb'       => [
                ['label' => '关键词管理', 'url' => url('tag/list')],
                ['label' => '关键词列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增关键词
     * @param TagService $tagService
     * @return mixed
     */
    public function createAction(TagService $tagService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $tagService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑关键词
     * @param TagService $tagService
     * @return mixed
     */
    public function editAction(TagService $tagService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $tagService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 关键词排序
     * @param TagService $tagService
     * @return mixed
     */
    public function sortAction(TagService $tagService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $tagService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 关键词删除
     * @param TagService $tagService
     * @return mixed
     */
    public function deleteAction(TagService $tagService)
    {
        if ($this->request->isAjax()) {
            return $tagService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
