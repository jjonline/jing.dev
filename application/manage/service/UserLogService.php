<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-16 19:15
 * @file UserLogService.php
 */

namespace app\manage\service;


use app\common\model\UserLog;

class UserLogService
{
    public $UserLog;


    public function __construct(UserLog $userLog)
    {
        $this->UserLog = $userLog;
    }

    /**
     * 记录用户操作日志
     * @param $user_id
     * @param $action
     * @param array $extra_data
     * @return bool
     */
    public function insertUserLog($user_id,$action,$extra_data = [])
    {
        try {
            return $this->UserLog->insertUserLog($user_id,$action,$extra_data);
        }catch (\Throwable $e) {
            return false;
        }
    }
}
