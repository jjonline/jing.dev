<?php
/**
 * 角色模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:18
 * @file Role.php
 */

namespace app\common\model;

use think\Model;

class Role extends Model
{
    /**
     * @var string 用户级别的角色菜单权限缓存前缀
     */
    const USER_ROLE_CACHE_PREFIX = 'Role.User.Menu_';
    /**
     * @var string 角色的菜单tag和url作为键名的Map缓存欠嘴
     */
    const ROLE_CACHE_MAP_PREFIX = 'Role.Menu.Map_';
    /**
     * @var string 角色级别的角色菜单权限缓存前缀
     */
    const ROLE_CACHE_PREFIX = 'Role.Menu.Cache_';
    /**
     * @var string 角色菜单权限缓存标签tag
     */
    const ROLE_CACHE_TAG = 'RoleAuthTag';

    /**
     * 角色ID查找角色信息
     * @param $id mixed
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleInfoById($id)
    {
        if (empty($id)) {
            return [];
        }
        $role = $this->find($id);
        return $role ? $role->toArray() : [];
    }

    /**
     * 通过角色名称查找角色数据
     * @param $name
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleInfoByName($name)
    {
        if (empty($name)) {
            return [];
        }
        $role = $this->where('name', trim($name))->find();
        return $role ? $role->toArray() : [];
    }

    /**
     * 查询出所有角色数据
     * ---
     * 角色数据有限，无需考虑分页等情况
     * ---
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleList()
    {
        $data = $this->order(['sort' => 'ASC','create_time' => 'DESC'])->select();
        return $data ? $data->toArray() : [];
    }
}
