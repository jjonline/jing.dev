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
use app\common\model\Role;
use think\Exception;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;

class UserService
{
    /**
     * @var User
     */
    public $User;
    /**
     * @var DepartmentService
     */
    public $DepartmentService;
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

    public function __construct(LogService $logService,
                                User $User,
                                Role $Role,
                                DepartmentService $departmentService,
                                UserLogService $userLogService,
                                UserOpenService $userOpenService)
    {
        $this->User              = $User;
        $this->DepartmentService = $departmentService;
        $this->Role              = $Role;
        $this->LogService        = $logService;
        $this->UserOpenService   = $userOpenService;
        $this->UserLogService    = $userLogService;
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
                if(empty($User) || empty($User['enable']))
                {
                    $this->setUserLogout();
                    return false;
                }
                // 效验cookie合法性通过后设置登录
                if($this->generateAuthCookie($User) == Cookie::get('token'))
                {
                    return $this->setUserLogin($User,true);
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
        if(empty($user) || empty($user['user_name']) || empty($user['password']))
        {
            throw new Exception('请输入账号密码',500);
        }
        // 账号是否存在
        $user_exist = $this->User->getUserInfoAutoByUniqueKey(trim($user['user_name']));
        if(empty($user_exist))
        {
            throw new Exception('账号不存在',500);
        }
        if(empty($user_exist['enable']))
        {
            throw new Exception('该账号已禁用，若需重新开通请联系平台管理员',500);
        }
        // 检查密码是否正确
        if(!$this->checkUserPassword($user['password'],$user_exist['password']))
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
        // 记录底层日志
        $this->LogService->logRecorder('用户登录');
        // 记录用户日志
        $this->UserLogService->insert('登录',$User);
        return true;
    }

    /**
     * 用户退出登录
     */
    public function setUserLogout()
    {
        // 记录日志--不检查是否登录状态，可能造成检查登录的死循环
        $this->LogService->logRecorder('用户退出');
        $this->UserLogService->insert('退出登录',Session::get('user_info'));
        Cookie::delete('user_id');
        Cookie::delete('token');
        Session::clear();
    }

    /**
     * 超级管理员编辑修改其他管理员信息
     * @param Request $request
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function superUserUpdateUser(Request $request,$act_user_info)
    {
        $_user = $request->post('User/a');
        // 待编辑的用户是否存在
        if(empty($_user['id']))
        {
            return ['error_code' => 400,'error_msg' => '参数缺失'];
        }
        $exist_user = $this->User->getUserInfoById($_user['id']);
        if(empty($exist_user))
        {
            return ['error_code' => 400,'error_msg' => '拟编辑用户信息不存在'];
        }
        // 是否有权限编辑
        if(!in_array($exist_user['dept_id'],$act_user_info['dept_auth']['dept_id_vector']))
        {
            return ['error_code' => 400,'error_msg' => '您无权限编辑该用户的信息'];
        }

        // 收集修改编辑过的item
        $update_user = [];
        // 修改用户名
        if($_user['user_name'] != $exist_user['user_name'])
        {
            $repeat = $this->User->getUserInfoByUserName(trim($_user['user_name']));
            if(!empty($repeat))
            {
                return ['error_code' => 400,'error_msg' => '用户名已存在'];
            }
            $update_user['user_name'] = trim($_user['user_name']);
        }
        // 修改真实姓名
        if($_user['real_name'] != $exist_user['real_name'])
        {
            if(mb_strlen($_user['real_name'],'utf8') >= 50)
            {
                return ['error_code' => 400,'error_msg' => '真实姓名不得大于50个字符'];
            }
            $update_user['real_name'] = trim($_user['real_name']);
        }
        // 修改性别
        if($_user['gender'] != $exist_user['gender'])
        {
            if(in_array($_user['gender'],[-1,0,1]))
            {
                $update_user['gender'] = $_user['gender'];
            }
        }
        // 修改或删掉手机号
        if($_user['mobile'] != $exist_user['mobile'])
        {
            if(!empty($_user['mobile']))
            {
                // 修改手机号
                if(!FilterValidHelper::is_phone_valid(trim($_user['mobile'])))
                {
                    return ['error_code' => 400,'error_msg' => '手机号格式有误'];
                }
                $repeat = $this->User->getUserInfoByMobile(trim($_user['mobile']));
                if(!empty($repeat))
                {
                    return ['error_code' => 400,'error_msg' => '手机号已存在'];
                }
                $update_user['mobile'] = trim($_user['mobile']);
            }else {
                // 删除手机号
                $update_user['mobile'] = '';
            }
        }
        // 修改或删掉邮箱
        if($_user['email'] != $exist_user['email'])
        {
            if(!empty($_user['email']))
            {
                // 修改邮箱
                if(!FilterValidHelper::is_mail_valid(trim($_user['email'])))
                {
                    return ['error_code' => 400,'error_msg' => '邮箱格式有误'];
                }
                $repeat = $this->User->getUserInfoByEmail(trim($_user['email']));
                if(!empty($repeat))
                {
                    return ['error_code' => 400,'error_msg' => '邮箱已存在'];
                }
                $update_user['email'] = trim($_user['email']);
            }else {
                // 删除邮箱
                $update_user['email'] = '';
            }
        }
        // 修改座机
        if($_user['telephone'] != $exist_user['telephone'])
        {
            if(!empty($_user['telephone']))
            {
                // 修改座机
                $update_user['telephone'] = trim($_user['telephone']);
            }else {
                // 删除座机
                $update_user['telephone'] = '';
            }
        }
        // 修改部门
        if($_user['dept_id'] != $exist_user['dept_id'])
        {
            $exist_dept = $this->DepartmentService->Department->getDeptInfoById($_user['dept_id']);
            if(empty($exist_dept))
            {
                return ['error_code' => 400,'error_msg' => '拟分配的部门不存在'];
            }
            $update_user['dept_id'] = $_user['dept_id'];
        }
        // 修改角色
        if($_user['role_id'] != $exist_user['role_id'])
        {
            $exist_role = $this->Role->getRoleInfoById($_user['role_id']);
            if(empty($exist_role))
            {
                return ['error_code' => 400,'error_msg' => '拟分配的角色不存在'];
            }
            $update_user['role_id'] = $_user['role_id'];
        }
        // 修改备注
        if(empty($_user['remark']) && $_user['remark'] != $exist_user['remark'])
        {
            $update_user['remark'] = trim($_user['remark']);
        }
        // 启用|禁用 && 是否部门领导
        $is_leader = isset($_user['is_leader']) ? 1 : 0;
        $enable    = isset($_user['enable']) ? 1 : 0;
        if($is_leader != $exist_user['is_leader'])
        {
            $update_user['is_leader'] = $is_leader;
        }
        if($enable != $exist_user['enable'])
        {
            $update_user['enable'] = $enable;
        }

        // 修改密码
        if(!empty($_user['password']) && FilterValidHelper::is_password_valid($_user['password']))
        {
            $update_user['password'] = $this->generateUserPassword($_user['password']);
        }

        if(empty($update_user))
        {
            return ['error_code' => 200,'error_msg' => '未修改任何信息'];
        }

        // 补充ID 开始更新
        $update_user['id'] = $exist_user['id'];
        $result = $this->User->isUpdate(true)->save($update_user);
        if(false !== $result)
        {
            $this->LogService->logRecorder([$update_user,$_user,$exist_user],'单独更新编辑后台用户');
            return ['error_code' => 0,'error_msg' => '编辑用户信息成功'];
        }
        return ['error_code' => 500,'error_msg' => '编辑用户信息失败：系统异常'];
    }

    /**
     * super管理员单独新增后台用户，不涉及职员信息维护和管理
     * @param Request $request
     * @return array
     */
    public function superUserInsertUser(Request $request)
    {
        $_user = $request->post('User/a');
        try{
            // 自动检测生成新用户信息
            $user              = $this->generateNewUserInfo($_user);
            // 补充座机和是否领导以及是否启用
            $user['telephone'] = !empty($_user['telephone']) ? $_user['telephone'] : '';
            $user['is_leader'] = !empty($_user['is_leader']) ? 1 : 0;
            $user['enable']    = !empty($_user['enable']) ? 1 : 0;
            $user['auth_code'] = GenerateHelper::makeNonceStr(8);
            $result = $this->User->isUpdate(false)->save($user);
            if(false !== $result)
            {
                $this->LogService->logRecorder([$_user,$user],'单独新增后台用户');
                return ['error_code' => 0,'error_msg' => '新增用户成功，初始密码为：'.$_user['password']];
            }
            return ['error_code' => 500,'error_msg' => '新增用户失败：系统异常'];
        }catch (\Throwable $e) {
            return ['error_code' => 500,'error_msg' => $e->getMessage()];
        }
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
        if(empty($User['user_name']) || mb_strlen($User['user_name'],'utf8') >= 32)
        {
            throw new Exception('用户名不得为空或大于50个字符',500);
        }
        // 用户名不能重复
        $user_name_repeat = $this->User->getUserInfoByUserName($User['user_name']);
        if(!empty($user_name_repeat))
        {
            throw new Exception('用户名'.$User['user_name'].'已存在',500);
        }
        // 性别
        if(!empty($User['gender']) && in_array($User['gender'],[-1,0,1]))
        {
            $_User['gender'] = $User['gender'];
        }
        // 检查非空手机号是否重复
        if(!empty($User['mobile']))
        {
            $mobile_exist = $this->User->getUserInfoByMobile($User['mobile']);
            if(!empty($mobile_exist))
            {
                throw new Exception('手机号已存在',500);
            }
        }
        // 检查非空邮箱是否重复
        if(!empty($User['email']))
        {
            $email_exist = $this->User->getUserInfoByEmail($User['email']);
            if(!empty($email_exist))
            {
                throw new Exception('邮箱已存在',500);
            }
        }
        // 验证密码
        if(empty($User['password']) || !FilterValidHelper::is_password_valid(trim($User['password'])))
        {
            throw new Exception('密码必须同时包含字母和数字，6至18位',500);
        }
        // 部门
        $dept = $this->DepartmentService->Department->getDeptInfoById($User['dept_id']);
        if(empty($dept))
        {
            throw new Exception('拟分配用户的部门信息不存在',500);
        }
        // 角色
        $role = $this->Role->getRoleInfoById($User['role_id']);
        if(empty($role))
        {
            throw new Exception('拟分配用户的角色信息不存在',500);
        }
        $_User['user_name'] = trim($User['user_name']);
        $_User['real_name'] = trim($User['real_name']);
        $_User['mobile']    = !empty($User['mobile']) ? $User['mobile'] : '';
        $_User['email']     = !empty($User['email'])  ? trim($User['email']) : '';
        $_User['auth_code'] = GenerateHelper::makeNonceStr(8);
        $_User['password']  = $this->generateUserPassword(trim($User['password']));
        $_User['dept_id']   = $User['dept_id'];
        $_User['role_id']   = $User['role_id'];
        $_User['remark']    = !empty($User['remark']) ? trim($User['remark']) : '';

        return $_User;
    }

    /**
     * 会员修改个人会员资料
     * @param Request $request
     * @return array
     */
    public function userModifyOwnUserInfo(Request $request)
    {
        try{
            $this->autoSmartCheckPassword($request->post('Profile.password'));
            // 效验
            if(!empty($profile['re_password']) && !FilterValidHelper::is_password_valid($profile['re_password']))
            {
                return ['error_code' => 400,'error_msg' => '新密码格式有误：6至18位同时包含数字和字母'];
            }
            if(!empty($profile['mobile']) && !FilterValidHelper::is_phone_valid($profile['mobile']))
            {
                return ['error_code' => 400,'error_msg' => '手机号码格式有误'];
            }
            if(!empty($profile['email']) && !FilterValidHelper::is_mail_valid($profile['email']))
            {
                return ['error_code' => 400,'error_msg' => '邮箱格式有误'];
            }
            if(!empty($profile['real_name']) && mb_strlen($profile['real_name'],'utf8') >= 32)
            {
                return ['error_code' => 400,'error_msg' => '真实姓名长度不得超过32位'];
            }
            // 收集修改的信息
            $profile     = $request->post('Profile/a');
            $user        = $this->getLoginUserInfo();
            $user_update = [];
            // 真实姓名
            if(!empty($profile['real_name']) && $profile['real_name'] != $user['real_name'])
            {
                $user_update['real_name'] = trim($profile['real_name']);
            }
            // 手机号
            if(FilterValidHelper::is_phone_valid($profile['mobile']) && $profile['mobile'] != $user['mobile'])
            {
                $user_update['mobile'] = trim($profile['mobile']);
            }
            // 邮箱
            if(FilterValidHelper::is_mail_valid($profile['email']) && $profile['email'] != $user['email'])
            {
                $user_update['email'] = trim($profile['email']);
            }
            // 性别
            if(in_array($profile['gender'],[-1,0,1]) && $profile['gender'] != $user['gender'])
            {
                $user_update['gender'] = $profile['gender'];
            }
            // 密码
            if(FilterValidHelper::is_password_valid($profile['re_password']) && $profile['re_password'] != $profile['password'])
            {
                $user_update['password'] = $this->generateUserPassword($profile['re_password']);
            }
            // 座机号码
            if(!empty($profile['telephone']) && $profile['telephone'] != $user['telephone'])
            {
                $user_update['telephone'] = $profile['telephone'];
            }

            if(empty($user_update))
            {
                return ['error_code' => 400,'error_msg' => '本次未修改个人信息'];
            }
            $user_update['id'] = $user['id'];
            $result = $this->User->isUpdate(true)->data($user_update)->save();
            if(false !== $result)
            {
                $this->LogService->logRecorder($profile,'修改个人资料');
                // 记录用户日志
                $this->UserLogService->insert('修改个人资料',$user);
                // 修改成功 清理session 下次请求自动重新生成
                Session::delete('user_id');
                Session::delete('user_info');
                return ['error_code' => 0,'error_msg' => '个人资料修改成功'];
            }
            return ['error_code' => 500,'error_msg' => '个人资料修改失败：数据库异常'];
        }catch (\Throwable $e){
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
        if(Session::get('auto_check_pwd_times'))
        {
            $times = Session::get('auto_check_pwd_times');
            $times++;
        }
        $User = $this->getLoginUserInfo();
        // 检查密码超过3次自动退出
        if(empty($User) || $times > 3)
        {
            // 记录日志 将最后一次尝试的密码记录下来
            $this->LogService->logRecorder($password_text,'登录状态下尝试密码次数过多');
            $this->setUserLogout();
            throw new Exception('密码错误次数过多，请重新登录');
        }
        if($this->checkUserPassword($password_text,$User['password']))
        {
            Session::delete('auto_check_pwd_times');//验证通过清理记录次数的session
            return true;
        }
        Session::set('auto_check_pwd_times',$times);
        throw new Exception('密码效验失败');
    }

    /**
     * 登录状态下获取登录用户信息，空数组则是未登录
     * @return array
     */
    public function getLoginUserInfo()
    {
        if(!$this->isUserLogin())
        {
            return [];
        }
        return Session::get('user_info');
    }

    /**
     * 带部门权限判断的启用|禁用用户
     * @param mixed|int $user_id
     * @param array $act_user_info 控制器中的包含菜单、部门权限信息的UserInfo属性数组
     * @return array
     * @throws \think\exception\DbException
     */
    public function enableUserToggle($user_id,$act_user_info = array())
    {
        if(empty($user_id) || empty($act_user_info))
        {
            return ['error_code' => 400,'error_msg' => '参数缺失'];
        }
        $user            = $this->User->getUserInfoById($user_id);
        $act_dept_vector = $act_user_info['dept_auth']['dept_id_vector'] ?? [];
        if(!in_array($user['dept_id'],$act_dept_vector))
        {
            return ['error_code' => 500,'error_msg' => '您无权限启用或禁用该用户'];
        }

        // 启用或禁用用户写入
        $_enable           = [];
        $_enable['id']     = $user['id'];
        $_enable['enable'] = $user['enable'] ? 0 : 1;
        $result            = $this->User->isUpdate(true)->save($_enable);
        if(false !== $result)
        {
            $this->LogService->logRecorder($user,'启用或禁用用户');
            return ['error_code' => 0,'error_msg' => $user['enable'] ? '禁用完成' : '启用完成'];
        }
        return ['error_code' => 500,'error_msg' => '操作失败：数据库异常'];
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
        return password_hash(Config::get('local.auth_key').trim($pwd_text),PASSWORD_BCRYPT);
    }

    /**
     * 检查用户密码
     * @param string $pwd_text 用户密码明文
     * @param string $pwd_hash 保存的密码hash
     * @return bool
     */
    protected function checkUserPassword($pwd_text,$pwd_hash)
    {
        return password_verify(Config::get('local.auth_key').trim($pwd_text),$pwd_hash);
    }
}
