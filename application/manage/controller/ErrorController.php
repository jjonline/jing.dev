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
use think\Response;

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
        }else {
            $msg = '请确认您访问的是有效的地址';
        }

        // 依据请求类型返回
        if($this->request->isAjax())
        {
            // ajax响应 - http code 200
            return Response::create(['error_code' => 500,'error_msg' => $msg], 'json');
        }else {
            // html输出响应
            $this->view->engine->layout(false);// 关闭全局设定的模板布局功能
            $response = app('response');
            $response->code(404);// http code 404
            $this->assign('title','控制器不存在');
            $this->assign('msg',$msg);
            return $this->fetch('../application/common/view/error.html');
        }
    }

}
