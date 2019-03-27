<?php
/**
 * 轮播图控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 21:23:00
 * @file ImageController.php
 */
namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\service\ImageService;
use app\manage\model\search\ImageSearch;

class ImageController extends BaseController
{
    /**
     * 轮播图管理
     * @param ImageSearch $imageSearch
     * @return mixed
     */
    public function listAction(ImageSearch $imageSearch)
    {
        if ($this->request->isAjax()) {
            $result = $imageSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '轮播图管理 - ' . config('local.site_name'),
            'content_title'    => '轮播图管理',
            'content_subtitle' => '轮播图列表和管理',
            'breadcrumb'       => [
                ['label' => '轮播图管理', 'url' => url('image/list')],
                ['label' => '轮播图列表和管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 新增轮播图
     * @param ImageService $imageService
     * @return mixed
     */
    public function createAction(ImageService $imageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $imageService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 编辑轮播图
     * @param ImageService $imageService
     * @return mixed
     */
    public function editAction(ImageService $imageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $imageService->save($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 轮播图排序
     * @param ImageService $imageService
     * @return mixed
     */
    public function sortAction(ImageService $imageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $imageService->sort($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 轮播图删除
     * @param ImageService $imageService
     * @return mixed
     */
    public function deleteAction(ImageService $imageService)
    {
        if ($this->request->isAjax()) {
            return $imageService->delete($this->request);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 快速启用|禁用文章
     * @param ImageService $articleService
     * @return array|\think\Response
     */
    public function enableAction(ImageService $imageService)
    {
        if ($this->request->isAjax()) {
            return $imageService->enable($this->request);
        }
        return $this->renderJson('error', 500);
    }
}
