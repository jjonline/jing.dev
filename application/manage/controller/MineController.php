<?php
/**
 * 个人中心
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-02 17:02
 * @file MineController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\UserOpenService;
use think\Request;


class MineController extends BaseController
{

    /**
     * 个人资料概要页
     * @return mixed
     * @throws \think\Exception
     */
    public function profileAction(UserOpenService $userOpenService)
    {
        $this->title            = '个人中心 - '.config('local.site_name');
        $this->content_title    = '个人中心';
        $this->content_subtitle = '个人中心-个人资料概要';
        $this->breadcrumb       = [
            ['label' => '个人中心','url' => url('mine/profile')],
            ['label' => '个人资料概要','url'  => ''],
        ];
        $this->load_layout_css = true;
        $this->load_layout_js  = true;

        // 是否有权编辑个人信息
        // $can_edit = $this->userHasPermission('manage/mine/edit'); // 无前后缀菜单url方法检查权限，全部使用小写
        $can_edit = $this->userHasPermission('Mine_Edit'); // 菜单tag方法检查权限，tag严格区分大小写
        $this->assign('can_edit',$can_edit);

        // 用户的开放平台绑定信息
        $user_open = $userOpenService->UserOpen->getUserOpenListInfoByUserId($this->UserInfo['id']);
        $this->assign('user_open',$user_open);

        return $this->fetch();
    }

    /**
     * ajax提交编辑的用户个人资料|密码+真实姓名+手机号+邮箱+性别
     * @param Request $request
     * @return array|\think\Response
     */
    public function editAction(Request $request)
    {
        if($request->isAjax() && $request->post('Profile.id') == $this->UserInfo['id'])
        {
            return $this->UserService->userModifyOwnUserInfo($request);
        }
        return $this->renderJson('异常错误',500);
    }
}
