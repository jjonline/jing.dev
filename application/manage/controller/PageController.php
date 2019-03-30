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
     * 网站单页setting列表管理
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
     * 单页面设置数据提交
     * @param PageService $pageService
     * @return array|mixed
     */
    public function settingAction(PageService $pageService)
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            return $pageService->setting($this->request);
        }
        $common = [
            'title'            => '设置单页面 - ' . config('local.site_name'),
            'content_title'    => '设置单页面',
            'content_subtitle' => '网站单页参数设置',
            'breadcrumb'       => [
                ['label' => '设置单页面', 'url' => url('page/list')],
                ['label' => '网站单页参数设置', 'url' => 'page/save'],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 网站单页面config配置列表管理
     * @param PageSearch $pageSearch
     * @return mixed
     */
    public function configAction(PageSearch $pageSearch)
    {
        if ($this->request->isAjax()) {
            $result = $pageSearch->config($this->UserInfo);
            return $this->asJson($result);
        }
        $common = [
            'title'            => '单页配置管理 - ' . config('local.site_name'),
            'content_title'    => '单页配置管理',
            'content_subtitle' => '配置各个单页面的设置特性',
            'breadcrumb'       => [
                ['label' => '单页配置管理', 'url' => url('page/config')],
                ['label' => '单页配置管理', 'url' => ''],
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
        $common = [
            'title'            => '新增单页 - ' . config('local.site_name'),
            'content_title'    => '新增单页',
            'content_subtitle' => '新增单页并设定单页可设置选项',
            'breadcrumb'       => [
                ['label' => '新增单页', 'url' => url('page/create')],
                ['label' => '新增单页', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
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
        $common = [
            'title'            => '编辑单页 - ' . config('local.site_name'),
            'content_title'    => '编辑单页',
            'content_subtitle' => '编辑单页的各项配置属性',
            'breadcrumb'       => [
                ['label' => '编辑单页', 'url' => url('page/config')],
                ['label' => '编辑单页', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
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
