<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 17:14
 * @file ChatGroup.php
 */

namespace app\common\model;

use think\Db;
use think\Model;

class ChatGroup extends Model
{

    /**
     * @param $group_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChatGroupById($group_id)
    {
        $group = $this->find($group_id);
        return $group ? $group->toArray() : [];
    }

    /**
     * 获取用户的有效互撩分组列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @return []
     */
    public function getAuthChatGroupList()
    {
        $user_id = session('user_id');
        $data    = $this->where(['user_id' => $user_id,'delete_time' => null])->order('sort','ASC')->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 查找当前登录用户下的互撩分组
     * @param $group_id
     * @throws
     * @return []
     */
    public function getAuthChatGroupById($group_id)
    {
        $user_id = session('user_id');
        $data    = $this->where(['id' => $group_id,'user_id' => $user_id,'delete_time' => null])->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 检查用户分组是否有重复
     * @param $user_id
     * @param $name
     * @throws
     * @return bool
     */
    public function isUserChatGroupRepeat($user_id,$name)
    {
        $data = $this->where(['user_id' => $user_id,'name' => $name])->find();
        return $data ? true : false;
    }

}
