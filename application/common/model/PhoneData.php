<?php
/**
 * 加好友使用的导入的账户昵称信息模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-17 20:54
 * @file SysAccountData.php
 */

namespace app\common\model;

use think\Db;
use think\Exception;
use think\facade\Log;
use think\Model;

class PhoneData extends Model
{

    /**
     * 统计公司级别总数据量||限定公司管理员仅一个账号
     * @param $dept_id1
     * @return int
     */
    public function getTotalCountByDeptId1($dept_id1)
    {
        return $this->where(['dept_id1' => $dept_id1])->count();
    }

    /**
     * 统计公司级别已分配数据量
     * ----
     * 未分配数据量使用总数据量减去已分配数据量即可
     * ----
     * @param $dept_id1
     * @return int
     */
    public function getTotalAllocationByDeptId1($dept_id1)
    {
        return $this->where(['dept_id1' => $dept_id1])->where('dept_id2','not null')->count();
    }

    /**
     * 统计公司级别已使用数据量
     * ----
     * 未使用数据量使用总数据量减去已使用量即可
     * ----
     * @param $dept_id1
     */
    public function getTotalUsedByDeptId1($dept_id1)
    {
        return $this->where(['dept_id1' => $dept_id1,'is_use' => 1])->count();
    }

    /**
     * 获取公司级别已分配数据的使用量统计数据
     * ----
     * 已分配未使用使用已分配总量减去已分配并且已使用即可
     * ----
     * @param $dept_id1
     * @return int
     */
    public function getTotalAllocationUsedByDeptId1($dept_id1)
    {
        return $this->where(['dept_id1' => $dept_id1,'is_use' => 1])->where('dept_id2','not null')->count();
    }

    /**
     * 获取公司级别未分配数据的使用量统计数据
     * @param $dept_id1
     */
    public function getTotalUnAllocationByDeptId1($dept_id1)
    {
        return $this->where(['dept_id1' => $dept_id1])->where('dept_id2','null')->count();
    }

    /**
     * 统计用户已分配未使用的手机号总数
     * @param $user_id
     * @param $dept_id1
     * @return int
     */
    public function getTotalUnUsedByUserId($user_id,$dept_id1)
    {
        return $this->where([
            'user_id' => $user_id,
            'dept_id1' => $dept_id1,
            'is_use' => 0
        ])->where('dept_id2','not null')->count();
    }

    /**
     * 获取指定条数的未使用手机号列表
     * @param int $total 获取到的总数
     * @param String $user_id 用户
     * @param String $dept_id1 公司ID
     * @throws
     * @return []
     */
    public function getUnusedPhoneDataForTask($total,$user_id,$dept_id1)
    {
        $data =  $this->where([
            'user_id' => $user_id,
            'dept_id1' => $dept_id1,
            'is_use' => 0
        ])->where('dept_id2','not null')
          ->field(true)
          ->limit($total)
          ->order('create_time','ASC')->select();
        if(empty($data))
        {
            throw new Exception('拟分配手机号读取异常');
        }
        return $data->toArray();
    }

    /**
     * 批量插入账号数据，方法体内部自动实现检查重复
     * @param [] $account 拟批量写入的处理好的二维数组
     * @param [] $account_assoc 拟批量写入的处理好的所有账号构成的一维数组
     * @param string $user_id 用户ID
     * @return bool
     */
    public function batchInsert($accounts,$account_assoc,$user_id)
    {
        Db::startTrans();
        try{
            Log::record('Delete开始'.date_format(date_create(),'H:i:s u'));
            // 删除已有的重复数据
            Db::name('phone_data')->where(['user_id' => $user_id])->where('phone','in',$account_assoc)->delete();
            Log::record('Insert开始'.date_format(date_create(),'H:i:s u'));
            // 写入新数据
            Db::name('phone_data')->insertAll($accounts);

            // 提交
            Db::commit();
            Log::record('Commit结束'.date_format(date_create(),'H:i:s u'));
            return true;
        }catch (\Throwable $e){
            Db::rollback();
            Log::record('导入手机号列表出现异常：'.$e->getMessage().'['.date_format(date_create(),'H:i:s u').']');
            return false;
        }
    }

    /**
     * 为业务员分配待加手机号
     * @param array $allocate_info
     * @param $per_count
     * @param $dept_id1
     * @return bool
     */
    public function allocatePhoneData($allocate_info = array(),$per_count,$dept_id1)
    {
        Db::startTrans();
        try{
            Log::record('分配开始'.date_format(date_create(),'H:i:s u'));

            foreach ($allocate_info as $key => $info)
            {
                Db::name('phone_data')->where(['dept_id1' => $dept_id1,'dept_id2' => null])->limit($per_count)->update($info);
            }
            // 提交
            Db::commit();
            Log::record('分配结束'.date_format(date_create(),'H:i:s u'));
            return true;
        }catch (\Throwable $e){
            Db::rollback();
            Log::record('分配业务员手机号出现异常：'.$e->getMessage().'['.date_format(date_create(),'H:i:s u').']');
            return false;
        }
    }

}
