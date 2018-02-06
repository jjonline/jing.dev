<?php
/**
 * 用户相关操作服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:34
 * @file UserService.php
 */

namespace app\manage\service;

use app\common\helpers\FilterValidHelper;
use app\common\helpers\GenerateHelper;
use app\common\helpers\StringHelper;
use app\manage\model\Department;
use app\manage\model\Device;
use app\manage\model\Role;
use app\manage\model\UserDepartment;
use app\manage\model\UserLog;
use app\manage\model\User;
use app\manage\model\UserRole;
use think\Db;
use think\Exception;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;
use think\Validate;

class UserService {
    /**
     * @var User
     */
    public $User;
    /**
     * @var UserLog
     */
    public $UserLogModel;
    /**
     * @var Department
     */
    public $Department;
    /**
     * @var Role
     */
    public $Role;
    /**
     * @var UserRole
     */
    public $UserRole;
    /**
     * @var UserDepartment
     */
    public $UserDepartment;
    /**
     * @var Device
     */
    public $Device;

    public function __construct(User $User,
                                Role $Role,
                                UserRole $UserRole,
                                Department $Department,
                                UserDepartment $UserDepartment,
                                Device $Device,
                                UserLog $UserLogModel)
    {
        $this->User           = $User;
        $this->Department     = $Department;
        $this->UserRole       = $UserRole;
        $this->Role           = $Role;
        $this->Device         = $Device;
        $this->UserDepartment = $UserDepartment;
        $this->UserLogModel   = $UserLogModel;
    }

    /**
     * 查询公司管理员设备额度
     * @param $user_id
     * @param $dept_id1
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDept1UserQuotaInfo($user_id,$dept_id1)
    {
        $user = $this->User->getFullUserInfoById($user_id);
        $user_dept = $this->UserDepartment->db()
                   ->where(['user_id' => $user_id,'dept_id1' => $dept_id1,'dept_id2' => null])
                   ->find();
        if(empty($user) || empty($user_dept))
        {
            return ['error_code' => -1,'error_msg' => '没有查询设备额度的权限'];
        }
        $data = [];
        $data['quota_total'] = $user['device_quota'];//公司管理员可用总额度
        $quota_allocated = $this->User->db()->name('user u')
                         ->join('user_department udp','udp.user_id = u.id')
                         ->where(['udp.dept_id1' => $dept_id1,'u.enabled' => 1])
                         ->where('udp.dept_id2 IS NOT NULL')
                         ->field('sum(u.device_quota) as allocated')
                         ->group('u.id')
                         ->select();
        if(empty($quota_allocated) || empty($quota_allocated[0]))
        {
            return ['error_code' => -1,'error_msg' => '设备额度查询出现故障'];
        }
        $data['quota_allocated'] = $quota_allocated[0]['allocated'];//公司管理员已分配额度
        $data['quota_unused']    = $data['quota_total'] - $data['quota_allocated'];//公司管理员已分配额度

        return ['error_code' => 0 ,'error_msg' => 'success','data' => $data];
    }

    /**
     * 公司管理员分配子账号设备额度
     * @param Request $request
     * @return array|mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function allocatedUserDeviceQuota(Request $request)
    {
        $quota_number = $request->post('device_quota/i',0);
        if($quota_number < 0)
        {
            return ['error_code' => -1,'error_msg' => '分配额度输入有误'];
        }
        $user_id = session('user_info.id');
        $user = $this->User->getFullUserInfoById($request->post('user_id'));
        if(empty($user))
        {
            return ['error_code' => -1,'error_msg' => '子账号不存在'];
        }
        $dept1 = session('default_dept1');
        $user_dept = $this->UserDepartment->db()
            ->where(['user_id' => $user['id']])
            ->find();
        if(empty($user) || empty($user_dept) || empty($user_dept['dept_id2']) || $dept1['dept_id'] != $user_dept['dept_id1'])
        {
            return ['error_code' => -1,'error_msg' => '子账号权限有误'];
        }
        $quota_info = $this->getDept1UserQuotaInfo($user_id,$dept1['dept_id']);
        if($quota_info['error_code'] != 0)
        {
            return $quota_info;
        }
        $quota_info = $quota_info['data'];
        // 检查公司管理员可用额度
        if($quota_info['quota_unused'] < $quota_number)
        {
            return ['error_code' => -1,'error_msg' => '可用设备绑定额度不足，无法分配'];
        }
        // 检查用于已绑定数量，拟分配额度不得低于已绑定数量
        $used_quota = $this->Device->getUserDeviceQuotaUsed($user['id']);
        if($used_quota > $quota_number)
        {
            return ['error_code' => -1,'error_msg' => '拟分配给子账号的新额度不得低于子账号已使用额度'];
        }
        // 子账号写入额度
        $quota                 = [];
        $quota['device_quota'] = $quota_number;
        $quota['id']           = $user['id'];
        $ret = $this->User->db()->update($quota);
        return $ret > 0 ?
               ['error_code' => 0,'error_msg' => '额度分配成功','data' => array_merge($quota,$quota_info)] :
               ['error_code' => -1,'error_msg' => '额度分配失败'];
    }

    /**
     * 超级管理员为公司管理员分配可绑定设备额度
     * @param Request $request
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function allocatedUserDeviceQuotaBySupper(Request $request)
    {
        $quota_number = $request->post('device_quota/i',0);
        if($quota_number < 0)
        {
            return ['error_code' => -1,'error_msg' => '分配额度输入有误'];
        }
        $user = $this->User->getFullUserInfoById($request->post('user_id'));
        if(empty($user))
        {
            return ['error_code' => -1,'error_msg' => '子账号不存在'];
        }
        $user_dept = $this->UserDepartment->db()
            ->where(['user_id' => $user['id']])
            ->find();
        if(empty($user) || empty($user_dept) || !empty($user_dept['dept_id2']))
        {
            return ['error_code' => -1,'error_msg' => '只能为公司管理员分配设备绑定额度'];
        }
        // todo 当分配的额度小于已使用额度时的检测处理

        $quota = [
            'device_quota' => $quota_number,
            'id'  => $user['id']
        ];
        $ret = $this->User->db()->update($quota);
        return $ret >= 0 ? ['error_code' => 0,'error_msg' => '分配成功','data' => ['old' => array_merge($quota,$user)]] :
               ['error_code' => -1,'error_msg' => '系统异常：分配失败'];
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
                $User = $this->User->getDataById(Cookie::get('user_id'));
                if(empty($User) || $User['delete_time'])
                {
                    return false;
                }
                // 效验cookie合法性通过后设置登录
                if($this->generateAuthCookie($User) == Cookie::get('token'))
                {
                    return $this->setUserLogin($User,UserLog::COOKIE_AUTO_LOGIN,true);
                }
                return false;
            }
        }
        return false;
    }

    /**
     * 设置用户登录状态，给予cookie、session
     * @param $User []]User 用户模型
     * @param $login_type string 登录类型标记
     * @param $reGenerate bool   是否强制重新生成auth_code并重新生成登录cookie
     * @throws
     * @return bool
     */
    public function setUserLogin($User,$login_type,$reGenerate = false)
    {
        if(empty($User) || empty($User['id']) || empty($User['auth_code']))
        {
            return false;
        }
        // 重新生成auth_code
        if(false !== $reGenerate)
        {
            $this->User->updateUserAuthCode($User);
            $User = $this->User->getDataById($User['id']);
        }
        // session中存储用户部门、角色信息||用户所属部门可能为多个、所属角色可能为多个
        $User = $this->User->getFullUserInfoById($User['id']);
        // 发送cookie 浏览器关闭cookie失效
        Cookie::set('token',$this->generateAuthCookie($User));
        Cookie::set('user_id',$User['id']);
        Cookie::set('device_id',GenerateHelper::guid());
        // 保存session
        Session::set('user_id',$User['id']);
        Session::set('user_info',$User);
        // 记录日志
        $this->UserLogModel->insertUserLog($User['id'],$login_type);
        return true;
    }

    /**
     * 用户退出登录
     */
    public function setUserLogout()
    {
        if(Session::get('user_id'))
        {
            $this->UserLogModel->insertUserLog(Session::get('user_id'),UserLog::USER_LOGOUT);
        }
        Cookie::delete('user_id');
        Cookie::delete('token');
        Session::clear();
    }

    /**
     * 检查用户是否超级管理员
     * @return bool
     */
    public function isSupper()
    {
        if(!$this->isUserLogin())
        {
            return false;
        }
        $user = Session::get('user_info');
        return $user['role']['role_name'] == '超级管理员';//一个用户只能有一个角色
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
        return md5($User['auth_code'].config('local.auth_key').$User['id']);
    }

    /**
     * 登录验证
     * @param $post []
     */
    public function doLogin($post)
    {
        $validate = new Validate;
        $validate->rule([
            'username|用户名'  => 'require',
            'password|密码'    => 'require',
        ]);
        // 令牌效验
        if(Session::get('__token__') != $post['__token__'])
        {
            return ['error_code' => -2,'error_msg' => '页面已过期，请刷新页面后再试'];
        }
        if(!$validate->check($post))
        {
            return ['error_code' => -1,'error_msg' => $validate->getError()];
        }
        $_user = $this->User->getDataByUserName($post['username']);// 暂时只支持用户名方式登录
        if(empty($_user) || $_user['enabled'] == 0 || !empty($_user['delete_time']))
        {
            return ['error_code' => -1,'error_msg' => '用户不存在或已被禁用'];
        }
        // 效验密码
        if(!$this->checkUserPassword($post['password'],$_user['password']))
        {
            // 密码错误的时候令牌使用期限的问题
            Session::set('__token__',null);//清空令牌
            return ['error_code' => -1,'error_msg' => '密码错误，请刷新页面后再试'];
        }
        // 给予登录状态
        $this->setUserLogin($_user,UserLog::ACCOUNT_LOGIN,true);
        return ['error_code' => 0,'error_msg' => '登录成功'];
    }

    /**
     * 保存用户信息，是否有权限新增用户由上层业务逻辑确定
     * @param Request $request
     * @throws
     * @return []
     */
    public function insertNewUser(Request $request)
    {
        $user = $request->post('User/a');
        // 处理部门数据
        $userDept = [];
        $dept_id1 = $user['dept_id1'];
        $dept_id2 = $user['dept_id2'];
        if(empty($dept_id1))
        {
            return ['error_code' => -1,'error_msg' => '请选择所属公司'];
        }
        $dept1 = $this->Department->getDeptById($dept_id1);
        if(empty($dept1))
        {
            return ['error_code' => -1,'error_msg' => '所属公司不存在'];
        }
        $userDept['dept_id1'] = $dept_id1;
        if(!empty($dept_id2))
        {
            $dept2 = $this->Department->getDeptById($dept_id2);
            if(empty($dept2))
            {
                return ['error_code' => -1,'error_msg' => '所属业态不存在'];
            }
            $userDept['dept_id2'] = $dept_id2;
        }
        // 处理角色数据
        $user_role = [];
        $role_name = trim($user['role_name']);
        $role      = $this->Role->getRoleByName($role_name);
        if(empty($role))
        {
            return ['error_code' => -1,'error_msg' => '所属角色不存在'];
        }
        $user_role['role_name'] = $role_name;
        $user = $this->generateNewUserInfo($user);
        if(isset($user['error_code']))
        {
            return $user;
        }
        // 检查用户名是否重复
        $exist = $this->User->where('username',$user['username'])->find();
        if($exist)
        {
            return ['error_code' => -1,'error_msg' => '用户名['.$user['user_name'].']已存在，请更换用户名'];
        }
        // 事务写入用户
        Db::startTrans();
        try {
            // 写入用户数据
            Db::name('user')->data($user)->insert();
            // 写入所属角色
            $user_role['user_id'] = $user['id'];
            $user_role['id']      = GenerateHelper::uuid();
            Db::name('user_role')->data($user_role)->insert();
            //写入所属部门
            $userDept['user_id'] = $user['id'];
            $userDept['id']      = GenerateHelper::uuid();
            Db::name('user_department')->data($userDept)->insert();
            // 提交事务
            Db::commit();
            return ['error_code' => 0,'error_msg' => '保存成功'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '保存出现错误'];
        }
    }

    /**
     * 超级管理员编辑账号信息
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function EditUser(Request $request)
    {
        $_user   = $request->post('User/a');
        $user_id = $_user['id'];
        $user    = $this->User->getFullUserInfoById($user_id);
        // 用户部门和角色
        $user_dept = $this->UserDepartment->getUserDeptInfoByUserId($user['id']);
        $user_role = $this->UserRole->getRoleInfoByUserId($user['id']);
        if(empty($user) || empty($user_dept) || empty($user_role))
        {
            return ['error_code' => -1,'error_msg' => '拟编辑用户不存在'];
        }
        // 处理部门数据
        $userDept = [];
        $dept_id1 = $_user['dept_id1'];
        $dept_id2 = $_user['dept_id2'];
        if(empty($dept_id1))
        {
            return ['error_code' => -1,'error_msg' => '请选择所属公司'];
        }
        $dept1 = $this->Department->getDeptById($dept_id1);
        if(empty($dept1))
        {
            return ['error_code' => -1,'error_msg' => '所属公司不存在'];
        }
        // 修改了公司
        if($user_dept['dept_id1'] != $dept_id1)
        {
            $userDept['dept_id1'] = $dept_id1;
            $userDept['id'] = $user_dept['id'];
        }
        // 业态处理
        if(!empty($dept_id2))
        {
            $dept2 = $this->Department->getDeptById($dept_id2);
            if(empty($dept2))
            {
                return ['error_code' => -1,'error_msg' => '所属业态不存在'];
            }
            // 修改了公司
            if($user_dept['dept_id2'] != $dept_id2)
            {
                $userDept['dept_id2'] = $dept_id2;
            }
        }else {
            if(!is_null($user_dept['dept_id2']))
            {
                $userDept['dept_id2'] = null;
                $userDept['id'] = $user_dept['id'];
            }
        }
        // dept_id2为空变更为业态不允许
        if(is_null($user_dept['dept_id2']) && !empty($userDept['dept_id2']))
        {
            return ['error_code' => -1,'error_msg' => '公司管理员不允许降级为业务员'];
        }
        // 处理角色数据
        $userRole = [];
        $role_name = trim($_user['role_name']);
        $role      = $this->Role->getRoleByName($role_name);
        if(empty($role))
        {
            return ['error_code' => -1,'error_msg' => '所属角色不存在'];
        }
        // 变更用户了角色
        if($role_name != $user_role['role_name'])
        {
            $userRole['role_name'] = $role_name;
            $userRole['id']        = $user_role['id'];
        }
        // 处理用户账号信息
        $edit_user = [];
        // 真实姓名
        if(!empty($_user['real_name']) && $_user['real_name'] != $user['real_name'])
        {
            $edit_user['real_name'] = trim($_user['real_name']);
        }
        // 登录用户名
        if(!empty($_user['username']) && $_user['username'] != $user['username'])
        {
            $edit_user['username'] = trim($_user['username']);
            // 检查用户名是否重复
            $exist = $this->User->where('username',$edit_user['username'])->find();
            if($exist)
            {
                return ['error_code' => -1,'error_msg' => '用户名['.$edit_user['user_name'].']已存在，请更换用户名'];
            }
        }
        // 手机号
        if(!empty($_user['phone']) && $_user['phone'] != $user['phone'])
        {
            $edit_user['phone'] = trim($_user['phone']);
            if(!FilterValidHelper::is_phone_valid($edit_user['phone']))
            {
                return ['error_code' => -1,'error_msg' => '手机号格式有误'];
            }
        }
        // 邮箱
        if(!empty($_user['email']) && $_user['email'] != $user['email'])
        {
            $edit_user['email'] = trim($_user['email']);
            if(!FilterValidHelper::is_mail_valid($edit_user['email']))
            {
                return ['error_code' => -1,'error_msg' => '邮箱格式有误'];
            }
        }
        // 密码
        if(!empty($_user['password']))
        {
            $edit_user['password'] = trim($_user['password']);
            if(!FilterValidHelper::is_password_valid($edit_user['password']))
            {
                return ['error_code' => -1,'error_msg' => '密码必须同时包含字母和数字，6至18位'];
            }
            $edit_user['password'] = $this->generateUserPassword($edit_user['password']);
        }
        // 备注
        if(!empty($_user['remark']) && $_user['remark'] != $user['remark'])
        {
            $edit_user['remark'] = trim($_user['remark']);
        }
        // 角色
        if(empty($edit_user) && empty($userDept) && empty($userRole))
        {
            return ['error_code' => -1,'error_msg' => '未修改任何信息'];
        }
        // 修改用户
        Db::startTrans();
        try {
            // 修改业态部门
            if(!empty($userDept))
            {
                $this->UserDepartment->db()->update($userDept);
                // 由公司管理员降级成业务员的逻辑
            }
            // 用户角色
            if(!empty($userRole))
            {
                $this->UserRole->db()->update($userRole);
            }
            // 用户信息
            if(!empty($edit_user))
            {
                $edit_user['id'] = $user_id;
                $this->User->db()->update($edit_user);
            }
            // 提交事务
            Db::commit();
            return ['error_code' => 0,'error_msg' => '编辑成功','data' => ['old' => $user]];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '编辑出现错误'];
        }
    }

    /**
     * 公司管理员新增业务员
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function saveDept2User(Request $request)
    {
        $user = $request->post('User/a');
        $default_dept1 = Session::get('default_dept1');
        // 处理部门数据
        $userDept = [];
        $dept_id1 = $default_dept1['dept_id'];
        $dept_id2 = $user['dept_id2'];
        if(empty($dept_id2))
        {
            return ['error_code' => -1,'error_msg' => '请选择所属业态部门'];
        }
        // 检查业态
        $dept2 = $this->Department->getDeptById($dept_id2);
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '所属业态部门不存在'];
        }
        $userDept['dept_id1'] = $dept_id1;
        $userDept['dept_id2'] = $dept_id2;
        // 处理角色数据
        $user_role = [];
        $role_name = '业务员';
        $role      = $this->Role->getRoleByName($role_name);
        if(empty($role))
        {
            return ['error_code' => -1,'error_msg' => '系统异常：业务员角色不存在'];
        }
        $user_role['role_name'] = $role_name;
        $user = $this->generateNewUserInfo($user);
        if(isset($user['error_code']))
        {
            return $user;//生成用户信息出错
        }
        // 检查用户名是否重复
        $exist = $this->User->where('username',$user['username'])->find();
        if($exist)
        {
            return ['error_code' => -1,'error_msg' => '用户名['.$user['user_name'].']已存在，请更换用户名'];
        }
        // 事务写入用户
        Db::startTrans();
        try {
            // 写入用户数据
            Db::name('user')->data($user)->insert();
            // 写入所属角色
            $user_role['user_id'] = $user['id'];
            $user_role['id']      = GenerateHelper::uuid();
            Db::name('user_role')->data($user_role)->insert();
            //写入所属部门
            $userDept['user_id'] = $user['id'];
            $userDept['id']      = GenerateHelper::uuid();
            Db::name('user_department')->data($userDept)->insert();
            // 提交事务
            Db::commit();
            return ['error_code' => 0,'error_msg' => '保存成功'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '保存出现错误'];
        }
    }

    /**
     * 公司管理员编辑业务员账号
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function EditDept2User(Request $request)
    {
        $_user = $request->post('User/a');
        $user_id = $_user['id'];
        $user    = $this->User->getFullUserInfoById($user_id);
        if(empty($user) || count($user['department']) > 1 || empty($user['department'][0]['dept_id2']))
        {
            return ['error_code' => -1,'error_msg' => '无权限编辑该用户'];
        }
        $edit_user = [];
        // 真实姓名
        if(!empty($_user['real_name']) && $_user['real_name'] != $user['real_name'])
        {
            $edit_user['real_name'] = trim($_user['real_name']);
        }
        // 登录用户名
        if(!empty($_user['username']) && $_user['username'] != $user['username'])
        {
            $edit_user['username'] = trim($_user['username']);
            // 检查用户名是否重复
            $exist = $this->User->where('username',$edit_user['username'])->find();
            if($exist)
            {
                return ['error_code' => -1,'error_msg' => '用户名['.$edit_user['user_name'].']已存在，请更换用户名'];
            }
        }
        // 手机号
        if(!empty($_user['phone']) && $_user['phone'] != $user['phone'])
        {
            $edit_user['phone'] = trim($_user['phone']);
            if(!FilterValidHelper::is_phone_valid($edit_user['phone']))
            {
                return ['error_code' => -1,'error_msg' => '手机号格式有误'];
            }
        }
        // 邮箱
        if(!empty($_user['email']) && $_user['email'] != $user['email'])
        {
            $edit_user['email'] = trim($_user['email']);
            if(!FilterValidHelper::is_mail_valid($edit_user['email']))
            {
                return ['error_code' => -1,'error_msg' => '邮箱格式有误'];
            }
        }
        // 密码
        if(!empty($_user['password']))
        {
            $edit_user['password'] = trim($_user['password']);
            if(!FilterValidHelper::is_password_valid($edit_user['password']))
            {
                return ['error_code' => -1,'error_msg' => '密码必须同时包含字母和数字，6至18位'];
            }
            $edit_user['password'] = $this->generateUserPassword($edit_user['password']);
        }
        // 备注
        if(!empty($_user['remark']) && $_user['remark'] != $user['remark'])
        {
            $edit_user['remark'] = trim($_user['remark']);
        }
        // 修改部门
        $dept_id2 = $_user['dept_id2'];
        if(empty($dept_id2))
        {
            return ['error_code' => -1,'error_msg' => '请选择所属业态部门'];
        }
        // 检查业态
        $dept2 = $this->Department->getDeptById($dept_id2);
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '所属业态部门不存在'];
        }
        Db::startTrans();
        try {
            // 修改业态部门
            if($user['department'][0]['dept_id2'] != $dept_id2)
            {
                $user_department = $user['department'][0];//业务员只有一个部门
                $this->UserDepartment->isUpdate(true)->save(['dept_id2' => $dept_id2],['id' => $user_department['id']]);
            }
            if(!empty($edit_user))
            {
                $this->User->isUpdate(true)->save($edit_user,['id' => $user_id]);
            }
            // 提交事务
            Db::commit();
            return ['error_code' => 0,'error_msg' => '编辑成功','data' => ['old' => $user]];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '编辑出现错误'];
        }
    }

    /**
     * 公司管理员删除子账号
     * @param Request $request
     * @return array
     */
    public function deleteDept2User(Request $request)
    {
        $user_id = $request->post('id');
        $user    = $this->User->getFullUserInfoById($user_id);
        if(empty($user) || count($user['department']) > 1 || empty($user['department'][0]['dept_id2']))
        {
            return ['error_code' => -1,'error_msg' => '无权限删除该用户'];
        }
        $device_count = $this->Device->where(['user_id' => $user_id,'delete_time' => null])->count();
        if($device_count > 0)
        {
            return ['error_code' => -1,'error_msg' => '该子账号绑定有设备，不允许删除'];
        }
        Db::startTrans();
        try {
            // 删除用户
            $this->User->where('id',$user_id)->delete();
            // 删除部门数据
            $this->UserDepartment->where('user_id',$user_id)->delete();
            // 删除所属角色
            $this->UserRole->where('user_id',$user_id)->delete();
            // 提交事务
            Db::commit();
            return ['error_code' => 0,'error_msg' => '用户删除成功','data' => ['old' => $user]];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '删除出现错误'];
        }

    }

    /**
     * 生成新用户数组信息，仅用于生成新用户信息，并不会写入
     * ----
     *  $user = [
     *   'username' => 'jing',
     *   'real_name' => '杨晶晶',
     *   'phone' => '15872254727',
     *   'email' => 'jjonline@jjonline.cn',
     *   'password' => 'laikebang@2017',
     *  ];
     *   $user = $this->UserService->generateNewUserInfo($user);
     *   User::create($user);
     * ----
     * @param $User []
     * @return []
     */
    public function generateNewUserInfo($User)
    {
        $_User = [];
        if(!empty($User['phone']) && !FilterValidHelper::is_phone_valid($User['phone']))
        {
            return ['error_code' => -1,'error_msg' => '手机号格式有误'];
        }
        if(!empty($User['email']) && !FilterValidHelper::is_mail_valid($User['email']))
        {
            return ['error_code' => -1,'error_msg' => '邮箱格式有误'];
        }
        // 姓名
        if(empty($User['real_name']) || mb_strlen($User['real_name'],'utf8') >= 50)
        {
            return ['error_code' => -1,'error_msg' => '姓名不得为空或大于50个字符'];
        }
        // 若用户名为空 则从姓名的汉字中转换 用户名仅允许小写字母
        if(empty($User['username']))
        {
            $_User['username'] = StringHelper::convertToPinyin($User['real_name']);
        }else {
            $_User['username'] = strtolower($User['username']);
        }
        // 验证密码
        if(empty($User['password']) || !FilterValidHelper::is_password_valid($User['password'],6,18))
        {
            return ['error_code' => -1,'error_msg' => '密码必须同时包含字母和数字，6至18位'];
        }
        $_User['real_name'] = trim($User['real_name']);
        $_User['phone']     = $User['phone'];
        $_User['email']     = trim($User['email']);
        $_User['auth_code'] = GenerateHelper::makeNonceStr(8);
        $_User['password']  = $this->generateUserPassword(trim($User['password']));
        $_User['id']        = GenerateHelper::uuid();
        $_User['remark']    = trim($User['remark']);

        return $_User;
    }

    /**
     * 个人中心用户编辑自己的用户信息
     * @param Request $request
     * @return array
     */
    public function updateUserInfo(Request $request)
    {
        $user_id = session('user_id');
        $user_info = $this->User->getFullUserInfoById($user_id);
        if(empty($user_info) || $user_info['enabled'] != 1)
        {
            return ['error_code' => -1,'error_msg' => '用户不存在'];
        }
        $_user = [];
        $real_name = $request->post('real_name',null,'trim');
        if(!empty($real_name) && $real_name != $user_info['real_name'])
        {
            $_user['real_name'] = $real_name;
        }
        $phone = $request->post('phone',null,'trim');
        if(!empty($phone) && $phone != $user_info['phone'])
        {
            if(!FilterValidHelper::is_phone_valid($phone))
            {
                return ['error_code' => -1,'error_msg' => '手机号格式有误'];
            }
            $_user['phone'] = $phone;
        }
        $email = $request->post('email',null,'trim');
        if(!empty($email) && $email != $user_info['email'])
        {
            if(!FilterValidHelper::is_mail_valid($email))
            {
                return ['error_code' => -1,'error_msg' => '邮箱号格式有误'];
            }
            $_user['email'] = $email;
        }
        $password = $request->post('password');
        if(!empty($password))
        {
            if(!FilterValidHelper::is_password_valid($password))
            {
                return ['error_code' => -1,'error_msg' => '密码必须由字母和数字构成，6至18位'];
            }
            $_user['password'] = $this->generateUserPassword($password);
        }

        // 修改了个人信息
        if(empty($_user))
        {
            return ['error_code' => -1,'error_msg' => '个人信息未修改'];
        }
        $ret = $this->User->where('id',$user_id)->update($_user);
        return $ret!== false ? ['error_code' => 0,'error_msg' => '个人信息修改成功','data' => ['old' => $user_info]] : ['error_code' => -1,'error_msg' => '个人信息修改失败'];
    }

    /**
     * 生成密码密文内容
     * @param  $pwd_text string 密码明文
     * @return string
     */
    protected function generateUserPassword($pwd_text)
    {
        return password_hash(config('local.auth_key').$pwd_text,PASSWORD_BCRYPT);
    }

    /**
     * 检查用户密码
     * @param string $pwd_text 用户密码明文
     * @param string $pwd_hash 保存的密码hash
     * @return bool
     */
    protected function checkUserPassword($pwd_text,$pwd_hash)
    {
        return password_verify(config('local.auth_key').$pwd_text,$pwd_hash);
    }
}
