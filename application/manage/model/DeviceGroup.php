<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-15 20:57
 * @file DeviceGroup.php
 */

namespace app\manage\model;

use think\Db;
use think\Model;

class DeviceGroup extends Model
{

    /**
     * 设备分组ID获取设备分组信息
     * @param $id
     * @throws
     * @return
     */
    public function getDeviceGroupById($id)
    {
        $data = $this->where(['id' => $id])->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 检查用户输入的新分组名称是否重复
     * @param $user_id
     * @param $name
     * @throws
     * @return bool true重复 false未重复
     */
    public function isUserDeviceGroupRepeat($user_id,$name)
    {
        $data = $this->where(['user_id' => $user_id,'name' => $name])->find();
        return $data ? true : false;
    }

    /**
     * 效验设备分组所有者获取设备分组信息
     * @param $id
     * @throws
     * @return
     */
    public function getAuthDeviceGroupById($id)
    {
        $user_id = session('user_id');
        return $this->where(['id' => $id,'user_id' => $user_id])->find();
    }

    /**
     * 获取当前登录用户自定义的设备自定义分组
     * @throws
     * @return []
     */
    public function getUserDeviceGroupList()
    {
        $user_id = session('user_id');
        return $this->where(['user_id' => $user_id])->order(['sort' => 'ASC'])->select();
    }
}
