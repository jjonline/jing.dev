<?php
/**
 * 开发者模式菜单管理服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-19 21:19
 * @file MenuService.php
 */

namespace app\common\service;

use app\common\model\Menu;
use think\Db;
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
    /**
     * @var string 菜单、权限的缓存tag
     */
    public $cache_tag = 'auth';

    public function __construct(Menu $Menu, LogService $logService)
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
    public function save(Request $request)
    {
        $menu = $request->post('Menu/a');
        if (empty($menu['name']) || empty($menu['tag'])) {
            return ['error_code' => 400, 'error_msg' => '菜单名称或菜单标签缺失'];
        }
        // 编辑模式会传原id
        $is_edit = !empty($menu['id']);
        // 检查tag是否重复
        $repeat_menu = $this->Menu->getMenuByTag(trim($menu['tag']));
        if ($is_edit) {
            // 检查拟编辑菜单是否存在
            $exist_menu = $this->Menu->getMenuById($menu['id']);
            if (empty($exist_menu)) {
                return ['error_code' => 400, 'error_msg' => '拟编辑菜单不存在'];
            }
            // 编辑模式检查tag是否重复
            if ($exist_menu['tag'] != trim($menu['tag']) && !empty($repeat_menu)) {
                return ['error_code' => 400, 'error_msg' => '菜单tag已存在'];
            }
        } else {
            // 检查拟新增菜单的tag是否重复
            if (!empty($repeat_menu)) {
                return ['error_code' => 400, 'error_msg' => '菜单tag已存在'];
            }
        }
        // 数据
        $Menu           = [];
        $Menu['sort']   = intval($menu['sort']) < 0 ? 1 : intval($menu['sort']);
        $Menu['url']    = trim($menu['url']);
        $Menu['tag']    = trim($menu['tag']);
        $Menu['icon']   = trim($menu['icon']);
        $Menu['name']   = trim($menu['name']);
        $Menu['remark'] = trim($menu['remark']);

        // 是否必选
        $Menu['is_required'] = 0;
        if (isset($menu['is_required'])) {
            $Menu['is_required'] = 1;
        }
        // 是否badge
        $Menu['is_badge'] = 0;
        if (isset($menu['is_badge'])) {
            $Menu['is_badge'] = 1;
        }
        // 是否系统菜单 不允许删除
        $Menu['is_system'] = 0;
        if (isset($menu['is_system'])) {
            $Menu['is_system'] = 1;
        }
        // 是否控制数据权限
        $Menu['is_permissions'] = 0;
        if (isset($menu['is_permissions'])) {
            $Menu['is_permissions'] = 1;
        }
        /**
         * ++++++++++++++++++
         * 菜单额外数据处理，先收集最后统一赋值
         * $extra_param = [
         *     'columns' => [],
         * ];
         * ++++++++++++++++++
         */
        $extra_param = [];

        // 是否控制字段显示
        $Menu['is_column'] = 0;
        if (isset($menu['is_column'])) {
            $Menu['is_column'] = 1;
            $Columns           = $request->post('Columns/a');
            if (empty($Columns)
                || empty($Columns['columns'])
                || empty($Columns['name'])
                || empty($Columns['sorted'])) {
                return ['error_code' => 400, 'error_msg' => '待选字段列表不完善'];
            }
            $columns = array_unique(array_filter($Columns['columns'])); // 去重
            $name    = array_filter($Columns['name']);
            $sorted  = $Columns['sorted'];
            if (count($sorted) != count($name) || count($sorted) != count($columns)) {
                return ['error_code' => 400, 'error_msg' => '待选字段的信息不完整或存在重复字段'];
            }
            // 处理成json
            $columns_list = [];
            foreach ($sorted as $key => $value) {
                $item            = [];
                $item['columns'] = $columns[$key];
                $item['name']    = $name[$key];
                $item['sorted']  = empty($value) ? 0 : 1;
                $columns_list[]  = $item;
            }
            $extra_param['columns'] = $columns_list;
        }

        // 菜单级别处理
        if (empty($menu['level1']) && empty($menu['level2'])) {
            // 一级菜单
            $Menu['level']     = 1;
            $Menu['parent_id'] = 0;
        } elseif (empty($menu['level2']) && !empty($menu['level1'])) {
            // 二级菜单
            $level1 = $this->Menu->getMenuById($menu['level1']);
            if (empty($level1)) {
                return ['error_code' => -1, 'error_msg' => '一级菜单不存在'];
            }
            $Menu['level']     = 2;
            $Menu['parent_id'] = $level1['id'];
        } elseif (!empty($menu['level2']) && !empty($menu['level1'])) {
            // 三级菜单
            $level2 = $this->Menu->getMenuById($menu['level2']);
            if (empty($level2)) {
                return ['error_code' => -1, 'error_msg' => '二级菜单不存在'];
            }
            $Menu['level']     = 3;
            $Menu['parent_id'] = $level2['id'];
        }

        /**
         * ++++++++++
         * 统一赋值额外参数json字段值
         * ++++++++++
         */
        $Menu['extra_param'] = $extra_param;

        // 新增或编辑区分写入
        if ($is_edit) {
            // update模式
            $ret = $this->Menu->isUpdate(true)->save($Menu, ['id' => $menu['id']]);
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder([$exist_menu,$Menu], '编辑菜单');
        } else {
            // insert模式
            $ret = $this->Menu->data($Menu)->isUpdate(false)->save();
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder($Menu, '新增菜单');
        }
        // 编辑菜单之后清空缓存
        Cache::clear($this->cache_tag);
        return $ret !== false ?
            ['error_code' => 0, 'error_msg' => '保存菜单成功'] :
            ['error_code' => 500, 'error_msg' => '菜单保存失败：系统异常'];
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
        if ($sort <= 0) {
            return ['error_code' => 400, 'error_msg' => '排序数字有误'];
        }
        $menu = $this->Menu->getMenuById($id);
        if (empty($menu)) {
            return ['error_code' => 400, 'error_msg' => '拟编辑排序的菜单数据不存在'];
        }
        $ret = $this->Menu->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
        // 编辑菜单之后清空缓存
        Cache::clear($this->cache_tag);
        return $ret >= 0 ?
            ['error_code' => 0, 'error_msg' => '排序调整成功'] :
            ['error_code' => 500, 'error_msg' => '排序调整失败：系统异常'];
    }

    /**
     * 按层级、排序重新排列菜单数据并生成seed数组
     * ---
     * 1、按层级 + 排序排列所有菜单
     * 2、重新生成递增ID
     * ---
     * @return array
     */
    public function reorganize()
    {
        try {
            Db::startTrans();
            $menus   = $this->Menu->getMenuList();
            $primary = 1; // 重整主键
            $menu1   = [];
            $menu2   = [];
            $menu3   = [];
            foreach ($menus as $key => $value) {
                // 仅处理三级菜单
                $value['old_id'] = $value['id'];
                // 额外参数为空的统一成null值
                if (empty($value['extra_param'])) {
                    $value['extra_param'] = null;
                }
                switch ($value['level']) {
                    case 1:
                        $menu1[] = $value;
                        break;
                    case 2:
                        $menu2[] = $value;
                        break;
                    case 3:
                        $menu3[] = $value;
                        break;
                }
            }
            // 按层级处理菜单数组--仅到3级
            foreach ($menu1 as $key1 => $value1) {
                // 二级菜单
                $_menu2 = [];
                foreach ($menu2 as $key2 => $value2) {
                    // 三级菜单
                    $_menu3 = [];
                    foreach ($menu3 as $key3 => $value3) {
                        if ($value2['id'] == $value3['parent_id']) {
                            $_menu3[] = $value3;
                        }
                    }
                    $value2['children'] = $_menu3;

                    if ($value1['id'] == $value2['parent_id']) {
                        $_menu2[] = $value2;
                    }
                }
            }

            // 按层级 + 排序重新处理主键ID
            $reorganize = [];
            foreach ($menu1 as $value1) {
                $level1_id    = $primary;
                $cache        = $value1;
                $cache['id']  = $primary;
                $reorganize[] = $cache;
                // 处理1级菜单下的2级菜单
                foreach ($menu2 as $value2) {
                    if ($value1['old_id'] == $value2['parent_id']) {
                        $primary++; // 主键自增
                        $level2_id          = $primary;
                        $cache              = $value2;
                        $cache['id']        = $primary;
                        $cache['parent_id'] = $level1_id;
                        $reorganize[]       = $cache;
                        // 处理2级菜单下的3级菜单
                        foreach ($menu3 as $value3) {
                            if ($value2['old_id'] == $value3['parent_id']) {
                                $primary++; // 主键自增
                                $cache              = $value3;
                                $cache['id']        = $primary;
                                $cache['parent_id'] = $level2_id;
                                $reorganize[]       = $cache;
                            }
                        }
                    }
                }
                $primary++; // 主键自增
            }

            // 清理old_id生成seed结构
            foreach ($reorganize as $key => $value) {
                unset($reorganize[$key]['old_id']);
            }

            // seed数组生成php字符串描述格式
            $seeds = "[";
            foreach ($reorganize as $value) {
                $seeds .= "\n    [\n";
                foreach ($value as $key => $val) {
                    if (is_null($val)) {
                        $seeds .= "        '" . $key . "'" . ' => null,';
                    } else {
                        if ($key == 'create_time' || $key == 'update_time') {
                            $seeds .= "        '" . $key . "'" . ' => $date_time,';
                        } else {
                            $seeds .= "        '" . $key . "' => '" . $val . "',";
                        }
                    }
                    $seeds .= "\n";
                }
                $seeds .= "    ],";
            }
            $seeds .= "\n]";

            // 读取模板生成seed使用的php结构数组
            $stub = file_get_contents('../database/stubs/menu_seed.stub');
            $stub = str_replace('#dateTime#', date('Y-m-d H:i:s'), $stub);
            $stub = str_replace('#menu#', $seeds, $stub);
            file_put_contents('../database/stubs/menu_seed.php', $stub);

            // 清空menu表后重新插入重排整理后的菜单记录数据
            $this->Menu->db()->query('truncate table ' . $this->Menu->getTable());
            $this->Menu->db()->insertAll($reorganize);
            Db::commit();
            return ['error_code' => 0, 'error_msg' => '重整菜单列表并生成seed完成'];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
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
        if (empty($menu)) {
            return ['error_code' => 400, 'error_msg' => '拟删除的菜单数据不存在'];
        }
        if ($menu['is_system'] == 1) {
            return ['error_code' => 400, 'error_msg' => '系统核心菜单禁止删除'];
        }
        $ret = $this->Menu->db()->where('id', $id)->delete();
        // 编辑菜单之后清空缓存
        Cache::clear($this->cache_tag);
        // 日志方式备份保存原始菜单信息
        $this->LogService->logRecorder($menu);
        return $ret >= 0 ?
            ['error_code' => 0, 'error_msg' => '菜单删除成功'] :
            ['error_code' => 500, 'error_msg' => '菜单删除失败：系统异常'];
    }
}
