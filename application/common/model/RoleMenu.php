<?php
/**
 * 角色菜单
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:18
 * @file RoeMenu.php
 */

namespace app\common\model;

use think\Db;
use think\Model;

class RoleMenu extends Model
{
    protected $json = ['show_columns'];

    /**
     * 依据角色ID查询该角色所拥有的菜单列表和菜单权限
     * @param $role_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuListByRoleId($role_id)
    {
        $data = Db::name('menu menu')
              ->join('role_menu role_menu', 'menu.id = role_menu.menu_id')
              ->where('role_menu.role_id', $role_id)
              ->field([
                  'menu.*',
                  'role_menu.permissions as permissions',
                  'role_menu.show_columns as show_columns',
              ])
              ->order(['menu.sort' => 'ASC','menu.level' => 'ASC'])
              ->select();
        return $data->isEmpty() ? [] : $data->toArray();
    }

    /**
     * 通过用户ID查询用户所具有的菜单权限列表
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuListByUserId($user_id)
    {
        $data = Db::name('menu menu')
              ->join('role_menu role_menu', 'menu.id = role_menu.menu_id')
              ->join('user user', 'user.role_id = role_menu.role_id')
              ->where('user.id', $user_id)
              ->field([
                  'menu.*',
                  'role_menu.permissions as permissions',
                  'role_menu.show_columns as show_columns',
              ])
              ->order(['menu.sort' => 'ASC','menu.level' => 'ASC'])
              ->select();
        return $data->isEmpty() ? [] : $data->toArray();
    }
}
