<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-15 20:21
 * @file Device.php
 */

namespace app\common\model;

use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class Device extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 设备ID直接读取设备数据
     * @param $id
     * @throws
     */
    public function getDeviceById($id)
    {
        return $this->withTrashed()->where(['id' => $id])->find();
    }

    /**
     * 统计api用户的设备额度已使用情况
     * @param $user_id
     * @return int
     */
    public function getUserDeviceQuotaUsed($user_id)
    {
        return $this->where(['user_id' => $user_id,'is_bind'=>1,'delete_time' => null])->count();
    }

    /**
     * 设备code查询设备信息
     * @param $device_code
     * @throws
     * @return []
     */
    public function getDeviceByDeviceCode($device_code)
    {
        return $this->withTrashed()->where(['device_code' => $device_code])->find();
    }

}
