<?php
/**
 * 鉴权
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-19 21:49:15
 * @file SiteController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\AuthService;
use think\Exception;
use think\facade\Session;
use think\Request;

class SiteController extends BaseController
{

    /**
     * 渲染登录页面
     */
    public function loginAction()
    {
        // 检查是否登录
        if($this->isUserLogin())
        {
            if($this->request->isAjax())
            {
                return $this->renderJson('已处于登录状态');
            }
            $this->redirect('index/index');
        }
        // post提交动作
        if($this->request->isPost() && $this->request->isAjax())
        {
            return $this->doLogin();
        }
        // 关闭全局设定的模板布局功能 渲染登录页面
        $this->view->engine->layout(false);
        $this->assign('load_layout_css',true);
        $this->assign('load_layout_js',true);
        return $this->fetch();
    }

    /**
     * ajax post提交登录操作
     */
    protected function doLogin()
    {
        try{
            // 令牌效验
            if(Session::get('__token__') != $this->request->post('__token__'))
            {
                return ['error_code' => -2,'error_msg' => '页面已过期，请刷新页面后再试'];
            }
            $this->UserService->checkUserLogin($this->request->post());
            return $this->renderJson('登录成功');
        }catch (Exception $e) {
            // 密码错误的时候令牌使用期限的问题
            Session::set('__token__',null);//清空令牌
            return $this->renderJson($e->getMessage(),400);
        }
    }

    /**
     * 退出登录
     */
    public function logoutAction()
    {
        if($this->isUserLogin())
        {
            $this->UserService->setUserLogout();
        }
        $this->redirect('site/login');
    }

    /**
     * 导航直达功能--输入菜单名称模糊检索后直接跳转到第一条有权限的菜单列表
     * ---
     * 未检索到或检索到的记录没有权限则回到检索前的页面
     * ---
     * @param Request $request
     * @param AuthService $authService
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menuSearchAction(Request $request,AuthService $authService)
    {
        $menu_name = $request->get('q');
        if(empty($menu_name))
        {
            $this->redirect($request->header('Referer'));
        }
        // 模糊搜索出所有菜单，注意检索出第一条有权限的记录并跳转
        $menu = $authService->Menu->db()
              ->where('name','like','%'.$menu_name.'%')
              ->order('sort','ASC')
              ->select();
        if(!empty($menu))
        {
            foreach ($menu as $item)
            {
                if($authService->userHasPermission($item['url']))
                {
                    $this->redirect(url($item['url']));
                }
            }
        }
        $this->redirect($request->header('Referer'));
    }

}
