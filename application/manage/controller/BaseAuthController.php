<?php
/**
 * 控制器基础认证类基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04 13:37
 * @file BaseController.php
 */

namespace app\manage\controller;

use app\manage\service\UserAuthService;
use app\manage\service\UserLogService;
use think\Container;
use think\Controller;
use app\manage\service\UserService;
use think\exception\HttpResponseException;
use think\facade\Session;
use think\Response;


class BaseAuthController extends Controller
{
    /**
     * @var UserService
     */
    protected $UserService;
    /**
     * @var UserAuthService
     */
    protected $UserAuthService;
    /**
     * @var UserLogService
     */
    protected $UserLogService;
    /**
     * @var bool 是否超级管理员
     */
    protected $isSuper;
    /**
     * @var [] 登录用户的信息数组
     */
    protected $User;

    /**
     * 初始化认证、鉴权
     * @throws
     */
    public function initialize()
    {
        parent::initialize();

        /**
         * 不需要登录状态即可渲染的控制器和不需要验证权限的公共ajax控制器
         */
        $except_controller = ['site','common'];
        if(in_array(strtolower($this->request->controller()),$except_controller))
        {
            return true;
        }
        $this->UserService     = Container::get('app\manage\service\UserService');
        $this->UserAuthService = Container::get('app\manage\service\UserAuthService');
        $this->UserLogService  = Container::get('app\manage\service\UserLogService');
        // 检查是否登录
        if(!$this->isUserLogin())
        {
            $this->redirect('site/login');
        }
        // 检查是否有权限，没有权限通过抛出Http异常终止执行并输出错误信息
        if(!$this->UserAuthService->userHasPermission())
        {
            $request  = app('request');//读取单例
            $response = app('response');//读取单例
            $response->code(404);
            $this->view->engine->layout(false);//关闭layout 防止死循环
            if($request->isAjax())
            {
                $response = Response::create(['error_code' => -1,'error_msg' => '没有操作权限'], 'json');
            }else {
                $error = $this->fetch(
                    '../application/common/view/error.html',
                    [
                        'title' => '没有操作权限',
                        'msg'   => '抱歉，您没有操作该页面的权限！'
                    ]
                );
                $response->data($error);
            }
            //抛出异常并输出，终止后续业务代码执行
            throw new HttpResponseException($response);
        }
        // 初始化User属性
        $this->User    = Session::get('user_info');
        // 标记当前用户是否为超级管理员
        $this->isSuper = $this->isSupper();
        // 获取管理菜单
        $UserAuthMenu = $this->getUserAuthMenu();
        // dump($UserAuthMenu);
        // 初始化用户默认公司和部门
        $this->UserAuthService->initDefaultDept();
        // 输出管理菜单
        $this->assign('UserAuthMenu',$UserAuthMenu);
        // 输出公司列表和部门列表
        $this->assign('Dept1',$this->UserAuthService->UserDepartmentService->getUserDept1List($this->User['id']));
        $this->assign('Dept2',$this->UserAuthService->UserDepartmentService->getUserDept2List($this->User['id']));
    }

    /**
     * 检查登录用户是否超级管理员
     */
    protected function isSupper()
    {
        return $this->UserService->isSupper();
    }

    /**
     * 检查用户是否登录
     * @return bool
     */
    protected function isUserLogin()
    {
        if(empty($this->UserService))
        {
            $this->UserService = Container::get('app\manage\service\UserService');
        }
        return $this->UserService->isUserLogin();
    }

    /**
     * 获取用户授权菜单列表
     * @return []
     */
    protected function getUserAuthMenu()
    {
        if(empty($this->UserAuthService))
        {
            $this->UserAuthService = Container::get('app\manage\service\UserAuthService');
        }
        return $this->UserAuthService->getUserAuthMenu();
    }

    /**
     * 检查当前用户指定的url是否有访问权限，用于控制菜单按钮级别的显示与否
     * @throws
     * @param null $url
     */
    protected function userHasPermission($url = null)
    {
        return $this->UserAuthService->userHasPermission($url);
    }
}
