<?php
/**
 * 角色基类服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-13 12:14
 * @file RoleService.php
 */

namespace app\common\service;

use app\common\model\Role;
use app\common\model\User;
use think\Exception;

trait RoleTrait
{
    /**
     * @var User
     */
    public $User;
    /**
     * @var Role
     */
    public $Role;

    /**
     * 获取组织账号使用的权重范围内的角色列表
     * @param integer $user_id
     * @return array
     */
    public function getOrgRoleListByUserId($user_id)
    {
        try {
            $user = $this->User->getUserInfoById($user_id);
            if (empty($user) || empty($user['role_id'])) {
                throw new Exception('指定用户不存在');
            }
            $role = $this->Role->getRoleInfoById($user['role_id']);
            if (empty($role)) {
                throw new Exception('角色数据异常');
            }
            return $this->Role->getRoleListGtSort($role['sort']);
        } catch (Exception $e) {
            return [];
        }
    }
}
