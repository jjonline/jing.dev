<?php
/**
 * 基础拦截验证控制器基类
 * ----
 * 1、实现控制器、操作的权限效验和拦截提示
 * 2、实现权限有关的公用方法
 * 3、所有开发控制器类不要直接继承该类，请继承BaseController
 * ----
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:12
 * @file BaseAuthController.php
 */

namespace app\common\controller;

use think\Container;
use app\common\service\AuthService;
use app\common\service\LogService;
use app\common\service\UserService;
use think\exception\HttpResponseException;
use think\Response;


class BaseAuthController extends BasicController
{
    /**
     * @var []
     */
    protected $User;
    /**
     * @var UserService
     */
    protected $UserService;
    /**
     * @var AuthService
     */
    protected $AuthService;
    /**
     * @var LogService
     */
    protected $LogService;

    /**
     * 初始化认证、鉴权
     * @throws
     */
    public function initialize()
    {
        parent::initialize();
        /**
         * @var [] 不需要登录状态即可渲染的控制器和不需要验证权限的公共ajax控制器
         */
        $except_controller = ['site','common'];
        if(in_array(strtolower($this->request->controller()),$except_controller))
        {
            return true;
        }
        //初始化用户服务、权限效验服务、操作日志服务
        $this->UserService = Container::get('app\common\service\UserService');
        $this->AuthService = Container::get('app\common\service\AuthService');
        $this->LogService  = Container::get('app\common\service\LogService');
        // 检查是否登录
        if(!$this->isUserLogin())
        {
            // ajax请求返回json 非ajax跳转至登录页面
            if($this->request->isAjax())
            {
                $response = Response::create(['error_code' => -1,'error_msg' => '您尚未登录，请保留好编辑的内容后刷新页面~'], 'json');
                //抛出异常并输出，终止后续业务代码执行
                throw new HttpResponseException($response);
            }else {
                //跳转隐式抛出异常，终止后续业务代码执行
                $this->redirect('site/login');
            }
        }
        // 检查权限

    }

    /**
     * 检查用户是否登录
     * --
     * 1、业务控制器中基本用不到，能执行业务控制器则一定过了登录效验
     * 2、仅登录页面控制用到
     * --
     * @return bool
     */
    protected function isUserLogin()
    {
        return $this->UserService->isUserLogin();
    }
}
