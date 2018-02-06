<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-17 14:46
 * @file SysFriends.php
 */

namespace app\common\model;


use think\Db;

class SysFriends
{

    /**
     * 增量新增系统好友库||这个模型只写不删，重复的数据以后再去重处理
     * @param array $list 增量好友数据
     * @return bool|int|string
     */
    public function batchInsertFriends($list = array())
    {
        try{
            return Db::name('sys_friends')->data($list)->insertAll();
        }catch (\Throwable $e) {
           return false;
        }
    }
}
