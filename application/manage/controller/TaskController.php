<?php
/**
 * 任务管理控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-11 15:12
 * @file TaskController.php
 */

namespace app\manage\controller;

use app\common\model\ChatGroup;
use app\common\model\TaskType;
use app\common\model\UserLog;
use app\manage\model\search\TaskSearch;
use app\manage\service\DeviceService;
use app\manage\service\TaskService;
use think\Request;

class TaskController extends BaseController
{

    /**
     * 任务列表管理
     * @param Request $request
     * @throws
     */
    public function listAction(Request $request , TaskSearch $taskSearch)
    {
        if($request->isAjax())
        {
            return $taskSearch->search($request);
        }
        $this->title            = '任务管理 - '.config('local.site_name');
        $this->content_title    = '任务管理';
        $this->content_subtitle = '管理手机执行的任务';
        $this->breadcrumb       = [
            ['label' => '任务管理','url' => url('task/list')],
            ['label' => '任务列表','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        // 任务类型筛选
        $user_menus = $this->UserAuthService->getUserMenuList();
        $task_add_menus = [];
        foreach ($user_menus as $user_menu) {
            if($user_menu['parent_name'] == 'TaskManage_Create')
            {
                $task_add_menus[] = $user_menu['url'];
            }
        }
        $task_type = (new TaskType())->order(['tag' => 'DESC','sort'=>'ASC'])->select();
        $this->assign('task_type',$task_type);
        $this->assign('task_menus',$task_add_menus);

        return $this->fetch();
    }

    /**
     * ajax启动、停止任务
     * @param Request $request
     */
    public function HandleAction(Request $request,TaskService $taskService)
    {
        if($request->isAjax() && $request->isPost())
        {
            if($request->post('status') == 0)
            {
                // 停止任务
                $result = $taskService->stopTask($request->post('id'));
            }else {
                // 启动任务
                $result = $taskService->startTask($request->post('id'));
            }
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::HANDLE_TASK,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 新建任务导航页面
     * @param Request $request
     * @throws
     */
    public function createAction(Request $request)
    {
        $this->title            = '新建任务 - '.config('local.site_name');
        $this->content_title    = '新建任务';
        $this->content_subtitle = '新建手机执行的任务';
        $this->breadcrumb       = [
            ['label' => '任务管理','url' => url('task/list')],
            ['label' => '新建任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = false;

        $user_menus = $this->UserAuthService->getUserMenuList();
        $task_add_menus = [];
        foreach ($user_menus as $user_menu) {
            if($user_menu['parent_name'] == 'TaskManage_Create')
            {
                $task_add_menus[] = $user_menu['url'];
            }
        }
        $initTask = (new TaskType())->where(['tag' => 1])->order('sort','ASC')->select();
        $commonTask = (new TaskType())->where(['tag' => 0])->order('sort','ASC')->select();
        $this->assign('initTask',$initTask);
        $this->assign('commonTask',$commonTask);
        $this->assign('task_menus',$task_add_menus);
        return $this->fetch();
    }

    /**
     * 删除任务
     * @param Request $request
     * @param TaskService $taskService
     */
    public function DeleteAction(Request $request , TaskService $taskService )
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $taskService->deleteTask($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_TASK,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 新建微信加好友任务
     * @param Request $request
     * @param TaskService $taskService
     */
    public function JiaHaoYouAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveJiaHaoYou($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建微信加好友任务 - '.config('local.site_name');
        $this->content_title    = '新建微信加好友任务';
        $this->content_subtitle = '新建微信加好友任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建微信加好友任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600001'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        // 已分配未施用的手机号列表
        $unused_phone_data_count = $taskService->PhoneData->getTotalUnUsedByUserId($user_id,$dept1['dept_id']);
        $this->assign('unused_phone_data_count',$unused_phone_data_count);

        return $this->fetch();
    }

    /**
     * 微信发朋友圈
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function FaPengYouQuanAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveFaPengYouQuan($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建发朋友圈任务 - '.config('local.site_name');
        $this->content_title    = '新建发朋友圈任务';
        $this->content_subtitle = '新建发朋友圈任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建发朋友圈任务','url'  => ''],
        ];
        $this->load_layout_css  = true;
        $this->load_layout_js   = true;

        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600001'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        // 已分配未施用的手机号列表
        $unused_phone_data_count = $taskService->PhoneData->getTotalUnUsedByUserId($user_id,$dept1['dept_id']);
        $this->assign('unused_phone_data_count',$unused_phone_data_count);

        return $this->fetch();
    }

    /**
     * 看新闻任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function KanXinWenAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveKanXinWen($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建微信看新闻任务 - '.config('local.site_name');
        $this->content_title    = '新建微信看新闻任务';
        $this->content_subtitle = '新建微信看新闻任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建微信看新闻任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600003'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        return $this->fetch();
    }

    /**
     * 看订阅任务==看公众号任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function KanDingYueAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveKanDingYue($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建微信看公众号任务 - '.config('local.site_name');
        $this->content_title    = '新建微信看公众号任务';
        $this->content_subtitle = '新建微信看公众号任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建微信看公众号任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600004'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        return $this->fetch();
    }

    /**
     * 群发消息任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function QunFaXiaoXiAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveQunFaXiaoXi($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建群发消息任务 - '.config('local.site_name');
        $this->content_title    = '新建群发消息任务';
        $this->content_subtitle = '新建群发消息任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建群发消息任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        // 设备分组
        $device_typ = $deviceService->DeviceGroup->getUserDeviceGroupList();
        $this->assign('device_type',$device_typ);
        // 设备列表
        $devices = $deviceService->getUserDeviceGroupData();
        $this->assign('devices',$devices);
        // 互撩内容分组
        $chat_group = (new ChatGroup())->getAuthChatGroupList();
        $this->assign('chat_group',$chat_group);

        return $this->fetch();
    }

    /**
     * 互撩任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function HuLiaoTaskAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveHuLiaoTask($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建微信互聊任务 - '.config('local.site_name');
        $this->content_title    = '新建微信互聊任务';
        $this->content_subtitle = '新建微信互聊任务';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建微信互聊任务','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600006'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        return $this->fetch();
    }

    /**
     * 初始化任务--上报好友列表
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function UploadFriendsAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveUploadFriends($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建微信好友上报任务 - '.config('local.site_name');
        $this->content_title    = '新建微信好友上报';
        $this->content_subtitle = '新建微信好友上报';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '微信好友上报','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        $this->assign('TaskType',$taskService->TaskType->getTaskTypeByCode('600006'));

        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);

        return $this->fetch();
    }

    /**
     * 互撩对象下发任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function HuLiaoUsersAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveHuLiaoUsers($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建互聊对象下发任务 - '.config('local.site_name');
        $this->content_title    = '新建互聊对象下发';
        $this->content_subtitle = '新建互聊对象下发';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建互聊对象下发','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        // 设备分组
        $device_typ = $deviceService->DeviceGroup->getUserDeviceGroupList();
        $this->assign('device_type',$device_typ);
        // 设备列表
        $devices = $deviceService->getUserDeviceGroupData();
        $this->assign('devices',$devices);

        return $this->fetch();
    }

    /**
     * 互撩内容下发任务
     * @param Request $request
     * @param TaskService $taskService
     * @param DeviceService $deviceService
     * @throws
     */
    public function HuLiaoNeiRongAction(Request $request , TaskService $taskService , DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            $result = $taskService->saveHuLiaoNeiRong($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::ADD_NEW_TASK,$request->post());
            }
            return $this->asJson($result);
        }
        $this->title            = '新建互聊内容下发任务 - '.config('local.site_name');
        $this->content_title    = '新建互聊内容下发';
        $this->content_subtitle = '新建互聊内容下发';
        $this->breadcrumb       = [
            ['label' => '新建任务','url' => url('task/create')],
            ['label' => '新建互聊内容下发','url'  => ''],
        ];
        $this->load_layout_css  = false;
        $this->load_layout_js   = true;

        // 设备分组
        $device_typ = $deviceService->DeviceGroup->getUserDeviceGroupList();
        $this->assign('device_type',$device_typ);
        // 设备列表
        $device_tree = $deviceService->getUserDeviceTree($request->get('device_id'));
        $this->assign('device_tree',$device_tree);
        // 互撩内容分组
        $chat_group = (new ChatGroup())->getAuthChatGroupList();
        $this->assign('chat_group',$chat_group);

        return $this->fetch();
    }

}