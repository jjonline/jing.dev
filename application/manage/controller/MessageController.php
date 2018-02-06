<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 10:13
 * @file MessageController.php
 */

namespace app\manage\controller;


use app\common\model\UserLog;
use app\manage\model\search\MessageDataSearch;
use app\manage\service\MessageDataService;
use think\Request;

class MessageController extends BaseController
{

    /**
     * 话术数据列表
     * @param Request $request
     * @return mixed
     */
    public function ListAction(Request $request,MessageDataSearch $messageDataSearch)
    {
        if($request->isAjax())
        {
            return $messageDataSearch->search($request);
        }
        $this->title            = '话术数据 - '.config('local.site_name');
        $this->content_title    = '话术数据';
        $this->content_subtitle = '话术数据列表';
        $this->breadcrumb       = [
            ['label' => '话术数据','url' => url('message/list')],
            ['label' => '话术数据列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 删除话术数据
     * @param Request $request
     * @return []
     */
    public function DeleteAction(Request $request,MessageDataService $messageDataService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $messageDataService->deleteMessageData($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_MESSAGE,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 新增话术
     * @param Request $request
     * @throws
     */
    public function createAction(Request $request,MessageDataService $messageDataService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 保存角色
            return $messageDataService->saveData($request);
        }
        $this->title            = '新增话术 - '.config('local.site_name');
        $this->content_title    = '新增话术';
        $this->content_subtitle = '新增话术';
        $this->breadcrumb       = [
            ['label' => '话术管理','url' => url('message/list')],
            ['label' => '新增话术','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 编辑话术
     * @param Request $request
     * @throws
     */
    public function editAction(Request $request,MessageDataService $messageDataService)
    {
        if($request->isPost() && $request->isAjax())
        {
            // 保存角色
            return $messageDataService->saveData($request);
        }
        $this->title            = '编辑话术 - '.config('local.site_name');
        $this->content_title    = '编辑话术';
        $this->content_subtitle = '编辑话术';
        $this->breadcrumb       = [
            ['label' => '话术管理','url' => url('message/list')],
            ['label' => '编辑话术','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $message = $messageDataService->MessageData->find($request->get('id'));
        if(empty($message))
        {
            // 无权限或数据不存在
            $this->redirect(url('message/list'));
        }
        $this->assign('message',$message);

        return $this->fetch();
    }
}
