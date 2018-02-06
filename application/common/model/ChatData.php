<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 17:14
 * @file ChatData.php
 */

namespace app\common\model;

use app\common\helpers\GenerateHelper;
use think\Db;
use think\facade\Log;
use think\Model;
use think\Request;

class ChatData extends Model
{

    /**
     * 检查是否存在A-B||B-A类型互撩，避免死循环
     * @param string $send  发出的内容
     * @param string $reply 收到的内容
     * @param string $user_id 用户级别的检查
     * @throws
     * @return bool
     */
    public function isReverseChatData($send,$reply,$user_id)
    {
        $exist = $this->where(['user_id' => $user_id,'send' => $reply,'reply' => $send])->find();
        return !!$exist;
    }

    /**
     * @param $chat_id
     * @throws
     * @return []
     */
    public function getAuthChatDataById($chat_id)
    {
        $user_id = session('user_id');
        $data    = $this->where(['id' => $chat_id,'user_id' => $user_id,'delete_time' => null])->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 批量自动检查重复、分类插入数据
     * @param [] $data 批量数据
     * @param $user_id
     * @param $dept_id1
     * @param $dept_id2
     * @return bool
     */
    public function batchInsert($data,$user_id,$dept_id1,$dept_id2)
    {
        Db::startTrans();
        try{
            Log::record('数据检查'.date_format(date_create(),'H:i:s u'));
            // 循环一条一条的执行检查
            $batch_data = [];
            foreach ($data as $key => $chat)
            {
                $is_reverse = $this->isReverseChatData($chat['send'],$chat['reply'],$user_id);
                if($is_reverse)
                {
                    continue;
                }
                // 新插入数据A-B与数据库已有A-B重复类型不处理
                $chat['chat_group_id'] = $this->autoGetGroupIdByGroupName($chat['group_name'],$user_id,$dept_id1,$dept_id2);
                unset($chat['group_name']);
                $batch_data[] = $chat;
            }
            // 记录日志批量insert
            Log::record('数据批量插入'.date_format(date_create(),'H:i:s u'));
            Db::name('chat_data')->insertAll($batch_data);
            // 提交事务
            Db::commit();
            Log::record('Commit结束'.date_format(date_create(),'H:i:s u'));
            return true;
        }catch (\Throwable $e){
            Db::rollback();
            Log::record('导入互聊列表出现异常：'.$e->getMessage().'['.date_format(date_create(),'H:i:s u').']');
            return false;
        }
    }

    /**
     * 通过分组名称获取分组ID，若没有查到则自动插入一条记录并返回ID
     * @throws
     * @param $name
     * @return string
     */
    public function autoGetGroupIdByGroupName($name,$user_id,$dept_id1,$dept_id2)
    {
        if(empty($name))
        {
            return '';
        }
        // 读取
        $data = Db::name('chat_group')->where([
            'name'     => $name,
            'user_id'  => $user_id,
            'dept_id1' => $dept_id1,
            'dept_id2' => $dept_id2
        ])->find();
        if(!empty($data))
        {
            return $data['id'];
        }
        // 写入
        $data = [
            'id'       => GenerateHelper::uuid(),
            'name'     => $name,
            'user_id'  => $user_id,
            'dept_id1' => $dept_id1,
            'dept_id2' => $dept_id2,
            'remark'   => '批量csv导入新建分组'
        ];
        $ret = Db::name('chat_group')->insert($data);
        return $ret ? $data['id'] : '';
    }

    /**
     * 检索互撩内容
     * @param Request $request
     * @throws
     * @return []
     */
    public function searchChatsData(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $group_id   = $request->get('group_id/s');
        $query      = $request->get('query/s');
        // 关键词模糊检索
        $data  = $this->where(['user_id' => $user_id])->order(['create_time' => 'DESC'])->limit(1000);
        if(!empty($group_id))
        {
            $data  = $data->where(['chat_group_id' => $group_id]);
        }
        if(!empty($query))
        {
            $data->where('send LIKE :query1 OR reply LIKE :query2 OR remark LIKE :query3',[
                'query1' => '%'.$query.'%',
                'query2' => '%'.$query.'%',
                'query3' => '%'.$query.'%',
            ]);
        }
        $data = $data->field(['send','reply'])->select();
        return $data ?
            ['error_code' => 0,'error_msg' => 'success','data' => $data->toArray()] :
            ['error_code' => 0,'error_msg' => '暂无数据','data' => []];
    }
}
