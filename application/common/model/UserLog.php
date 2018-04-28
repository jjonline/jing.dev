<?php
/**
 * 用户操作日志模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-28 18:09
 * @file UserLog.php
 */

namespace app\common\model;

use think\Model;

class UserLog extends Model
{

    /**
     * @param $user_id
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLimitListByUserId($user_id,$limit = 10)
    {
        $data = $this->db()->alias('user_log')
              ->join('user user','user.id = user_log.user_id')
              ->join('department department','department.id = user_log.dept_id')
              ->field(['user_log.*','user.real_name','department.name as dept_name'])
              ->where('user_log.user_id',$user_id)
              ->limit($limit)
              ->order('user_log.create_time','DESC')
              ->select();
        return $data->isEmpty() ? [] : $data->toArray();
    }
}
