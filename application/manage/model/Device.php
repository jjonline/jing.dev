<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-15 20:21
 * @file Device.php
 */

namespace app\manage\model;

use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class Device extends Model
{
    protected $type = [
        'delete_time'  =>  'datetime'
    ];
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 设备ID直接读取设备数据
     * @param $id
     * @throws
     */
    public function getDeviceById($id)
    {
        return $this->where(['id' => $id])->find();
    }

    /**
     * 带鉴权功能的读取设备数据方法
     * -----
     * 设备ID读取设备信息，并且检查登录用户是否有权操作该设备
     * -----
     * @param $id
     * @throws
     * @return []
     */
    public function getAuthDeviceById($id)
    {
        $user_id = session('user_id');
        $data = Db::name('device d')
             ->join('user_department user_dept','(d.dept_id1 = user_dept.dept_id1 AND user_dept.dept_id2 IS NULL) OR d.dept_id2 = user_dept.dept_id2 AND user_dept.dept_id2 IS NOT NULL')
             ->where(['user_dept.user_id'=>$user_id,'d.id' => $id])
             ->field('d.*')
             ->find();
        return $data;
    }

    /**
     * 获取有效设备列表
     * @throws
     * @return
     */
    public function getAuthDeviceList()
    {
        $user_id = session('user_id');
        $data = Db::name('device d')
              ->leftJoin('device_group dg','d.device_group_id = dg.id')
              ->field(['d.*','dg.name as group_name'])
              ->where(['d.user_id' => $user_id,'d.is_bind' => 1,'d.delete_time' => null])
              ->order(['dg.sort' => 'ASC','d.create_time' => 'ASC'])
              ->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 通过设备ID数组检查是否归当前用户所有
     * @param array $device_ids
     * @throws
     * @return []
     */
    public function getAuthDeviceListByIds($device_ids = array())
    {
        $user_id = session('user_id');
        $data = Db::name('device d')
            ->leftJoin('device_group dg','d.device_group_id = dg.id')
            ->field(['d.*','dg.name as group_name'])
            ->where(['d.user_id' => $user_id,'d.is_bind' => 1,'d.delete_time' => null])
            ->where('d.id','IN',$device_ids)
            ->order(['dg.sort' => 'ASC','d.create_time' => 'ASC'])
            ->select();
        return $data ? $data->toArray() : [];
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
}
