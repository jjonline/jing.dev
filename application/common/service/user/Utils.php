<?php
/**
 * 用户管理工具方法
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:29
 * @file Utils.php
 */

namespace app\common\service\user;

use app\common\model\Department;
use app\common\model\Role;
use app\common\model\User;
use think\Exception;
use think\facade\Config;

trait Utils
{
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
     * 生成加密cookie
     * @param $User []|UserModel 用户模型
     * @return string
     * @throws Exception
     */
    protected function generateAuthCookie($User)
    {
        if (empty($User) || empty($User['id'])) {
            throw new Exception('致命错误，用户数据异常');
        }
        return md5($User['auth_code'].Config::get('local.auth_key').$User['id']);
    }

    /**
     * 生成密码密文内容
     * @param  $pwd_text string 密码明文
     * @return string
     */
    protected function generateUserPassword($pwd_text)
    {
        return password_hash(Config::get('local.auth_key').trim($pwd_text), PASSWORD_BCRYPT);
    }

    /**
     * 检查用户密码
     * @param string $pwd_text 用户密码明文
     * @param string $pwd_hash 保存的密码hash
     * @return bool
     */
    protected function checkUserPassword($pwd_text, $pwd_hash)
    {
        return password_verify(Config::get('local.auth_key').trim($pwd_text), $pwd_hash);
    }

    /**
     * 检查手机号系统是否不存在，参数为空或存在就抛异常
     * @param number $mobile 待检查的手机号
     * @throws Exception
     * @throws \think\exception\DbException
     */
    protected function isMobileNotExistOrFail($mobile)
    {
        if (empty($mobile)) {
            throw new Exception('手机号不得为空');
        }

        $exist_mobile = $this->User->getUserInfoByMobile($mobile);
        if (!empty($exist_mobile)) {
            throw new Exception('手机号[' . $mobile . ']已存在');
        }
    }

    /**
     * 检查邮箱地址系统是否不存在，参数为空或存在就抛异常
     * @param string $email 待检查的邮箱
     * @throws Exception
     * @throws \think\exception\DbException
     */
    protected function isEmailNotExistOrFail($email)
    {
        if (empty($email)) {
            throw new Exception('邮箱不得为空');
        }

        $exist_mobile = $this->User->getUserInfoByEmail($email);
        if (!empty($exist_mobile)) {
            throw new Exception('邮箱[' . $email . ']已存在');
        }
    }

    /**
     * 检查用户名系统是否不存在，参数为空或存在就抛异常
     * @param string $user_name 待检查用户名
     * @throws Exception
     * @throws \think\exception\DbException
     */
    protected function isUserNameNotExistOrFail($user_name)
    {
        if (empty($user_name)) {
            throw new Exception('用户名不得为空');
        }

        $exist_mobile = $this->User->getUserInfoByEmail($user_name);
        if (!empty($exist_mobile)) {
            throw new Exception('用户名[' . $user_name . ']已存在');
        }
    }

    /**
     * 检查指定部门ID的数据是否存在，不存在抛异常
     * @param integer $dept_id 部门ID
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isDeptExistOrFail($dept_id)
    {
        if (empty($dept_id)) {
            throw new Exception('部门ID不得为空');
        }

        $dept = $this->Department->getDeptInfoById($dept_id);
        if (empty($dept)) {
            throw new Exception('ID为' . $dept_id . '的部门数据不存在');
        }
    }

    /**
     * 检查指定角色ID的数据是否存在，不存在抛异常
     * @param integer $role_id 角色ID
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isRoleExistOrFail($role_id)
    {
        if (empty($role_id)) {
            throw new Exception('角色ID不得为空');
        }

        $dept = $this->Role->getRoleInfoById($role_id);
        if (empty($dept)) {
            throw new Exception('ID为' . $role_id . '的角色数据不存在');
        }
    }
}
