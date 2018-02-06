<?php
/**
 * 设备管理
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 16:02
 * @file DeviceController.php
 */

namespace app\manage\controller;


use app\common\model\TaskType;
use app\common\model\UserLog;
use app\manage\model\search\DeviceGroupSearch;
use app\manage\model\search\DeviceSearch;
use app\manage\service\DeviceService;
use think\Request;

class DeviceController extends BaseController
{

    /**
     * 设备列表
     * @return mixed
     * @throws
     */
    public function listAction(Request $request,DeviceSearch $deviceSearch)
    {
        if($request->isAjax())
        {
            return $deviceSearch->search($request);
        }
        $this->title            = '设备管理 - '.config('local.site_name');
        $this->content_title    = '设备管理';
        $this->content_subtitle = '所有设备信息列表并进行管理';
        $this->breadcrumb       = [
            ['label' => '设备管理','url' => url('device/list')],
            ['label' => '设备列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

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
     * 删除设备
     * @param Request $request
     */
    public function deleteAction(Request $request, DeviceService $deviceService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $deviceService->deleteDevice($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_DEVICE,$request->post());
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }

    /**
     * 编辑设备信息
     * @param Request $request
     * @param DeviceService $deviceService
     */
    public function editAction(Request $request , DeviceService $deviceService)
    {
        if($request->isAjax() && $request->isPost())
        {
            return $deviceService->saveData($request);
        }
        $this->title            = '编辑设备 - '.config('local.site_name');
        $this->content_title    = '编辑设备';
        $this->content_subtitle = '编辑设备自定义个性化信息';
        $this->breadcrumb       = [
            ['label' => '设备管理','url' => url('device/list')],
            ['label' => '编辑设备','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $device = $deviceService->Device->getAuthDeviceById($request->get('id'));
        $device_group = $deviceService->DeviceGroup->getUserDeviceGroupList();
        if(empty($device))
        {
            // 无权限或数据不存在
            $this->redirect(url('device/list'));
        }

        $this->assign('device',$device);
        $this->assign('device_group',$device_group);
        return $this->fetch();
    }

    /**
     * 设备分组列表
     * @return mixed
     */
    public function groupAction(Request $request , DeviceGroupSearch $deviceGroupSearch)
    {
        if($request->isAjax())
        {
            return $deviceGroupSearch->search($request);
        }
        $this->title            = '设备分组 - '.config('local.site_name');
        $this->content_title    = '设备分组';
        $this->content_subtitle = '所有设备的分组列表和分组管理';
        $this->breadcrumb       = [
            ['label' => '设备管理','url' => url('device/list')],
            ['label' => '设备分组','url' => url('device/group')],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 新建设备分组
     * @return mixed
     */
    public function groupCreateAction(Request $request ,  DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            return $deviceService->saveDeviceGroupData($request);
        }
        $this->title            = '新建设备分组 - '.config('local.site_name');
        $this->content_title    = '新建设备分组';
        $this->content_subtitle = '新建设备分组';
        $this->breadcrumb       = [
            ['label' => '设备管理','url' => url('device/list')],
            ['label' => '设备分组','url' => url('device/group')],
            ['label' => '新建设备分组','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 编辑设备分组
     * @return mixed
     */
    public function groupEditAction(Request $request ,  DeviceService $deviceService)
    {
        if($request->isAjax())
        {
            return $deviceService->saveDeviceGroupData($request);
        }
        $this->title            = '编辑设备分组 - '.config('local.site_name');
        $this->content_title    = '编辑设备分组';
        $this->content_subtitle = '编辑设备分组';
        $this->breadcrumb       = [
            ['label' => '设备管理','url' => url('device/list')],
            ['label' => '设备分组','url' => url('device/group')],
            ['label' => '编辑设备分组','url' => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $group = $deviceService->DeviceGroup->getDeviceGroupById($request->get('id'));
        if(empty($group))
        {
            $this->redirect(url('device/group'));
        }
        $this->assign('group',$group);
        // 强行将当前选择的业态切换成该分组
        cookie('default_dept2',$group['dept_id2']);
        return $this->fetch();
    }

    /**
     * 删除设备分组
     * @param Request $request
     * @param DeviceService $deviceService
     */
    public function GroupDeleteAction(Request $request ,  DeviceService $deviceService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $deviceService->deleteDeviceGroup($request);
            if($result['error_code'] === 0)
            {
                // 新增成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_DEVICE_GROUP,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }
}
