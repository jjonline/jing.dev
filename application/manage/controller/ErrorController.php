<?php
/**
 * 不存在控制器的错误提示
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-05 23:32
 * @file Error.php
 */

namespace app\manage\controller;

use think\Controller;
use think\facade\Config;

class ErrorController extends Controller
{
    /**
     * 统一空方法，控制器不存在的提示
     * @return mixed
     */
    public function _empty()
    {
        // Debug模式提示更具体一些 非debug模式仅提示404
        if(Config::get('app_debug'))
        {
            $msg = '控制器【'.$this->request->controller().'】不存在';
            $this->assign('title','控制器不存在');
            $this->assign('msg',$msg);
        }
        // 关闭全局设定的模板布局功能
        $this->view->engine->layout(false);
        $response = app('response');
        $response->code(404);
        return $this->fetch('error/error');
    }

}
