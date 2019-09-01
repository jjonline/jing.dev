<?php
/**
 * 登录用户的基础操纵服务类，其他业务相关的服务类不要放到common模块下
 * ---
 * 1、用户角色、菜单权限、用户基础信息操纵
 * 2、用户所属部门等信息
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:22
 * @file UserService.php
 */

namespace app\common\service;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use app\common\helper\UtilHelper;
use app\common\model\User;
use app\common\model\Role;
use app\common\model\Department;
use app\common\service\user\Organization;
use app\common\service\user\Sign;
use app\common\service\user\Super;
use app\common\service\user\Utils;
use think\Exception;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;

class UserService extends BaseService
{
    use Super;
    use Sign;
    use Organization;
    use Utils;

    /**
     * @var User
     */
    public $User;
    /**
     * @var Department
     */
    public $Department;
    /**
     * @var Role
     */
    public $Role;
    /**
     * @var LogService
     */
    public $LogService;
    /**
     * @var UserOpenService
     */
    public $UserOpenService;
    /**
     * @var UserLogService
     */
    public $UserLogService;

    public function __construct(
        LogService $logService,
        User $User,
        Role $Role,
        Department $department,
        UserLogService $userLogService,
        UserOpenService $userOpenService
    ) {
        $this->User            = $User;
        $this->Department      = $department;
        $this->Role            = $Role;
        $this->LogService      = $logService;
        $this->UserOpenService = $userOpenService;
        $this->UserLogService  = $userLogService;
    }

    /**
     * 会员修改个人会员资料
     * @param Request $request
     * @return array
     */
    public function userModifyOwnUserInfo(Request $request)
    {
        try {
            $this->autoSmartCheckPassword($request->post('Profile.password'));
            // 效验
            if (!empty($profile['re_password']) && !FilterValidHelper::isPasswordValid($profile['re_password'])) {
                return ['error_code' => 400,'error_msg' => '新密码格式有误：6至18位同时包含数字和字母'];
            }
            if (!empty($profile['mobile']) && !FilterValidHelper::isPhoneValid($profile['mobile'])) {
                return ['error_code' => 400,'error_msg' => '手机号码格式有误'];
            }
            if (!empty($profile['email']) && !FilterValidHelper::isMailValid($profile['email'])) {
                return ['error_code' => 400,'error_msg' => '邮箱格式有误'];
            }
            if (!empty($profile['real_name']) && mb_strlen($profile['real_name'], 'utf8') >= 32) {
                return ['error_code' => 400,'error_msg' => '真实姓名长度不得超过32位'];
            }
            // 收集修改的信息
            $profile     = $request->post('Profile/a');
            $user        = $this->getLoginUserInfo();
            $user_update = [];
            // 真实姓名
            if (!empty($profile['real_name']) && $profile['real_name'] != $user['real_name']) {
                $user_update['real_name'] = trim($profile['real_name']);
            }
            // 手机号
            if (FilterValidHelper::isPhoneValid($profile['mobile']) && $profile['mobile'] != $user['mobile']) {
                $user_update['mobile'] = trim($profile['mobile']);
            }
            // 邮箱
            if (FilterValidHelper::isMailValid($profile['email']) && $profile['email'] != $user['email']) {
                $user_update['email'] = strtolower(trim($profile['email']));
            }
            // 性别
            if (in_array($profile['gender'], [-1,0,1]) && $profile['gender'] != $user['gender']) {
                $user_update['gender'] = $profile['gender'];
            }
            // 密码
            if (FilterValidHelper::isPasswordValid($profile['re_password'])
                && $profile['re_password'] != $profile['password']) {
                $user_update['password'] = $this->generateUserPassword($profile['re_password']);
            }
            // 座机号码
            if (!empty($profile['telephone']) && $profile['telephone'] != $user['telephone']) {
                $user_update['telephone'] = $profile['telephone'];
            }

            if (empty($user_update)) {
                return ['error_code' => 400,'error_msg' => '本次未修改个人信息'];
            }
            $user_update['id'] = $user['id'];
            $result = $this->User->isUpdate(true)->data($user_update)->save();
            if (false !== $result) {
                $this->LogService->logRecorder($profile, '修改个人资料');
                // 记录用户日志
                $this->UserLogService->insert('修改个人资料', $user);
                // 修改成功 清理session 下次请求自动重新生成
                Session::delete('user_id');
                Session::delete('user_info');
                return ['error_code' => 0,'error_msg' => '个人资料修改成功'];
            }
            return ['error_code' => 500,'error_msg' => '个人资料修改失败：数据库异常'];
        } catch (\Throwable $e) {
            return ['error_code' => 500,'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 登录状态下自动效验用户密码，连续3次错误自动退出登录状态
     * ---
     * 效验失败或错误次数过多抛出异常
     * ---
     * @param $password_text
     * @return bool
     * @throws Exception
     */
    public function autoSmartCheckPassword($password_text)
    {
        $times = 1;
        if (Session::get('auto_check_pwd_times')) {
            $times = Session::get('auto_check_pwd_times');
            $times++;
        }
        $User = $this->getLoginUserInfo();
        // 检查密码超过3次自动退出
        if (empty($User) || $times > 3) {
            // 记录日志 将最后一次尝试的密码记录下来
            $this->LogService->logRecorder($password_text, '登录状态下尝试密码次数过多');
            $this->setUserLogout();
            throw new Exception('密码错误次数过多，请重新登录');
        }
        if ($this->checkUserPassword($password_text, $User['password'])) {
            Session::delete('auto_check_pwd_times');//验证通过清理记录次数的session
            return true;
        }
        Session::set('auto_check_pwd_times', $times);
        throw new Exception('密码效验失败');
    }

    /**
     * 登录状态下获取登录用户信息，空数组则是未登录
     * @return array
     */
    public function getLoginUserInfo()
    {
        if (!$this->isUserLogin()) {
            return [];
        }
        return Session::get('user_info');
    }

    /**
     * 获取所有用户ID和姓名列表
     * @return array
     */
    public function getUserTreeList()
    {
        try {
            return $this->User->getUserTreeList();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 获取指定用户所属部门及子部门下辖所有用户和姓名列表
     * @param integer $user_id 指定用户的id
     * @return array
     */
    public function getAuthUserTreeList($user_id)
    {
        try {
            // 读取指定用户信息获得部门及子部门id数组
            $user = $this->User->getFullUserInfoById($user_id);
            if (empty($user)) {
                throw new Exception('指定用户不存在');
            }

            // 根用户查看所有
            if (!empty($user['is_root'])) {
                return $this->getUserTreeList();
            }

            // 普通用户按所辖部门id查找
            $multi_dept_id = $this->Department->getDeptChildAndSelfIdArrayById($user['dept_id']);
            if (empty($multi_dept)) {
                throw new Exception('没有所属部门或下辖部门');
            }

            return $this->User->getUsersByMultiDeptId($multi_dept_id);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
