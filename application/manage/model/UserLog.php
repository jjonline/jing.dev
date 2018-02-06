<?php
/**
 * 用户操作日志模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

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
