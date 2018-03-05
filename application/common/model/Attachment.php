<?php
/**
 * 附件静态资源模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 13:23
 * @file Attachment.php
 */

namespace app\common\model;

use think\Model;
use think\facade\Session;

class Attachment extends Model
{
    /**
     * 静态资源ID查找资源信息
     * @param string $id UUID形式的资源ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAttachmentById($id)
    {
        if(empty($id))
        {
            return [];
        }
        $data = $this->where('id',$id)->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 附件sha1吗用户级别的全局唯一，通过附件sha1哈希码查找附件信息
     * @param String $file_sha1 拟查找文件的sha1字符串
     * @param int $user_id 用户ID，登录状态下课不传自动获取
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAttachmentByUserFileSha1($file_sha1,$user_id = null)
    {
        if(empty($user_id) && Session::get('user_id'))
        {
            $user_id = Session::get('user_id');
        }
        if(!empty($user_id))
        {
            $data = $this->where(['user_id' => $user_id,'file_sha1' => $file_sha1])->find();
            return $data ? $data->toArray() : [];
        }else {
            $data = $this->where(['file_sha1' => $file_sha1])->select();// 不同用户的相同资源有多条的问题
            return $data->isEmpty() ? [] : $data->toArray();
        }
    }

}
