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

use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use app\common\model\User;
use app\common\model\Department;
use app\common\model\Role;
use think\Exception;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;

class UserService
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
     * @var LogService
     */
    public $LogService;

    public function __construct(LogService $logService,
                                User $User,
                                Role $Role,
                                Department $Department)
    {
        $this->User           = $User;
        $this->Department     = $Department;
        $this->Role           = $Role;
        $this->LogService     = $logService;
    }

    /**
     * 检查用户是否登录
     * @return bool
     * @throws
     */
    public function isUserLogin()
    {
        // 先检查cookie
        if(Cookie::get('token') && Cookie::get('user_id'))
        {
            // 再检查session
            if(Session::get('user_id') && Session::get('user_info'))
            {
                return true;
            }else {
                // cookie维持登录状态
                $User = $this->User->getUserInfoById(Cookie::get('user_id'));
                if(empty($User) || $User['delete_time'])
                {
                    return false;
                }
                // 效验cookie合法性通过后设置登录
                if($this->generateAuthCookie($User) == Cookie::get('token'))
                {
                    return $this->setUserLogin($User,true);
                }
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
        if(empty($user) || empty($user['user_name']) || empty($user['password']))
        {
            throw new Exception('请输入账号密码',500);
        }
        // 账号是否存在
        $user_exist = $this->User->getUserInfoAutoByUniqueKey(trim($user['user_name']));
        if(empty($user_exist))
        {
            throw new Exception('账号不存在，用户名、手机号或邮箱任意一者均可作为账号',500);
        }
        if(empty($user_exist['enable']))
        {
            throw new Exception('该账号已禁用，若需重新开通请联系平台管理员',500);
        }
        // 检查密码是否正确
        if($this->checkUserPassword($user['password'],$user_exist['password']))
        {
            // 密码错误之后不详细提示是账号错误还是密码错误
            throw new Exception('账号或密码错误',500);
        }
        return $this->setUserLogin($user_exist,true);
    }

    /**
     * 设置用户登录状态，给予cookie、session
     * @param $User []|User 用户模型或用户数组
     * @param bool $reGenerateAuthToken 是否强制重新生成auth_code并重新生成登录cookie
     * @return bool
     * @throws Exception
     */
    public function setUserLogin($User,$reGenerateAuthToken = false)
    {
        if(empty($User) || empty($User['id']) || empty($User['auth_code']))
        {
            // 调用给予登录状态方法时严格检查参数，否则抛出异常终止
            throw new Exception('给予登录状态参数错误',500);
        }
        // 重新生成auth_code
        if(false !== $reGenerateAuthToken)
        {
            $this->User->updateUserAuthCode($User['id']);
        }
        // 重新获取完整的用户信息
        $User = $this->User->getFullUserInfoById($User['id']);
        // 发送cookie 浏览器关闭cookie失效
        Cookie::set('token',$this->generateAuthCookie($User));
        Cookie::set('user_id',$User['id']);
        Cookie::set('device_id',GenerateHelper::guid());
        // 保存session
        Session::set('user_id',$User['id']);
        Session::set('user_info',$User);
        // 记录日志
        $this->LogService->logRecorder('用户登录');
        return true;
    }

    /**
     * 用户退出登录
     */
    public function setUserLogout()
    {
        if($this->isUserLogin())
        {
            // 记录日志
            $this->LogService->logRecorder('用户退出');
        }
        Cookie::delete('user_id');
        Cookie::delete('token');
        Session::clear();
    }

    /**
     * 生成新用户数组信息，仅用于生成新用户信息，并不会写入
     * notice // 新增系统用户时提交的表单直接使用该方法进行效验和数据生成，需要try获取错误信息进行提示
     * ----
     *  $user = [
     *   'username' => '字母数字构成的用户名',
     *   'real_name' => '真实姓名',
     *   'mobile' => '手机号',
     *   'email' => '邮箱',
     *   'password' => '登录密码',
     *   'dept_id' => 1,
     *   'role_id' => 1,
     *  ];
     *   $user = $this->UserService->generateNewUserInfo($user);
     * ----
     * @param $User
     * @return array
     * @throws Exception
     */
    public function generateNewUserInfo($User = array())
    {
        $_User = [];
        // 手机号
        if(!empty($User['mobile']) && !FilterValidHelper::is_phone_valid($User['mobile']))
        {
            throw new Exception('手机号格式有误',500);
        }
        // 邮箱
        if(!empty($User['email']) && !FilterValidHelper::is_mail_valid($User['email']))
        {
            throw new Exception('邮箱格式有误',500);
        }
        // 姓名作为识别依据不得为空
        if(empty($User['real_name']) || mb_strlen($User['real_name'],'utf8') >= 50)
        {
            throw new Exception('姓名不得为空或大于50个字符',500);
        }
        // 用户名作为主要登录条件，必须
        if(empty($User['username']) || mb_strlen($User['real_name'],'utf8') >= 32)
        {
            throw new Exception('用户名不得为空或大于50个字符',500);
        }
        // 验证密码
        if(empty($User['password']) || !FilterValidHelper::is_password_valid(trim($User['password'])))
        {
            throw new Exception('密码必须同时包含字母和数字，6至18位',500);
        }
        // 部门
        $dept = $this->Department->getDeptInfoById($User['dept_id']);
        if(empty($dept))
        {
            throw new Exception('拟分配用户的部门信息不存在',500);
        }
        // 角色
        $role = $this->Role->getRoleInfoById($User['dept_id']);
        if(empty($role))
        {
            throw new Exception('拟分配用户的角色信息不存在',500);
        }
        $_User['real_name'] = trim($User['real_name']);
        $_User['mobile']    = $User['mobile'];
        $_User['email']     = trim($User['email']);
        $_User['auth_code'] = GenerateHelper::makeNonceStr(8);
        $_User['password']  = $this->generateUserPassword(trim($User['password']));
        $_User['dept_id']   = $User['dept_id'];
        $_User['role_id']   = $User['role_id'];
        $_User['remark']    = trim($User['remark']);

        return $_User;
    }

    /**
     * 生成客户端加密cookie
     * @param $User []|UserModel 用户模型
     * @return string
     * @throws Exception
     */
    protected function generateAuthCookie($User)
    {
        if(empty($User) || empty($User['id']))
        {
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
        return password_hash(config('local.auth_key').trim($pwd_text),PASSWORD_BCRYPT);
    }

    /**
     * 检查用户密码
     * @param string $pwd_text 用户密码明文
     * @param string $pwd_hash 保存的密码hash
     * @return bool
     */
    protected function checkUserPassword($pwd_text,$pwd_hash)
    {
        return password_verify(config('local.auth_key').trim($pwd_text),$pwd_hash);
    }
}
