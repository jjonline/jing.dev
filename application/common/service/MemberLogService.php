<?php
/**
 * 前台用户日志操纵服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-28 18:22
 * @file UserLogService.php
 */

namespace app\common\service;

use app\common\helper\GenerateHelper;
use app\common\helper\UtilHelper;
use app\common\model\MemberLog;

class MemberLogService
{
    /**
     * @var MemberLog
     */
    public $MemberLog;
    /**
     * @var IpLocationService
     */
    public $IpLocationService;

    public function __construct(MemberLog $memberLog, IpLocationService $ipLocationService)
    {
        $this->MemberLog         = $memberLog;
        $this->IpLocationService = $ipLocationService;
    }

    /**
     * 新增用户日志
     * @param string $title 用户操作的简短说明，8个字以内
     * @param int    $member_id 前台用户信息
     * @return false|int
     */
    public function insert($title, $member_id)
    {
        if (empty($act_user_info)) {
            return false;
        }
        $request    = app('request');
        $ip         = $request->ip();
        $location   = $this->IpLocationService->getLocation($ip);
        $user_agent = $request->header('User-Agent');

        $log = [
            'id'        => GenerateHelper::uuid(),
            'member_id' => $member_id,
            'title'     => $title,
            'os'        => UtilHelper::get_os_info($user_agent),
            'browser'   => UtilHelper::get_browser_info($user_agent),
            'location'  => $location['country'] . $location['area'],
            'ip'        => $ip,
        ];
        return $this->MemberLog->isUpdate(false)->save($log);
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
        return $this->MemberLog->getLimitListByUserId($user_id, 10);
    }
}
