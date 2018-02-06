<?php
/**
 * 菜单模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use app\common\helpers\ArrayHelper;
use app\common\helpers\StringHelper;
use think\Model;
use think\model\concern\SoftDelete;

class Menu extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 菜单标签获取菜单
     * @param $name string 菜单标签名称
     * @throws
     */
    public function getMenuByName($name)
    {
        return $this->where(['name' => $name])->find();
    }

    /**
     * 获取所有菜单列表并按层级排序
     * @throws
     * @return []
     */
    public function getMenuList()
    {
        $data  = $this->order(['sort' => 'ASC','level' => 'ASC'])->select()->toArray();
        $group = ArrayHelper::group($data,'level');
        $menu  = ArrayHelper::sortMultiTree($data,$group[1],'name','parent_name');
        return $menu;
    }

    /**
     * 获取格式化列表输出的菜单
     * @throws
     * @return []
     */
    public function getFormatMenuList()
    {
        $menu = $this->getMenuList();
        foreach ($menu as $key => $value)
        {
            $role[$key]['level_text'] = StringHelper::toChineseUpper($value['level']).'级';
            if($value['level'] > 1)
            {
                $menu[$key]['title']   = str_repeat('&nbsp;',floor(pow(($value['level'] - 1),2.5) * 2)).'└─'.$menu[$key]['title'];
            }
        }
        return $menu;
    }


}
