<?php
/**
 * Dev模式的会员管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 10:08
 * @file UserController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\UserService;
use app\manage\model\search\UserSearch;
use think\Request;

class UserController extends BaseController
{
    /**
     * 用户列表
     * @param Request $request
     * @param UserSearch $userSearch
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listAction(Request $request,UserSearch $userSearch)
    {
        if($request->isAjax())
        {
            // 将当前登录用户信息传递过去
            return $this->asJson($userSearch->list($this->UserInfo));
        }
        $this->title            = '用户管理 - '.config('local.site_name');
        $this->content_title    = '用户列表';
        $this->content_subtitle = '后台用户列表管理';
        $this->breadcrumb       = [
            ['label' => '用户管理','url' => url('user/list')],
            ['label' => '用户列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $this->assign('dept_list',$this->UserInfo['dept_auth']['dept_list_tree']);

        return $this->fetch();
    }

    public function createAction(Request $request,UserService $userService)
    {

    }

    public function editAction(Request $request,UserService $userService)
    {

    }

    public function enableToggleAction(Request $request,UserService $userService)
    {
        if($request->isPost() && $request->isAjax())
        {
            return $this->asJson($userService->enableToggle($request));
        }
    }
}
