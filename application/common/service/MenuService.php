<?php
/**
 * 开发者模式菜单管理服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-19 21:19
 * @file MenuService.php
 */

namespace app\common\service;


use app\common\model\Menu;
use think\facade\Cache;
use think\Request;

class MenuService
{

    /**
     * @var Menu
     */
    public $Menu;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Menu $Menu,LogService $logService)
    {
        $this->Menu       = $Menu;
        $this->LogService = $logService;
    }

    /**
     * 编辑菜单--自动判断新增或编辑
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(Request $request)
    {
        $menu  = $request->post('Menu/a');
        if(empty($menu['name']) || empty($menu['tag']))
        {
            return ['error_code' => 400,'error_msg' => '菜单名称或菜单标签缺失'];
        }
        // 编辑模式会传原id
        $is_edit     = !empty($menu['id']);
        // 检查tag是否重复
        $repeat_menu = $this->Menu->getMenuByTag(trim($menu['tag']));
        if($is_edit)
        {
            // 检查拟编辑菜单是否存在
            $exist_menu = $this->Menu->getMenuById($menu['id']);
            if(empty($exist_menu))
            {
                return ['error_code' => 400,'error_msg' => '拟编辑菜单不存在'];
            }
            // 编辑模式检查tag是否重复
            if($exist_menu['tag'] != trim($menu['tag']) && !empty($repeat_menu))
            {
                return ['error_code' => 400,'error_msg' => '菜单tag已存在'];
            }
        }else {
            // 检查拟新增菜单的tag是否重复
            if(!empty($repeat_menu))
            {
                return ['error_code' => 400,'error_msg' => '菜单tag已存在'];
            }
        }
        // 数据
        $Menu                 = [];
        $Menu['sort']         = intval($menu['sort']) < 0 ? 1 : intval($menu['sort']);
        $Menu['url']          = trim($menu['url']);
        $Menu['tag']          = trim($menu['tag']);
        $Menu['icon']         = trim($menu['icon']);
        $Menu['name']         = trim($menu['name']);
        $Menu['remark']       = trim($menu['remark']);

        // 是否必选
        $Menu['is_required']  = 0;
        if(isset($menu['is_required']))
        {
            $Menu['is_required'] = 1;
        }
        // 是否badge
        $Menu['is_badge'] = 0;
        if(isset($menu['is_badge']))
        {
            $Menu['is_badge'] = 1;
        }
        // 是否系统菜单 不允许删除
        $Menu['is_system'] = 0;
        if(isset($menu['is_system']))
        {
            $Menu['is_system'] = 1;
        }
        // 可能的菜单额外数据处理
        if(!empty($menu['extra_param']))
        {
            $extra_param = json_decode($menu['extra_param'],true);
            if(empty($extra_param))
            {
                return ['error_code' => 400,'error_msg' => '菜单额外数据json字符串解析失败'];
            }
            $Menu['extra_param'] = $menu['extra_param'];
        }
        // 菜单级别处理
        if(empty($menu['level1']) && empty($menu['level2']))
        {
            // 一级菜单
            $Menu['level']    = 1;
        }elseif(empty($menu['level2']) && !empty($menu['level1'])) {
            // 二级菜单
            $level1 = $this->Menu->getMenuById($menu['level1']);
            if(empty($level1))
            {
                return ['error_code' => -1,'error_msg' => '一级菜单不存在'];
            }
            $Menu['level']     = 2;
            $Menu['parent_id'] = $level1['id'];
        }elseif(!empty($menu['level2']) && !empty($menu['level1'])) {
            // 三级菜单
            $level2 = $this->Menu->getMenuById($menu['level2']);
            if(empty($level2))
            {
                return ['error_code' => -1,'error_msg' => '二级菜单不存在'];
            }
            $Menu['level']     = 3;
            $Menu['parent_id'] = $level2['id'];
        }

        // 新增或编辑区分写入
        if($is_edit)
        {
            // update模式
            $ret = $this->Menu->isUpdate(true)->save($Menu,['id' => $exist_menu['id']]);
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder($exist_menu);
        }else{
            // insert模式
            $ret = $this->Menu->data($Menu)->isUpdate(false)->save();
        }
        // 编辑菜单之后清空缓存
        Cache::clear();
        return $ret !== false ?
               ['error_code' => 0,'error_msg' => '保存菜单成功'] :
               ['error_code' => 500,'error_msg' => '菜单保存失败：系统异常'];
    }

    /** 菜单排序调整
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sort(Request $request)
    {
        $id   = $request->post('id/i');
        $sort = intval($request->post('sort'));
        if($sort <= 0)
        {
            return ['error_code' => 400,'error_msg' => '排序数字有误'];
        }
        $menu = $this->Menu->getMenuById($id);
        if(empty($menu))
        {
            return ['error_code' => 400,'error_msg' => '拟编辑排序的菜单数据不存在'];
        }
        $ret = $this->Menu->isUpdate(true)->save(['sort' => intval($sort)],['id' => $id]);
        return $ret >= 0 ?
               ['error_code' => 0,'error_msg' => '排序调整成功'] :
               ['error_code' => 500,'error_msg' => '排序调整失败：系统异常'];
    }

    /**
     * 删除菜单
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete(Request $request)
    {
        $id   = $request->post('id/i');
        $menu = $this->Menu->getMenuById($id);
        if(empty($menu))
        {
            return ['error_code' => 400,'error_msg' => '拟删除的菜单数据不存在'];
        }
        if($menu['is_system'] == 1)
        {
            return ['error_code' => 400,'error_msg' => '系统核心菜单禁止删除'];
        }
        $ret = $this->Menu->db()->where('id',$id)->delete();
        // 日志方式备份保存原始菜单信息
        $this->LogService->logRecorder($menu);
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '菜单删除成功'] :
            ['error_code' => 500,'error_msg' => '菜单删除失败：系统异常'];
    }

}
