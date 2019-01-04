<?php
/**
 * Created by PhpStorm.
 * User: Zwb
 * Date: 2018/4/28
 * Time: 10:52
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\AsyncTaskService;
use app\manage\model\search\AsyncTaskSearch;
use think\Request;

class AsyncTaskController extends BaseController
{
    /**
     * 任务状态列表
     * @param AsyncTaskSearch $asyncTaskSearch
     * @return mixed
     */
    public function listAction(AsyncTaskSearch $asyncTaskSearch)
    {
        if($this->request->isAjax())
        {
            // 将当前登录用户信息传递过去
            return $this->asJson($asyncTaskSearch->list($this->UserInfo));
        }
        $common = [
            'title'            => '异步任务状态 - ' . config('local.site_name'),
            'content_title'    => '异步任务状态',
            'content_subtitle' => '查看异步任务完成状态',
            'breadcrumb'       => [
                ['label' => '个人中心', 'url' => url('async_task/list')],
                ['label' => '异步任务状态', 'url' => ''],
            ],
            'load_layout_css'  => false,
            'load_layout_js'   => true,
        ];
        $this->assign($common);

        // 仅能分配当前账号所下辖的部门
        $dept_list  = $this->UserInfo['dept_auth']['dept_list_tree'];
        $this->assign('dept_list',$dept_list);
        return $this->fetch();
    }

    /**
     * 查看任务详情
     * @param Request $request
     * @param AsyncTaskService $asyncTaskService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detailAction(Request $request,AsyncTaskService $asyncTaskService)
    {
        if($request->isAjax())
        {
            return $this->asJson($asyncTaskService->getDetailById($request));
        }
    }
}
