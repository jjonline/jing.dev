<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-11 22:19
 * @file Menu.php
 */

namespace app\common\model;

use app\common\helper\ArrayHelper;
use think\Model;

class Menu extends Model
{
    /**
     * 菜单标签获取菜单
     * @param $name string 菜单标签名称
     * @return array|null|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuByName($name)
    {
        return $this->where(['name' => $name])->find();
    }

    /**
     * 获取所有菜单列表并按层级排序
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuList()
    {
        $data  = $this->order(['sort' => 'ASC','level' => 'ASC'])->select()->toArray();
        $group = ArrayHelper::group($data,'level');
        $menu  = ArrayHelper::sortMultiTree($data,$group[1],'id','parent_id');
        return $menu;
    }
}
