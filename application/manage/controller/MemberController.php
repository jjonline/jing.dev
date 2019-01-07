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
        $common = [
            'title'            => '会员管理 - ' . config('local.site_name'),
            'content_title'    => '会员管理',
            'content_subtitle' => '前台会员管理',
            'breadcrumb'       => [
                ['label' => '会员管理', 'url' => url('member/list')],
                ['label' => '会员管理', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        return $this->fetch();
    }

    /**
     * 后台创建前台会员
     * @param MemberService $memberService
     * @return mixed
     */
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
        return $this->renderJson('error', 500);
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
        return $this->renderJson('error', 500);
    }
}
