<?php
/**
 * 角色所拥有的权限模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use think\Model;

class RoleMenu extends Model
{
    /**
     * 获取角色所拥有的菜单权限name数组
     * @param $role_name
     * @throws
     */
    public function getMenuNamesByRoleName($role_name)
    {
        $data       = $this->where(['role_name' => $role_name])->select();
        $menu_names = [];
        foreach ($data as $role_menu)
        {
            $menu_names[] = $role_menu['menu_name'];
        }
        return $menu_names;
    }
}
