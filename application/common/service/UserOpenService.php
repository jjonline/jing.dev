<?php
/**
 * 开放平台服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-03 17:46
 * @file UserOpenService.php
 */

namespace app\common\service;


use app\common\model\UserOpen;
use think\Exception;

class UserOpenService
{
    /**
     * @var UserOpen
     */
    public $UserOpen;
    /**
     * @var UserService
     */
    public $UserService;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(UserOpen $userOpen,
                                LogService $logService,
                                UserService $userService)
    {
        $this->LogService  = $logService;
        $this->UserService = $userService;
        $this->UserOpen    = $userOpen;
    }

    /**
     * 绑定开放平台用户
     * @param int   $user_id    用户ID
     * @param array $open_info  与user_open表字段对应的数组，必须字段open_type和open_id
     * @param bool $is_override 是否更改绑定，false否，true时将会重写绑定
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindOpenUser($user_id,$open_info = array(),$is_override = false)
    {
        if(empty($user_id) || empty($open_info) || empty($open_info['open_type']) || empty($open_info['open_id']))
        {
            throw new Exception('绑定开放平台参数错误：必须完善开放平台信息');
        }
        if(!$this->UserOpen->hasOpenType($open_info['open_type']))
        if(!$this->UserOpen->hasOpenType($open_info['open_type']))
        {
            throw new Exception('绑定开放平台参数错误：指定开放平台类型不存在，请先完善开放平台类型的支持方式');
        }
        $user_info = $this->UserService->User->getUserInfoById($user_id);
        if(empty($user_info) || empty($user_info['enable']))
        {
            throw new Exception('绑定开放平台失败：待绑定的系统用户不存在或已被禁用');
        }

        // 拼接开放平台用户信息
        $open              = [];
        $open['user_id']   = $user_id;
        $open['open_id']   = $open_info['open_id'];
        $open['open_type'] = $open_info['open_type'];
        if(!empty($open_info['access_token']))
        {
            $open['access_token'] = trim($open_info['access_token']);
        }
        if(!empty($open_info['name']))
        {
            $open['name'] = trim($open_info['name']);
        }
        if(!empty($open_info['gender']) && in_array($open_info['gender'],[-1,0,1]))
        {
            $open['gender'] = $open_info['gender'];
        }
        if(!empty($open_info['figure']))
        {
            $open['figure'] = trim($open_info['figure']);
        }
        if(!empty($open_info['union_id']))
        {
            $open['union_id'] = trim($open_info['union_id']);
        }
        if(!empty($open_info['expire_time']))
        {
            $open['expire_time'] = date('Y-m-d H:i:s',strtotime($open_info['expire_time']));
        }

        // 更新还是新增
        $has_open_info = $this->UserOpen->getUserOpenInfoByOpenId($open_info['open_id'],$open_info['open_type']);
        if(!empty($has_open_info))
        {
            // 更新模式，更新开放平台账户信息的user_id，若已绑定过且参数并不是强制重新绑定则跑错
            if(!$is_override && !empty($has_open_info['user_id']) && $user_id != $has_open_info['user_id'])
            {
                throw new Exception('绑定开放平台失败：该开放平台账号已绑定其他用户');
            }
            $open['id'] = $has_open_info['id'];
            $result     = $this->UserOpen->isUpdate(true)->data($open)->save();
        }else {
            // 新增开放平台信息并绑定用户
            $result     = $this->UserOpen->isUpdate(false)->data($open)->save();
        }

        // 记录绑定日志并返回可直接json输出的数组
        if($result !== false)
        {
            // 记录日志，开放平台信息和用户账号信息均写入
            $this->LogService->logRecorder([$open,$user_info],'绑定开放平台账号');
            return ['error_code' => 0,'error_msg' => '绑定成功'];
        }
        return ['error_code' => 500,'error_msg' => '绑定失败：写入数据异常'];
    }

}
