<?php
/**
 * 用户个人信息维护
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 16:22
 * @file UserSelf.php
 */

namespace app\common\service\user;

use app\common\helper\FilterValidHelper;
use app\common\helper\StringHelper;
use app\common\model\User;
use app\common\model\UserLog;
use app\common\service\LogService;
use app\common\service\UserLogService;
use think\Exception;
use think\facade\Session;

trait UserSelf
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
     * 用户自己修改自己的个人资料 -- 密码+真实姓名+手机号+邮箱+性别+座机
     * @param array $_user
     * @param array $act_user
     * @return array
     */
    public function userModifyOwnInfo(array $_user, array $act_user)
    {
        try {
            $rule = [
                'id'          => 'require|number',
                'real_name'   => 'require|chsAlphaNum|max:32', // 汉字、字母和数字，内置长度使用 mb_strlen
                'gender'      => 'require|in:-1,0,1',
                'password'    => 'require|length:6,18', // 密码6—18位
                'mobile'      => 'mobile',
                'email'       => 'email|max:128',
                'telephone'   => 'alphaDash|length:6,12', // 字母、数字_-构成，长度6至12位
                're_password' => 'length:6,18', // 可选新密码6—18位
            ];
            $column = [
                'id'          => '用户ID',
                'real_name'   => '真实姓名',
                'gender'      => '性别',
                'password'    => '登录密码',
                'mobile'      => '手机号',
                'email'       => '邮箱',
                'telephone'   => '座机号码',
                're_password' => '新密码',
            ];
            $this->checkRequestVariablesOrFail($_user, $rule, $column);

            // 被编辑用户信息
            $edit_user = $this->User->getFullUserInfoById($_user['id']);
            if (empty($edit_user) || $edit_user['id'] != $act_user['id']) {
                throw new Exception('待编辑用户不存在');
            }

            // 老密码校验
            $this->autoSmartCheckPassword($_user['Profile.password']);

            // 手机号、邮箱唯一校验
            if (!empty($_user['mobile']) && $edit_user['mobile'] != $_user['mobile']) {
                $this->isMobileNotExistOrFail($_user['mobile']);
            }
            if (!empty($_user['email']) && $edit_user['email'] != $_user['email']) {
                $this->isEmailNotExistOrFail($_user['email']);
            }

            // 有改密码，校验新密码
            if (!empty($_user['re_password']) && !FilterValidHelper::isPasswordValid($_user['re_password'])) {
                throw new Exception('新密码必须同时包含字母和数字，6至18位');
            }

            $user              = [];
            $user['id']        = $_user['id'];
            $user['mobile']    = $_user['mobile'];
            $user['email']     = $_user['email'];
            $user['real_name'] = $_user['real_name'];
            $user['gender']    = $_user['gender'];
            $user['telephone'] = $_user['telephone'];

            if (!empty($_user['re_password'])) {
                $user['password']  = $this->generateUserPassword($_user['telephone']);
                $user['auth_code'] = StringHelper::randString(8);
            }

            $result = $this->User->isUpdate(true)->data($user)->save();
            if (false !== $result) {
                $this->LogService->logRecorder([$_user, $user], UserLog::ACTION_MODIFY);
                // 记录用户日志
                $this->UserLogService->insert(UserLog::ACTION_MODIFY, $user);

                // 修改成功 清理session 下次请求自动重新生成
                Session::delete('user_id');
                Session::delete('user_info');

                return ['error_code' => 0,'error_msg' => '个人资料修改成功'];
            }
            return ['error_code' => 500,'error_msg' => '个人资料修改失败：数据库异常'];
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
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
    private function autoSmartCheckPassword($password_text)
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
}
