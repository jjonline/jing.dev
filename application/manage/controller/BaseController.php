<?php
/**
 * OA系统控制器基础基类，集成认证基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04 13:37
 * @file BaseController.php
 */

namespace app\manage\controller;

use think\facade\Config;
use think\Response;

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
     * 输出json字符串
     * @param array $data    json输出的数组
     * @param int $code      http状态码
     * @param array $header  可选的header头数组
     * @param array $options 可选的参数数组
     * @return mixed
     */
    public function asJson($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code, $header, $options);
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
        return $this->fetch('error/error');
    }
}
