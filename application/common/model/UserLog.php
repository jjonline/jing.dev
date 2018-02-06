<?php
/**
 * 用户操作日志模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-16 19:21:51
 * @file
 */

namespace app\common\model;

use app\common\helpers\GenerateHelper;
use think\exception\DbException;
use think\Model;

class UserLog extends Model
{
    /**
     * 登录类型标记常量：cookie自动登录
     */
    const COOKIE_AUTO_LOGIN = 'COOKIE_AUTO_LOGIN';
    /**
     * 登录类型标记常量：账户密码登录
     */
    const ACCOUNT_LOGIN     = 'ACCOUNT_LOGIN';
    /**
     * 用户手动退出
     */
    const USER_LOGOUT       = 'USER_LOGOUT';
    /**
     * api登录
     */
    const API_LOGIN         = 'API_LOGIN';
    /**
     * api绑定
     */
    const API_BIND          = 'API_BIND';
    /**
     * api解绑
     */
    const API_UNBIND        = 'API_UNBIND';
    /**
     * api查询任务--有任务时记录
     */
    const API_QUERY_TASK    = 'API_QUERY_TASK';
    /**
     * api修改任务状态
     */
    const API_TASK_STATUS    = 'API_TASK_STATUS';
    /**
     * 上传批量导入账号文件
     */
    const UPLOAD_ACCOUNT_FILE     = 'UPLOAD_ACCOUNT_FILE';
    /**
     * 导入批量手机号
     */
    const IMPORT_ACCOUNT_DATA   = 'IMPORT_ACCOUNT_DATA';
    /**
     * 为业务员分配待加手机号
     */
    const ALLOCATION_PHONE_DATA = 'ALLOCATION_PHONE_DATA';
    /**
     * 上传批量导入互撩文件
     */
    const UPLOAD_CHAT_FILE     = 'UPLOAD_CHAT_FILE';
    /**
     * 导入批量互撩
     */
    const IMPORT_CHAT_DATA     = 'IMPORT_CHAT_DATA';
    /**
     * 新增任务
     */
    const ADD_NEW_TASK         = 'ADD_NEW_TASK';
    /**
     * 删除设备
     */
    const DELETE_DEVICE        = 'DELETE_DEVICE';
    /**
     * 删除设备分组
     */
    const DELETE_DEVICE_GROUP  = 'DELETE_DEVICE_GROUP';
    /**
     * 删除任务
     */
    const DELETE_TASK        = 'DELETE_TASK';
    /**
     * 删除互撩
     */
    const DELETE_CHAT        = 'DELETE_CHAT';
    /**
     * 删除话术
     */
    const DELETE_MESSAGE     = 'DELETE_MESSAGE';
    /**
     * 删除互撩分组
     */
    const DELETE_CHAT_GROUP  = 'DELETE_CHAT_GROUP';
    /**
     * 停止或启动任务
     */
    const HANDLE_TASK        = 'HANDLE_TASK';
    /**
     * 删除素材数据
     */
    const DELETE_RESOURCE_DATA  = 'DELETE_RESOURCE_DATA';
    /**
     * 删除公司
     */
    const DELETE_DEPT1  = 'DELETE_DEPT1';
    /**
     * 删除业态
     */
    const DELETE_DEPT2  = 'DELETE_DEPT2';
    /**
     * 修改个人信息
     */
    const EDIT_USER_INFO  = 'EDIT_USER_INFO';
    /**
     * 编辑-新增部门
     */
    const EDIT_DEPARTMENT = 'EDIT_DEPARTMENT';
    /**
     * 新增业态用户
     */
    const INSERT_DEP2USER = 'INSERT_DEP2USER';
    /**
     * 删除业态用户
     */
    const DELETE_DEP2USER = 'DELETE_DEP2USER';
    /**
     * 编辑业态用户
     */
    const EDIT_DEP2USER   = 'EDIT_DEP2USER';
    /**
     * 公司管理员分配设备绑定额度
     */
    const DEVICE_QUOTA_ALLOCATED = 'DEVICE_QUOTA_ALLOCATED';

    /**
     * 用户ID查找用户信息
     * @param $user_id string 用户ID
     * @throws DbException
     */
    public function getModelById($user_id)
    {
        return $this->get(['id' => $user_id]);
    }

    /**
     * 新增用户操作日志
     * @param $user_id  string 用户ID
     * @param $action   string 操作动作
     * @param $extra_data []   操作日志额外写入的数据
     * @return bool
     */
    public function insertUserLog($user_id,$action,$extra_data = [])
    {
        // 处理额外数据
        $extra_data = empty($extra_data) ? ['info' => '无额外数据'] : $extra_data;
        $data                = [];
        $data['id']          = GenerateHelper::uuid();
        $data['user_id']     = $user_id;
        $data['action']      = $action;
        $data['extra_data']  = json_encode($extra_data,JSON_UNESCAPED_UNICODE);
        return $this->insert($data);
    }
}
