<?php
/**
 * 鉴权
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-09 10:56:37
 * @file
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use think\Exception;

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
            $this->UserService->checkUserLogin($this->request->post());
            return $this->renderJson('登录成功');
        }catch (Exception $e) {
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

}
