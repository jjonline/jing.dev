<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-16 21:39
 * @file Task.php
 */

namespace app\common\model;

use app\common\helpers\DatetimeHelper;
use think\Db;
use think\facade\Log;
use think\Model;
use think\model\concern\SoftDelete;

class Task extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 获取指定用户的指定设备下的今日未执行的任务
     * @param string $user_id   用户ID
     * @param string $device_id 设备ID
     * @throws
     * @return []
     */
    public function getTodayUserTask($user_id,$device_id)
    {
        $today = DatetimeHelper::getTodayBeginAndEndArray();
        $task = Db::name('task task')
              ->join('task_type task_type','task_type.id = task.type_id')
              ->field(['task.*','task_type.code'])
              ->where(['task.user_id' => $user_id,'task.device_id' => $device_id])
              ->where("task.begin_time BETWEEN :begin AND :end",['begin' => $today[0],'end' => $today[1]])
              ->where(['task.status' => 0,'task.delete_time' => null]) // 未执行的、未删除的任务
              ->select();
        return $task;
    }

    /**
     * ID查找任务
     * @param $task_id
     * @throws
     * @return []
     */
    public function getTaskById($task_id)
    {
        $data = $this->db()->find($task_id);
        if(empty($data))
        {
            return [];
        }
        return $data->toArray();
    }

    /**
     * 写入加好友任务数据
     * ----
     * 1、读取分配的总手机号数据
     * 2、完善任务数据
     * 3、标记手机号已被使用
     * 4、批量写入任务数据
     * ----
     * @param []  $data 生成的task_data尚缺具体手机号、昵称的批量数组
     * @param int $phone_data_num 每个任务拟分配的手机号数目
     * @throws
     * @return bool
     */
    public function addJiaHaoYou($data,$phone_data_num)
    {
        Db::startTrans();
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $PhoneDataModel = new PhoneData();
        try{
            Log::record('微信加好友任务处理数据开始'.date_format(date_create(),'H:i:s u'));
            $phone_data = $PhoneDataModel->getUnusedPhoneDataForTask(
                $phone_data_num * count($data),
                $user_id,
                $dept1['dept_id']
            );
            $phone_data_item = array_chunk($phone_data,$phone_data_num);//读出来的手机号分组
            foreach ($data as $key => $task)
            {
                // 按分片处理每一个任务的手机号
                $_phone_data = $phone_data_item[$key];
                $phones = [];
                foreach ($_phone_data as $phone_datum) {
                    $_phone = [
                        'phone_no' => $phone_datum['phone'],
                        'nick_name'=> $phone_datum['name']
                    ];
                    $phones[] = $_phone;
                }
                $data[$key]['task_data']['phones'] = $phones;
                //task_data字段转换为json字符串
                $data[$key]['task_data'] = json_encode($data[$key]['task_data'],JSON_UNESCAPED_UNICODE);
            }
            Log::record('微信加好友任务写入任务数据开始'.date_format(date_create(),'H:i:s u'));
            // 标记手机号资源已使用
            foreach ($phone_data as $phone)
            {
                Db::name('phone_data')->where(['id' => $phone['id']])->update(['is_use' => 1]);
            }
            // 批量插入任务数据
            Db::name('task')->insertAll($data);
            // 提交
            Db::commit();
            Log::record('Commit结束'.date_format(date_create(),'H:i:s u'));
            return true;
        }catch (\Throwable $e){
            Db::rollback();
            Log::record('批量新增任务出现异常：'.$e->getMessage().'['.date_format(date_create(),'H:i:s u').']');
            return false;
        }
    }
}
