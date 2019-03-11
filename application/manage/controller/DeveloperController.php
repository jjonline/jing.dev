<?php
/**
 * 组件系统开发样例
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-10 18:28
 * @file DeveloperController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\helper\UtilHelper;
use think\facade\Cache;

class DeveloperController extends BaseController
{
    /**
     * 组件系统开发样例
     * @return mixed
     */
    public function sampleAction()
    {
        $common = [
            'title'            => '开发样例 - ' . config('local.site_name'),
            'content_title'    => '开发样例',
            'content_subtitle' => '组件系统开发样例，演示一些常用的组件功能使用说明',
            'breadcrumb'       => [
                ['label' => '开发样例', 'url' => url('developer/list')],
                ['label' => '开发样例', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 辅助工具
     * @return mixed
     */
    public function toolsAction()
    {
        $common = [
            'title'            => '辅助工具 - ' . config('local.site_name'),
            'content_title'    => '辅助工具',
            'content_subtitle' => '辅助工具，用于一键管理系统的一些辅助功能',
            'breadcrumb'       => [
                ['label' => '辅助工具', 'url' => url('developer/tools')],
                ['label' => '辅助工具', 'url' => ''],
            ],
            'load_layout_css'  => true,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 清理runtime运行时 文件
     * @return array|\think\Response
     */
    public function runtimeAction()
    {
        if ($this->request->isAjax()) {
            $runtime_path = realpath('../runtime/temp/');
            return UtilHelper::rmRuntimeFile($runtime_path);
        }
        return $this->renderJson('error', 500);
    }

    /**
     * 清理整站缓存ajax请求
     * @return \think\Response
     */
    public function cacheAction()
    {
        if ($this->request->isAjax()) {
            Cache::clear();
            return $this->renderJson('整站缓存清理成功', 0);
        }
        return $this->renderJson('error', 500);
    }
}
