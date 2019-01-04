<?php
/**
 * 后台会员管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-29 14:26
 * @file MemberController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\manage\model\search\MemberSearch;
use app\manage\service\MemberService;

class MemberController extends BaseController
{
    public function listAction(MemberSearch $memberSearch)
    {
        if ($this->request->isAjax()) {
            // 将当前登录用户信息传递过去
            $result = $memberSearch->lists($this->UserInfo);
            return $this->asJson($result);
        }
        $this->title            = '会员管理 - '.config('local.site_name');
        $this->content_title    = '会员列表';
        $this->content_subtitle = '前台会员管理';
        $this->breadcrumb       = [
            ['label' => '会员管理','url' => url('member/list')],
            ['label' => '会员列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    public function createAction(MemberService $memberService)
    {
        if ($this->request->isAjax()) {
            $result = $memberService->insert($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
    }

    /**
     * 后台管理员编辑修改前台用户信息
     * @param MemberService $memberService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAction(MemberService $memberService)
    {
        if ($this->request->isAjax()) {
            $result = $memberService->update($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
    }

    /**
     * 启用|禁用前台用户
     * @param MemberService $memberService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function enableToggleAction(MemberService $memberService)
    {
        if ($this->request->isAjax()) {
            $result = $memberService->enableToggle($this->request, $this->UserInfo);
            return $this->asJson($result);
        }
    }
}
