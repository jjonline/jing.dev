<?php
/**
 * 用户管理登录注销部分
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:29
 * @file Sign.php
 */

namespace app\common\service\user;

use app\common\helper\GenerateHelper;
use app\common\helper\UtilHelper;
use app\common\model\User;
use app\common\service\LogService;
use app\common\service\UserLogService;
use think\Exception;
use think\facade\Cookie;
use think\facade\Session;

trait Sign
{
    /**
     * @var User
     */
    public $User;
    /**
     * @var LogService
     */
    public $LogService;
    /**
     * @var UserLogService
     */
    public $UserLogService;

    /**
     * 检查用户是否登录
     * @return bool
     * @throws
     */
    public function isUserLogin()
    {
        // 先检查cookie
        if (Cookie::get('token') && Cookie::get('user_id')) {
            // 再检查session
            if (Session::get('user_id') && Session::get('user_info')) {
                return true;
            } else {
                // cookie维持登录状态
                $User = $this->User->getUserInfoById(Cookie::get('user_id'));
                if (empty($User) || empty($User['enable'])) {
                    $this->setUserLogout();
                    return false;
                }

                // 效验cookie合法性通过后设置登录
                if ($this->generateAuthCookie($User) == Cookie::get('token')) {
                    return $this->setUserLogin($User, true);
                }
                $this->setUserLogout();
                return false;
            }
        }
        return false;
    }

    /**
     * 用户登录效验
     * ---
     * 1、效验失败抛出异常，注意try-catch
     * 2、验证码处理逻辑需在调用该方法之前自主实现
     * 3、效验成功直接自动给予登录状态和发送cookie
     * ---
     * @param array $user
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function checkUserLogin($user = array())
    {
        if (empty($user) || empty($user['user_name']) || empty($user['password'])) {
            throw new Exception('请输入账号密码', 500);
        }
        // 账号是否存在
        $user_exist = $this->User->getUserInfoAutoByUniqueKey(trim($user['user_name']));
        if (empty($user_exist)) {
            throw new Exception('账号不存在', 500);
        }
        if (empty($user_exist['enable'])) {
            throw new Exception('该账号已禁用，若需重新开通请联系平台管理员', 500);
        }
        // 防御状态禁止该账号登录
        if (UtilHelper::isInDefense($user_exist['id'])) {
            throw new Exception('密码错误次数过多，请15分钟后再试');
        }
        // 检查密码是否正确
        if (!$this->checkUserPassword($user['password'], $user_exist['password'])) {
            // 登录防御
            UtilHelper::loginDefense($user_exist['id']);
            // 密码错误之后不详细提示是账号错误还是密码错误
            throw new Exception('账号或密码错误', 500);
        }
        // 清除登录防御
        UtilHelper::releaseDefense($user_exist['id']);
        return $this->setUserLogin($user_exist, true);
    }

    /**
     * 设置用户登录状态，给予cookie、session
     * @param $User []|User 用户模型或用户数组
     * @param bool $reGenerateAuthToken 是否强制重新生成auth_code并重新生成登录cookie
     * @return bool
     * @throws Exception
     */
    public function setUserLogin($User, $reGenerateAuthToken = false)
    {
        if (empty($User) || empty($User['id']) || empty($User['auth_code'])) {
            // 调用给予登录状态方法时严格检查参数，否则抛出异常终止
            throw new Exception('给予登录状态参数错误', 500);
        }

        // 重新生成auth_code
        if (false !== $reGenerateAuthToken) {
            $this->User->updateUserAuthCode($User['id']);
        }

        // 重新获取完整的用户信息
        $User = $this->User->getFullUserInfoById($User['id']);
        // 发送cookie 浏览器关闭cookie失效
        Cookie::set('token', $this->generateAuthCookie($User));
        Cookie::set('user_id', $User['id']);
        Cookie::set('device_id', GenerateHelper::guid());

        // 保存session
        Session::set('user_id', $User['id']);
        Session::set('user_info', $User);

        // 记录底层日志
        $this->LogService->logRecorder('用户登录');
        // 记录用户日志
        $this->UserLogService->insert('登录', $User);
        return true;
    }

    /**
     * 用户退出登录
     */
    public function setUserLogout()
    {
        // 记录日志--不检查是否登录状态，可能造成检查登录的死循环
        $this->LogService->logRecorder('用户退出');
        $this->UserLogService->insert('退出登录', Session::get('user_info'));

        Cookie::delete('user_id');
        Cookie::delete('token');
        Session::clear();
    }
}
