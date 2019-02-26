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

use think\exception\HttpResponseException;
use think\facade\Config;
use think\Response;

class BaseController extends BaseAuthController
{
    /**
     * 初始化执行
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 系统异常错误进行覆盖操作--没有跳转动作，只有页面提示
     * @overwrite
     * @param string $msg
     * @param null $url
     * @param string $data
     * @param int $wait
     * @param array $header
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $msg = $msg ? $msg : '系统错误';
        $this->assign('title', '系统错误');
        $this->assign('msg', $msg);
        // 关闭全局设定的模板布局功能
        $this->view->engine->layout(false);
        /**
         * @var Response
         */
        $response = app('response');
        $response->code(500);
        $error    = $this->fetch('../application/common/view/error.html');
        $response->data($error);
        //抛出异常并输出，终止后续业务代码执行
        throw new HttpResponseException($response);
    }

    /**
     * 统一空方法，空操作时不存在操作的提示
     * @param $name string 不存在的操作名
     * @return mixed
     */
    public function _empty($name)
    {
        // Debug模式提示更具体一些 非debug模式仅提示404
        if (Config::get('app_debug')) {
            $msg = '控制器【'.$this->request->controller().'】下的操作【'.$name.'】不存在';
        } else {
            $msg = '请确认您访问的是有效的地址';
        }

        // 依据请求类型返回
        if ($this->request->isAjax()) {
            // ajax响应 - http code 200
            return Response::create(['error_code' => 500,'error_msg' => $msg], 'json');
        } else {
            // html输出响应
            $this->view->engine->layout(false);// 关闭全局设定的模板布局功能
            $response = app('response');
            $response->code(404);// http code 404
            $this->assign('title', '控制器下操作不存在');
            $this->assign('msg', $msg);
            return $this->fetch('../application/common/view/error.html');
        }
    }
}
