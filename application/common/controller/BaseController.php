<?php
/**
 * 所有业务控制器的集成基类，直接集成该类即自动完成权限效验和拦截
 * ---
 * 1、所有开发新功能的业务均集成开类
 * 2、开发新分类时请先在后台添加菜单和角色权限
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:15
 * @file BaseController.php
 */

namespace app\common\controller;

use think\facade\Config;

class BaseController extends BaseAuthController
{
    /**
     * @var bool 是否载入自定义css
     */
    public $load_layout_css;
    /**
     * @var bool 是否载入自定义js
     */
    public $load_layout_js;
    /**
     * @var string 网页标题
     */
    public $title;
    /**
     * @var string 主体内容标题
     */
    public $content_title;
    /**
     * @var string 主体内容副标题，主体内容的描述语句
     */
    public $content_subtitle;
    /**
     * @var [] 主体内容右侧导航面包屑数组 结构:[['label' => '','url' => ''],['label' => '','url' => '']]
     */
    public $breadcrumb;

    /**
     * 初始化执行
     */
    public function initialize()
    {
        parent::initialize();
        // 默认关闭每个操作下都载入特定css的特性
        $this->load_layout_css = false;
        // 默认关闭每个操作下都载入特定js的特性
        $this->load_layout_js  = false;
    }

    /**
     * 统一空方法，空操作时不存在操作的提示
     * @param $name string 不存在的操作名
     * @return mixed
     */
    public function _empty($name)
    {
        // Debug模式提示更具体一些 非debug模式仅提示404
        if(Config::get('app_debug'))
        {
            $msg = '控制器【'.$this->request->controller().'】下的操作【'.$name.'】不存在';
            $this->assign('title','控制器下操作不存在');
            $this->assign('msg',$msg);
        }
        // 关闭全局设定的模板布局功能
        $this->view->engine->layout(false);
        $response = app('response');
        $response->code(404);
        return $this->fetch('../application/common/view/error.html');
    }
}
