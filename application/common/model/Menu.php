<?php
/**
 * 菜单模型
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-20 18:00:03
 * @file Men.php
 */

namespace app\common\model;

use app\common\helper\ArrayHelper;
use app\common\helper\TreeHelper;
use think\Model;

class Menu extends Model
{
    protected $json = ['extra_param'];

    /**
     * 菜单ID获取菜单详情
     *
     * @param int $id 菜单ID
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuById($id)
    {
        $data = $this->find($id);
        return $data ? $data->toArray() : [];
    }

    /**
     * 通过tag查询菜单
     *
     * @param string $tag 标签名
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuByTag($tag)
    {
        $data = $this->where('tag', $tag)->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 获取所有菜单列表并按层级排序
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuList()
    {
        $data  = $this->order(['sort' => 'ASC', 'level' => 'ASC'])->select()->toArray();
        $group = ArrayHelper::group($data, 'level');
        $menu  = ArrayHelper::sortMultiTree($data, $group[1], 'id', 'parent_id');
        return $menu;
    }

    /**
     * 获取格式化列表输出的菜单
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFormatMenuList()
    {
        $menu = $this->getMenuList();
        $menu = TreeHelper::vTree($menu);
        foreach ($menu as $key => $value) {
            if ($value['level'] > 1) {
                $menu[$key]['name'] = str_repeat(
                    '&nbsp;',
                    floor(pow(($value['level'] - 1), 2.5) * 2)
                ) . '└─' . $menu[$key]['name'];
            }
        }
        return $menu;
    }
}
