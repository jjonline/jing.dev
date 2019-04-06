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
use app\common\service\UserService;
use app\common\service\DepartmentService;
use think\Exception;
use think\exception\HttpResponseException;
use think\Response;
use think\facade\Session;

class BaseAuthController extends BasicController
{
    /**
     * @var []
     */
    protected $UserInfo;
    /**
     * @var UserService
     */
    protected $UserService;
    /**
     * @var AuthService
     */
    protected $AuthService;
    /**
     * @var DepartmentService
     */
    protected $DepartmentService;

    /**
     * 初始化认证、鉴权
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        try {
            //初始化用户服务、权限效验服务、操作日志服务
            $this->UserService       = Container::get('app\common\service\UserService');
            $this->AuthService       = Container::get('app\common\service\AuthService');
            $this->DepartmentService = Container::get('app\common\service\DepartmentService');

            /**
             * @var [] 不需要登录状态即可渲染的控制器和不需要验证权限的公共ajax控制器
             * ---
             * 所有模块下的site|common两个控制器不做菜单权限检查和登录效验 直接跳过
             * ---
             */
            $except_controller = ['site','common'];
            if (in_array(strtolower($this->request->controller()), $except_controller)) {
                return;
            }

            // 检查是否登录
            if (!$this->isUserLogin()) {
                throw new Exception('您尚未登录，请保留好编辑的内容后刷新页面', -1);
            }

            // 检查权限
            if (!$this->AuthService->userHasPermission()) {
                throw new Exception('抱歉，您没有操作该页面的权限！', 404);
            }

            // 初始化User属性
            $this->UserInfo              = Session::get('user_info');
            // 当前菜单权限的一些信息
            $this->UserInfo['menu_auth'] = $this->AuthService->getUserSingleMenuInfo();
            // 会员可操作的部门列表信息
            $this->UserInfo['dept_auth'] = $this->DepartmentService->getAuthDeptInfoByDeptId(
                $this->UserInfo['dept_id']
            );

            // 获取并输出管理菜单
            $this->assign('UserAuthMenu', $this->AuthService->getUserAuthMenu());
        } catch (\Throwable $e) {
            /**
             * 未登录ajax则ajax返回-1，其他则redirect跳转
             * ---
             * 1、依据是否ajax，ajax响应code为-1的相应
             * 2、非ajax则直接重定向到登录页面
             * ---
             */
            if ($e->getCode() == -1) {
                if ($this->request->isAjax()) {
                    $response = Response::create([
                        'error_code' => -1,
                        'error_msg'  => $e->getMessage()
                    ], 'json');

                    // 抛出HttpResponseException异常并输出，终止后续业务代码执行
                    throw new HttpResponseException($response);
                } else {
                    // 跳转隐式抛出HttpResponseException异常，终止后续业务代码执行
                    $this->redirect('site/login');
                }
            }

            /**
             * 没有权限等其他异常
             * ----
             * 1、关闭模板布局
             * 2、依据是否ajax请求做出不同的响应
             * ----
             */
            $this->view->engine->layout(false); // 关闭layout 防止死循环
            if ($this->request->isAjax()) {
                $response = Response::create([
                    'error_code' => -1,
                    'error_msg'  => '没有操作权限'
                ], 'json');
            } else {
                $response = app('response'); // 读取单例构造html响应内容
                $error    = $this->fetch('../application/common/view/error.html', [
                    'title' => '没有操作权限',
                    'msg'   => $e->getMessage()
                ]);
                $response->code(404);
                $response->data($error);
            }
            // 抛出HttpResponseException异常并输出，终止后续业务代码执行
            throw new HttpResponseException($response);
        }
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

    /**
     * @param mixed $auth_tag 检查权限的Url或该菜单对应为全局唯一的字符串即菜单的tag字符串，为null则检查当前Url
     * @return bool
     * @throws \think\Exception
     */
    protected function userHasPermission($auth_tag = null)
    {
        return $this->AuthService->userHasPermission($auth_tag);
    }

    /**
     * 获取用户指定Url的权限标记，即返回：['super','leader','staff','guest']中的一者
     * @param mixed $tag 待检查的菜单标签名称或菜单无前缀url
     * @return string 一下4个中的1个-super|leader|staff|guest
     */
    protected function getUserPermissionsTag($url = null)
    {
        return $this->AuthService->getUserPermissionsTag($url);
    }
}
