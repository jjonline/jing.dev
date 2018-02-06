<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-11 14:26
 * @file MenuService.php
 */

namespace app\manage\service;


use app\manage\model\Menu;
use think\facade\Cache;
use think\Request;

class MenuService
{
    /**
     * @var Menu
     */
    public $Menu;

    public function __construct(Menu $Menu)
    {
        $this->Menu = $Menu;
    }

    /**
     * 新增|编辑 菜单
     * @param Request $request
     * @param bool $isAdd 新增模式与否的标记
     * @return []
     */
    public function saveMenu(Request $request,$isAdd = false)
    {
        $menu  = $request->post('Menu/a');
        if(empty($menu['name']) || empty($menu['title']))
        {
            return ['error_code' => -1,'error_msg' => '菜单名称或菜单标签缺失'];
        }
        // 编辑模式会传原name，post字段名为origin_name
        $name  = isset($menu['origin_name']) ? trim($menu['origin_name']) : trim($menu['name']);
        $exist = $this->Menu->getMenuByName($name);
        if($isAdd && $exist)
        {
            return ['error_code' => -1,'error_msg' => '菜单标签已存在'];
        }
        if(!$isAdd && !$exist)
        {
            return ['error_code' => -1,'error_msg' => '拟修改的菜单标签不存在'];
        }
        // 数据
        $Menu                 = [];
        $Menu['sort']         = intval($menu['sort']) < 0 ? 1 : intval($menu['sort']);
        $Menu['url']          = trim($menu['url']);
        $Menu['title']        = trim($menu['title']);
        $Menu['fontawesome']  = trim($menu['fontawesome']);
        $Menu['name']         = trim($menu['name']);
        $Menu['remark']       = trim($menu['remark']);
        if(empty($menu['level1']) && empty($menu['level2']))
        {
            // 一级菜单
            $Menu['level']    = 1;
        }elseif(empty($menu['level2']) && !empty($menu['level1'])) {
            // 二级菜单
            $level1 = $this->Menu->getMenuByName($menu['level1']);
            if(empty($level1))
            {
                return ['error_code' => -1,'error_msg' => '一级菜单不存在'];
            }
            $Menu['level']       = 2;
            $Menu['parent_name'] = $level1['name'];
        }elseif(!empty($menu['level2']) && !empty($menu['level1'])) {
            // 三级菜单
            $level2 = $this->Menu->getMenuByName($menu['level2']);
            if(empty($level2))
            {
                return ['error_code' => -1,'error_msg' => '二级菜单不存在'];
            }
            $Menu['level']       = 3;
            $Menu['parent_name'] = $level2['name'];
        }
        if($isAdd)
        {
            // insert模式
            $ret = $this->Menu->data($Menu)->isUpdate(false)->save();
        }else{
            // update模式
            $ret = $this->Menu->isUpdate(true)->save($Menu,['name' => $exist['name']]);
        }
        // 编辑菜单之后清空缓存
        Cache::clear();
        return $ret !== false ? ['error_code' => 0,'error_msg' => '保存菜单成功'] : ['error_code' => -1,'error_msg' => '系统异常'];
    }
}
