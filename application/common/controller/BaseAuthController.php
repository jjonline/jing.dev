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
     * @throws
     */
    protected function initialize()
    {
        parent::initialize();
        //初始化用户服务、权限效验服务、操作日志服务
        $this->UserService       = Container::get('app\common\service\UserService');
        $this->AuthService       = Container::get('app\common\service\AuthService');
        $this->DepartmentService = Container::get('app\common\service\DepartmentService');
        /**
         * @var [] 不需要登录状态即可渲染的控制器和不需要验证权限的公共ajax控制器，所有模块下的site、common两个控制器不做菜单权限检查和登录效验
         */
        $except_controller = ['site','common'];
        if (in_array(strtolower($this->request->controller()), $except_controller)) {
            return true;
        }
        // 检查是否登录
        if (!$this->isUserLogin()) {
            // ajax请求返回json 非ajax跳转至登录页面
            if ($this->request->isAjax()) {
                $response = Response::create(['error_code' => -1,'error_msg' => '您尚未登录，请保留好编辑的内容后刷新页面~'], 'json');
                //抛出异常并输出，终止后续业务代码执行
                throw new HttpResponseException($response);
            } else {
                //跳转隐式抛出异常，终止后续业务代码执行
                $this->redirect('site/login');
            }
        }
        // 检查权限
        if (!$this->AuthService->userHasPermission()) {
            $response = app('response');//读取单例
            $response->code(404);
            $this->view->engine->layout(false);//关闭layout 防止死循环
            if ($this->request->isAjax()) {
                $response = Response::create(['error_code' => -1,'error_msg' => '没有操作权限'], 'json');
            } else {
                $error = $this->fetch('../application/common/view/error.html', [
                    'title' => '没有操作权限',
                    'msg'   => '抱歉，您没有操作该页面的权限！'
                ]);
                $response->data($error);
            }
            //抛出异常并输出，终止后续业务代码执行
            throw new HttpResponseException($response);
        }
        // 初始化User属性
        $this->UserInfo              = Session::get('user_info');
        // 当前菜单权限的一些信息
        $this->UserInfo['menu_auth'] = $this->AuthService->getUserSingleMenuInfo();
        // 会员可操作的部门列表信息
        $this->UserInfo['dept_auth'] = $this->DepartmentService->getAuthDeptInfoByDeptId(
            $this->UserInfo['dept_id']
        );
        // 获取管理菜单
        $UserAuthMenu                = $this->AuthService->getUserAuthMenu();
        //dump($UserAuthMenu);
        // 输出管理菜单
        $this->assign('UserAuthMenu', $UserAuthMenu);
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
     * @param null $url
     * @return mixed|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getUserPermissionsTag($url = null)
    {
        return $this->AuthService->getUserPermissionsTag($url);
    }

    /**
     * 获取指定Url中的额外数组数据，无则为空字符串
     * @param null $url
     * @return mixed|array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getMenuExtraParam($url = null)
    {
        return $this->AuthService->getMenuExtraParam($url);
    }
}
