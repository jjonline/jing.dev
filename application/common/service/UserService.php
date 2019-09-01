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

use app\common\model\User;
use app\common\model\Role;
use app\common\model\Department;
use app\common\service\user\Organization;
use app\common\service\user\Sign;
use app\common\service\user\Super;
use app\common\service\user\UserSelf;
use app\common\service\user\Utils;
use think\Exception;
use think\facade\Session;

class UserService extends BaseService
{
    use Super;
    use Sign;
    use Organization;
    use Utils;
    use UserSelf;

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
