<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 11:32
 * @file MessageData.php
 */

namespace app\common\model;

use think\Db;
use think\Model;
use think\Request;

class MessageData extends Model
{

    /**
     * 检索话术
     * @param Request $request
     * @throws
     * @return []
     */
    public function searchMessageData(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $dept1 = session('default_dept1');
        $type  = $request->get('type/s');
        $type  = $type == 'jiahaoyou' ? 'jiahaoyou' : 'pengyouquan';
        $query = $request->get('query/s');
        // 关键词模糊检索
        $data  = $this->where([
            'user_id'  => $user_id,
            'dept_id1' => $dept1['dept_id'],
            'message_type' => $type,
        ])->order(['create_time' => 'DESC'])->limit(20);
        if(!empty($query))
        {
            $data->where('message LIKE :query1 OR remark LIKE :query2',[
                'query1' => '%'.$query.'%',
                'query2' => '%'.$query.'%',
            ]);
        }
        $data = $data->field(['message','id'])->select();
        return $data ?
               ['error_code' => 0,'error_msg' => 'success','data' => $data->toArray()] :
               ['error_code' => 0,'error_msg' => '暂无数据','data' => []];
    }

    /**
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMessageDataById($id)
    {
        $data = $this->find($id);
        if(empty($data))
        {
            return [];
        }
        return $data->toArray();
    }
}
