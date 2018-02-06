<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-14 01:06
 * @file DeviceService.php
 */

namespace app\manage\service;


use app\common\helpers\ArrayHelper;
use app\common\helpers\GenerateHelper;
use app\manage\model\Device;
use app\manage\model\DeviceGroup;
use think\Request;

class DeviceService
{
    /**
     * @var Device
     */
    public $Device;

    public $DeviceGroup;

    public function __construct(Device $device,DeviceGroup $deviceGroup)
    {
        $this->Device = $device;
        $this->DeviceGroup = $deviceGroup;
    }

    /**
     * 获取用户有效的设备分组和设备的Tree
     * @param string $checked_device_id 设定已勾选ID
     * @return array
     */
    public function getUserDeviceTree($checked_device_id = null)
    {
        $data = $this->Device->getAuthDeviceList();
        if(empty($data))
        {
            return [];
        }
        $data = ArrayHelper::group($data,'group_name');
        $nodes = [];
        foreach ($data as $key => $datum) {
            if(empty($key))
            {
                $key = '未分组设备';
            }
            $node         = [];
            $node['name'] = $key;
            $node['checked'] = false;
            $node['open'] = true;
            $child = [];
            foreach ($datum as $value)
            {
                // 传参默认勾选设备时勾选分组
                if($checked_device_id == $value['id'])
                {
                    $node['checked'] = true;
                }
                $node['idKey'] = $value['device_group_id'];//分组ID
                $_child = [];
                $_child['checked'] = $checked_device_id == $value['id'];
                $_child['name']  = $value['device_no'];
                $_child['idKey'] = $value['id'];//子节点的ID为设备ID
                $_child['open']  = true;//节点默认展开
                $_child['icon']  = '/static/images/phon_icon.png';//子节点icon
                $child[] = $_child;
            }
            $node['children'] = $child;

            $nodes[] = $node;
        }
        return $nodes;
    }

    /**
     * 获取用户的设备按分组切分的设备信息
     * @return array
     */
    public function getUserDeviceGroupData()
    {
        $data = $this->Device->getAuthDeviceList();
        if(empty($data))
        {
            return [];
        }
        foreach ($data as $key => $value)
        {
            if(empty($value['device_group_id']))
            {
                $data[$key]['group_name'] = '未分组';
                $data[$key]['device_group_id'] = 'none';
            }
        }
        return $data;
    }

    /**
     * 编辑修改设备信息的保存动作
     * ----
     * 1、可能会修改设备级别的任务随机间隔时间范围，修改后需要自动生成一条下发任务
     * ----
     * @param Request $request
     * @return []
     */
    public function saveData(Request $request)
    {
        $data = $request->post('Device/a');
        if(empty($data['device_no']) || empty($data['interval_begin']) || empty($data['interval_end']))
        {
            return ['error_code' => -1,'error_msg' => '参数缺失'];
        }
        $_device = [];
        if(!empty($data['device_group_id']))
        {
            $device_group = $this->DeviceGroup->getAuthDeviceGroupById($data['device_group_id']);
            if(empty($device_group))
            {
                return ['error_code' => -1,'error_msg' => '所选设备分组不存在'];
            }
            $_device['device_group_id'] = $data['device_group_id'];//设备分组ID
        }
        $device = $this->Device->getAuthDeviceById($data['id']);
        if(empty($device))
        {
            return ['error_code' => -1,'error_msg' => '拟编辑设备数据不存在'];
        }

        $_device['device_no'] = trim($data['device_no']);
        $_device['interval_begin'] = intval($data['interval_begin']);
        $_device['interval_end'] = intval($data['interval_end']);
        $_device['network'] = trim($data['network']);
        $_device['remark'] = trim($data['remark']);

        $ret = $this->Device->isUpdate(true)->save($_device,['id' => $device['id']]);
        return $ret !== false ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -1,'error_msg' => '保存失败'];
    }

    /**
     * 新增或编辑设备分组信息
     * @param Request $request
     * @return []
     */
    public function saveDeviceGroupData(Request $request)
    {
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post('Group/a',null,'trim');
        if(empty($data['name']))
        {
            return ['error_code' => -1,'error_msg' => '参数缺失'];
        }
        // 分组名称是否重复
        $is_repeat = $this->DeviceGroup->isUserDeviceGroupRepeat($user_id,$data['name']);
        // 编辑模式
        if(!empty($data['id']))
        {
            $exist = $this->DeviceGroup->getDeviceGroupById($data['id']);
            if(empty($exist) || $exist['user_id'] != $user_id)
            {
                return ['error_code' => -1,'error_msg' => '拟编辑的分组不存在或你无权限编辑该分组'];
            }
            if($data['name'] != $exist['name'])
            {
                if($is_repeat)
                {
                    return ['error_code' => -1,'error_msg' => '修改后的分组名称重复'];
                }
            }
        }else {
            if($is_repeat)
            {
                return ['error_code' => -1,'error_msg' => '分组名称重复'];
            }
        }
        $group           = [];
        $group['name']   = $data['name'];
        $group['remark'] = $data['remark'];
        $group['dept_id1'] = $dept1['dept_id'];
        $group['dept_id2'] = $dept2['dept_id'];
        $group['sort']     = intval($data['sort']) < 0 ? 0 : intval($data['sort']);
        $group['user_id']  = $user_id;

        if(!empty($data['id']))
        {
            // 修改
            $ret = $this->DeviceGroup->isUpdate(true)->save($group,['id' => $data['id']]);
        }else {
            // 新增
            $group['id'] = GenerateHelper::uuid();
            $ret = $this->DeviceGroup->isUpdate(false)->save($group);
        }
        return false !== $ret ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -1,'error_msg' => '写入数据异常'];
    }

    /**
     * 软删除设备
     * @param Request $request
     * @return []
     */
    public function deleteDevice(Request $request)
    {
        // 检查设备归属
        $device = $this->Device->getDeviceById($request->post('id'));
        if(empty($device) || !empty($device['delete_time']))
        {
            return ['error_code' => -1,'error_msg' => '设备不存在'];
        }
        $ret = $this->Device->where('id',$device['id'])->update(['delete_time' => date('Y-m-d H:i:s')]);
        return $ret > 0 ? ['error_code' => 0,'error_msg' => '执行完成，设备已删除'] : ['error_code' => -1,'error_msg' => '删除失败'];
    }

    /**
     * 删除设备分组
     * @param Request $request
     * @return array
     */
    public function deleteDeviceGroup(Request $request)
    {
        // 检查设备归属
        $group = $this->DeviceGroup->getDeviceGroupById($request->post('id'));
        if(empty($group))
        {
            return ['error_code' => -1,'error_msg' => '设备分组不存在'];
        }
        // 检查设备分组是否有设备
        $has_device = $this->Device->where(['device_group_id' => $group['id'],'delete_time' => null])->count();
        if($has_device > 0)
        {
            return ['error_code' => -1,'error_msg' => '分组下存在设备，请先删除设备再删除分组'];
        }
        $ret = $this->DeviceGroup->where('id',$group['id'])->delete();//硬删除
        return $ret > 0 ? ['error_code' => 0,'error_msg' => '执行完成，设备分组已删除','data' => $group] : ['error_code' => -1,'error_msg' => '删除失败'];
    }

}
