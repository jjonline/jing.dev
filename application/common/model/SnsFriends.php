<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-17 14:25
 * @file SnsFriends.php
 */

namespace app\common\model;

use app\common\helpers\GenerateHelper;
use think\Db;
use think\Model;
use think\Request;

class SnsFriends extends Model
{

    /**
     * 全量更新设备下好友的数据
     * @param String $device_id  设备ID
     * @param array $list 好友信息数组
     * @param array $list 好友信息数组
     * @param array $accounts 本次全量微信ID数组
     * @return bool
     */
    public function fullUpdateFriends($device_id,$list = array(),$accounts = array())
    {
        if(empty($list))
        {
            return false;
        }
        Db::startTrans();
        $batch_list = $list;
        try {
            // 删除库里有但本次全量数据里没有的数据--！会导致一个序号不连续的问题！
            Db::name('sns_friends')->where(['device_id' => $device_id])
                ->where('account','not in',$accounts)->delete();
            // 查询更新数据
            $exist = Db::name('sns_friends')->where(['device_id' => $device_id])
                ->where('account','in',$accounts)->select();
            if(!empty($exist))
            {
                foreach ($exist as $key => $value)
                {
                    foreach ($list as $key1 => $value1)
                    {
                        // 更新的数据
                        if($value['account'] == $value1['account'])
                        {
                            // 清理掉$value1中的主键ID值
                            unset($value1['id']);
                            Db::name('sns_friends')->where(['id' => $value['id']])->update($value1);
                            // 清理掉batch_list对应的元素
                            unset($batch_list[$key1]);
                        }
                    }
                }
            }
            // 如果没被清完，剩余的就是本次需要批量插入的
            if(!empty($batch_list))
            {
                // 批量新增好友数据
                Db::name('sns_friends')->data($batch_list)->insertAll();
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 增量更新设备下好友数据
     * @param $device_id
     * @param array $list
     * @return bool
     */
    public function patchUpdateFriends($device_id,$list = array())
    {
        if(empty($list))
        {
            return false;
        }
        $account = [];
        foreach ($list as $item) {
            $account[] = $item['account'];
        }
        if(empty($account))
        {
            return false;//严格检查 避免删除了该设备的所有数据
        }
        Db::startTrans();
        try {
            // 检查已存在数据
            $exist = Db::name('sns_friends')
                   ->where(['device_id' => $device_id])
                   ->where('account','in',$account)
                   ->select();
            if(!empty($exist))
            {
                foreach ($list as $key => $value)
                {
                    foreach ($exist as $index => $item) {
                        if($item['account'] == $value['account'])
                        {
                            unset($value['account_no']);//清理掉更新编号
                            Db::name('sns_friends')->where(['id' => $item['id']])->update($value);
                            unset($list[$key]);//清理到该更新多维数组的更新的元素
                        }
                    }
                }
            }
            // 上述更新逻辑执行完并unset掉更新的元素后若还有值则批量插入
            if(!empty($list))
            {
                // 批量新增好友数据
                Db::name('sns_friends')->data($list)->insertAll();
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取某个设备下的好友编号最大值
     * @param $device_id
     * @return int
     */
    public function getNextAccountNoNumberByDeviceID($device_id)
    {
        $column = $this->where(['device_id' => $device_id,'delete_time' => null])->limit(1)->value('account_no');
        return $column ? $column : 0;
    }

    /**
     * 检索指定设备的好友列表
     * @param Request $request
     * @throws
     * @return []
     */
    public function searchFriendsData(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $device_id  = $request->get('device_id/s');
        if(empty($device_id))
        {
            return ['error_code' => -1,'error_msg' => '核心参数缺失'];
        }
        $query      = $request->get('query/s');
        // 关键词模糊检索
        $data  = $this->where(['device_id' => $device_id,'is_zombie' => 0])->order(['create_time' => 'DESC'])->limit(20);
        if(!empty($query))
        {
            $data->where('nick_name LIKE :query1 OR account LIKE :query2',[
                'query1' => '%'.$query.'%',
                'query2' => '%'.$query.'%',
            ]);
        }
        $data = $data->field(['account','nick_name','remark_name'])->select();
        return $data ?
            ['error_code' => 0,'error_msg' => 'success','data' => $data->toArray()] :
            ['error_code' => 0,'error_msg' => '暂无数据','data' => []];
    }

    /**
     * 按编号范围拉取编号范围内的wx_id
     * @param Request $request
     * @throws
     * @return []
     */
    public function GetFriendsByNumNo(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $device_id  = $request->get('device_id/s');
        $start      = $request->get('start/i');
        $end        = $request->get('end/i');
        if(empty($device_id) || empty($start) || empty($end))
        {
            return ['error_code' => -1,'error_msg' => '核心参数缺失'];
        }
        if($start > $end)
        {
            return ['error_code' => -1,'error_msg' => '起始值取值有误'];
        }
        // 查询出wx_id
        $data = $this->where(['device_id' => $device_id])
              ->where('account_no','between',[$start,$end])->field('account')->select();
        $wx_ids = [];
        foreach ($data as $value)
        {
            $wx_ids[] = $value['account'];
        }
        return ['error_code' => 0,'error_msg' => 'success','data' => $wx_ids];
    }
}
