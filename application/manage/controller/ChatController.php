<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 10:06
 * @file ChatController.php
 */

namespace app\manage\controller;


use app\manage\model\search\ChatDataSearch;
use app\manage\model\search\ChatGroupSearch;
use app\common\model\UserLog;
use app\manage\service\ChatDataService;
use app\manage\service\ChatGroupService;
use think\Request;
use think\Response;

class ChatController extends BaseController
{

    /**
     * 互撩数据列表
     * @param Request $request
     * @return mixed
     */
    public function ListAction(Request $request,ChatDataSearch $chatDataSearch)
    {
        if($request->isAjax())
        {
            return $chatDataSearch->search($request);
        }
        $this->title            = '互聊数据 - '.config('local.site_name');
        $this->content_title    = '互聊数据';
        $this->content_subtitle = '互聊数据列表';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '互聊数据列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 删除互撩
     * @param Request $request
     * @return []
     */
    public function deleteAction(Request $request,ChatDataService $chatDataService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $chatDataService->deleteChat($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_CHAT,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 创建互撩内容
     * @param Request $request
     * @param ChatDataService $chatDataService
     * @throws
     * @return mixed
     */
    public function CreateAction(Request $request , ChatDataService $chatDataService)
    {
        if($request->isAjax() && $request->isPost())
        {
            return $chatDataService->saveData($request);
        }
        $this->title            = '互聊数据 - '.config('local.site_name');
        $this->content_title    = '新增互聊数据';
        $this->content_subtitle = '新增互聊数据';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '新增互聊数据','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $chat_group = $chatDataService->ChatGroup->getAuthChatGroupList();
        $this->assign('chat_group',$chat_group);

        return $this->fetch();
    }

    /**
     * 编辑撩内容
     * @param Request $request
     * @param ChatDataService $chatDataService
     * @throws
     * @return mixed
     */
    public function EditAction(Request $request , ChatDataService $chatDataService)
    {
        if($request->isAjax() && $request->isPost())
        {
            return $chatDataService->saveData($request);
        }
        $this->title            = '互聊数据 - '.config('local.site_name');
        $this->content_title    = '编辑互聊数据';
        $this->content_subtitle = '编辑互聊数据';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '编辑互聊数据','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $chat = $chatDataService->ChatData->getAuthChatDataById($request->get('id'));
        if(empty($chat))
        {
            $this->redirect(url('chat/list'));
        }

        $chat_group = $chatDataService->ChatGroup->getAuthChatGroupList();
        $this->assign('chat_group',$chat_group);

        $this->assign('chat',$chat);

        // 强行将当前选择的业态切换成该分组
        cookie('default_dept2',$chat['dept_id2']);
        return $this->fetch();
    }

    /**
     * 互撩分组列表
     * @return mixed
     */
    public function groupAction(Request $request , ChatGroupSearch $chatGroupSearch)
    {
        if($request->isAjax())
        {
            return $chatGroupSearch->search($request);
        }
        $this->title            = '互聊数据 - '.config('local.site_name');
        $this->content_title    = '互聊分组';
        $this->content_subtitle = '所有互聊的分组列表和分组管理';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '互聊分组','url' => url('chat/group')],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 删除互撩分组
     * @param Request $request
     * @return []
     */
    public function GroupDeleteAction(Request $request,ChatGroupService $chatGroupService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $chatGroupService->deleteChatGroup($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_CHAT_GROUP,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 新建设备分组
     * @return mixed
     * @throws
     */
    public function groupCreateAction(Request $request,ChatGroupService $chatGroupService)
    {
        if($request->isAjax())
        {
            return $chatGroupService->saveData($request);
        }
        $this->title            = '新建互聊分组 - '.config('local.site_name');
        $this->content_title    = '新建互聊分组';
        $this->content_subtitle = '新建互聊分组';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '互聊分组','url' => url('chat/group')],
            ['label' => '新建互聊分组','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 编辑设备分组
     * @return mixed
     */
    public function groupEditAction(Request $request,ChatGroupService $chatGroupService)
    {
        if($request->isAjax())
        {
            return $chatGroupService->saveData($request);
        }
        $this->title            = '编辑互聊分组 - '.config('local.site_name');
        $this->content_title    = '编辑互聊分组';
        $this->content_subtitle = '编辑互聊分组';
        $this->breadcrumb       = [
            ['label' => '互聊数据','url' => url('chat/list')],
            ['label' => '互聊分组','url' => url('chat/group')],
            ['label' => '编辑互聊分组','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $group = $chatGroupService->ChatGroup->getAuthChatGroupById($request->get('id'));
        if(empty($group))
        {
            $this->redirect(url('chat/group'));
        }
        $this->assign('group',$group);
        // 强行将当前选择的业态切换成该分组
        cookie('default_dept2',$group['dept_id2']);
        return $this->fetch();
    }

    /**
     * 下载批量导入互撩数据的csv模板
     */
    public function DownTemplateAction()
    {
        ob_start();
        $response = new Response();
        $response->header([
            'Content-Type'        => 'application/csv',
            'Content-Disposition' => 'attachment;filename=互聊数据批量导入模板.csv',
            'Cache-Control'       => 'max-age=0'
        ]);
        $response->expires(strtotime('-30 days'));
        ob_clean();
        $csv_file = fopen('php://output', 'a');
        fwrite($csv_file,mb_convert_encoding('发出内容','gb2312').'----'.mb_convert_encoding('回复内容','gb2312').'----'.mb_convert_encoding('互聊分组（输入分组名称即可）','gb2312'));
        ob_flush();
        fclose($csv_file);
        flush();
        return $response;
    }

    /**
     * csv批量导入互撩数据
     * @param Request $request
     * @param ChatDataService $chatDataService
     */
    public function BatchImportAction(Request $request , ChatDataService $chatDataService)
    {
        $file = $request->file('csvFile');
        $file_info = $file->validate(['ext' =>'csv'])->move('./upload/safe/chat/',$file->hash('md5'));
        if($file_info)
        {
            $excel_file = './upload/safe/chat/'.$file_info->getSaveName();
            // 记录上传文件日志
            $this->UserLogService->insertUserLog($this->User['id'],UserLog::UPLOAD_CHAT_FILE,['file' => $excel_file]);
            $result = $chatDataService->importChatData($excel_file);
            // 批量导入成功，记录批量导入动作日志
            if($result['error_code'] == 0)
            {
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::IMPORT_CHAT_DATA,['file' => $excel_file]);
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '批量导入出错：文件解析失败！']);
    }
}
