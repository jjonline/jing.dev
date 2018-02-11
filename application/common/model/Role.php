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
     * 角色ID查找角色信息
     * @param $id mixed
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleInfoById($id)
    {
        if(empty($id))
        {
            return [];
        }
        $role = $this->find($id);
        return $role ? $role->toArray() : [];
    }
}
