<?php
/**
 * 角色模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\manage\model;

use app\common\helpers\ArrayHelper;
use app\common\helpers\StringHelper;
use think\Model;
use think\model\concern\SoftDelete;

class Role extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 获取所有角色数据
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleList()
    {
        return $this->order(['sort' => 'ASC','create_time' => 'DESC'])->select()->toArray();
    }

    /**
     * 角色名称查找角色
     * @param string $name 角色名称
     * @throws
     * @return []
     */
    public function getRoleByName($name)
    {
        return $this->where(['name' => $name])->find();
    }

    /**
     * 获取格式化列表输出的菜单|暂未使用多级角色功能
     * @throws
     * @return []
     */
    public function getFormatRoleList()
    {
        $data  = $this->getRoleList();
        $group = ArrayHelper::group($data,'level');
        $role  = ArrayHelper::sortMultiTree($data,$group[1],'name','parent_name');
        foreach ($role as $key => $value)
        {
            $role[$key]['level_text'] = StringHelper::toChineseUpper($value['level']).'级';
            if($value['level'] > 1)
            {
                $role[$key]['title']   = str_repeat('&nbsp;',floor(pow(($value['level'] - 1),2.5) * 2)).'└─'.$role[$key]['title'];
            }
        }
        return $role;
    }

}
