<?php
/**
 * 用户日志操纵服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-28 18:22
 * @file UserLogService.php
 */

namespace app\common\service;

use app\common\helper\GenerateHelper;
use app\common\helper\UtilHelper;
use app\common\model\UserLog;

class UserLogService
{
    /**
     * @var UserLog
     */
    public $UserLog;
    /**
     * @var IpLocationService
     */
    public $IpLocationService;

    public function __construct(UserLog $userLog,IpLocationService $ipLocationService)
    {
        $this->UserLog           = $userLog;
        $this->IpLocationService = $ipLocationService;
    }

    /**
     * 新增用户日志
     * @param string $title 用户操作的简短说明，8个字以内
     * @param array  $act_user_info 操作用户信息
     * @return false|int
     */
    public function insert($title,$act_user_info)
    {
        if(empty($act_user_info))
        {
            return false;
        }
        $request    = app('request');
        $ip         = $request->ip();
        $location   = $this->IpLocationService->getLocation($ip);
        $user_agent = $request->header('User-Agent');

        $log = [
            'id'       => GenerateHelper::uuid(),
            'user_id'  => $act_user_info['id'],
            'dept_id'  => $act_user_info['dept_id'],
            'title'    => $title,
            'os'       => UtilHelper::get_os_info($user_agent),
            'browser'  => UtilHelper::get_browser_info($user_agent),
            'location' => $location['country'].$location['area'],
            'ip'       => $ip,
        ];
        return $this->UserLog->isUpdate(false)->save($log);
    }

    /**
     * 获取最近的10条登录记录
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLastTenItemByUserId($user_id)
    {
        return $this->UserLog->getLimitListByUserId($user_id,10);
    }
}
