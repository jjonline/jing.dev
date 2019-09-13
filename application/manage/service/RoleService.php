<?php
/**
 * 角色服务
 * ---
 * 仅处理角色新增|编辑|删除等管理功能，具体使用权限上由AuthService实现
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-23 11:45
 * @file RoleService.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\common\model\Menu;
use app\common\model\Role;
use app\common\model\RoleMenu;
use app\common\model\User;
use app\common\service\LogService;
use app\common\service\RoleTrait;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Session;
use think\Request;

class RoleService
{
    use RoleTrait;

    /**
     * @var Role
     */
    public $Role;
    /**
     * @var Menu
     */
    public $Menu;
    /**
     * @var RoleMenu
     */
    public $RoleMenu;
    /**
     * @var User
     */
    public $User;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(
        Role $role,
        User $user,
        Menu $menu,
        RoleMenu $roleMenu,
        LogService $logService
    ) {
        $this->Role        = $role;
        $this->Menu        = $menu;
        $this->RoleMenu    = $roleMenu;
        $this->User        = $user;
        $this->LogService  = $logService;
    }

    /**
     * 保存角色数据||编辑+新增
     * @param Request $request
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(Request $request)
    {
        $data = $request->post();
        if (empty($data['name'])) {
            return ['error_code' => 400,'error_msg' => '角色名称不得为空'];
        }
        $user_id    = Session::get('user_info.id');
        // 是否编辑模式
        $is_edit    = $request->has('id', 'post');
        $exist_role = $this->Role->getRoleInfoByName($data['name']);
        if ($is_edit) {
            // 检查拟编辑角色是否存在
            $repeat_role = $this->Role->getRoleInfoById($request->post('id'));
            if (empty($repeat_role)) {
                return ['error_code' => 400,'error_msg' => '拟编辑角色不存在'];
            }
            if ($repeat_role['name'] != trim($data['name']) && !empty($exist_role)) {
                return ['error_code' => 400,'error_msg' => '角色名称已存在，角色名称不能重复'];
            }

            // 编辑模式检查是否有权限编辑该角色
            $can_deal = $this->isRoleCanDealByUserId($repeat_role['id'], $user_id);
            if (empty($can_deal)) {
                return ['error_code' => 400,'error_msg' => '您无权限编辑该角色'];
            }
        } else {
            if (!empty($exist_role)) {
                return ['error_code' => 400,'error_msg' => '角色名称已存在，角色名称不能重复'];
            }
        }
        // 检查权限菜单是否存在
        $menu_ids = $request->post('ids/a');
        $menus    = $this->Menu->db()->where('id', 'IN', $menu_ids)->select();
        if (count($menus) != count($menu_ids)) {
            return ['error_code' => 400,'error_msg' => '菜单数据已变动请刷新页面后再试'];
        }
        $permissions = $request->post('permissions/a');
        if (count($permissions) != count($menu_ids)) {
            return ['error_code' => 400,'error_msg' => '菜单权限数据有误'];
        }

        // 字段控制，解析保存的角色的字段信息
        $_show_columns = $request->post('show_columns/a', []);

        // 构造提交过来的角色菜单及权限
        $role_menu = [];
        foreach ($menu_ids as $key => $menu_id) {
            $_role_menu                = [];
            $_role_menu['menu_id']     = $menu_id;
            $_role_menu['permissions'] = $permissions[$key];

            // 解析可能的待选字段
            foreach ($_show_columns as $column) {
                // $column格式 22.customer.real_name
                $parse_result = explode('.', $column, 2);
                if (count($parse_result) == 2 && is_numeric($parse_result[0])) {
                    if ($parse_result[0] == $menu_id) {
                        $_role_menu['show_columns'][] = $parse_result[1]; // customer.real_name
                    }
                }
            }
            $role_menu[] = $_role_menu;
        }

        /**
         * 检查提交过来的角色菜单数据权限是否超限：解决人为构造角色菜单和角色菜单权限范围的情况
         */
        $user_role_menu = $this->getRoleMenuListByUserId($user_id);
        $checked_menu   = $role_menu;
        foreach ($role_menu as $key => $value) {
            foreach ($user_role_menu as $_key => $_value) {
                if ($value['menu_id'] == $_value['id']) {
                    unset($checked_menu[$key]);
                    $permissions = $this->comparePermissions($_value['permissions'], $value['permissions']);
                    if (!$permissions) {
                        return ['error_code' => 400, 'error_msg' => '菜单权限级别非法'];
                    }
                }
            }
        }
        // 如果被unset后列表不为空，则是构造添加了额外的没有权限的菜单的情况
        if (!empty($checked_menu)) {
            return ['error_code' => 400, 'error_msg' => '拟分配的菜单不存在或您没有分配该菜单的权限'];
        }

        // 处理待选字段
        foreach ($role_menu as $key => $_menu) {
            $deal_show_columns = [];
            // 有指定字段，将字段名称替换为对应数组元素
            if (!empty($_menu['show_columns'])) {
                foreach ($user_role_menu as $menu) {
                    if ($menu['id'] == $_menu['menu_id']) {
                        foreach ($_menu['show_columns'] as $column) {
                            foreach ($menu['show_columns'] as $column_arr) {
                                if ($column_arr['columns'] == $column) {
                                    $deal_show_columns[] = $column_arr;
                                }
                            }
                        }
                    }
                }
            }
            $role_menu[$key]['show_columns'] = $deal_show_columns;
        }

        // 角色数据
        $role           = [];
        $role['name']   = trim($data['name']);
        $role['sort']   = intval($data['sort']) >= 0 ? intval($data['sort']) : 0;
        $role['remark'] = !empty($data['remark']) ? trim($data['remark']) : '';

        // 事务开始写入角色数据
        Db::startTrans();
        try {
            if ($is_edit) {
                $role_id = $repeat_role['id'];
                // 更新角色
                Db::name('role')->where('id', $role_id)->update($role);
                // 编辑模式 删除原先角色的菜单数据
                Db::name('role_menu')->where('role_id', $role_id)->delete();
            } else {
                $role_id = Db::name('role')->insertGetId($role);
            }
            // insert新增角色的菜单权限
            foreach ($role_menu as $key => $value) {
                $role_menu[$key]['role_id'] = $role_id;
            }
            Db::name('role_menu')->insertAll($role_menu);
            $this->LogService->logRecorder([$role,$role_menu], $is_edit ? '编辑角色' : '新增角色');
            // 编辑角色之后按tag清空所有角色缓存数据
            Cache::clear(Role::ROLE_CACHE_TAG);
            Db::commit();
            return ['error_code' => 0,'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['error_code' => 400,'error_msg' => '保存失败：'.$e->getMessage()];
        }
    }


    /**
     * 角色排序
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
            return ['error_code' => 400,'error_msg' => '排序数字有误'];
        }
        $role = $this->Role->getRoleInfoById($id);
        if (empty($role)) {
            return ['error_code' => 400,'error_msg' => '拟编辑排序的角色数据不存在'];
        }
        $ret = $this->Role->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
        // 编辑角色之后清空缓存
        // Cache::clear(Role::ROLE_CACHE_TAG); // 修改排序并不影响角色菜单数据，不予清理缓存
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '排序调整成功'] :
            ['error_code' => 500,'error_msg' => '排序调整失败：系统异常'];
    }

    /**
     * 删除菜单
     * @param Request $request
     * @param array $act_user_info 操作者用户信息
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete(Request $request, $act_user_info)
    {
        $id        = $request->post('id/i');
        $role      = $this->Role->getRoleInfoById($id);
        $role_menu = $this->RoleMenu->getRoleMenuListByRoleId($id);
        if ($id == 1) {
            return ['error_code' => 400,'error_msg' => '开发者角色不允许删除'];
        }
        if (empty($role)) {
            return ['error_code' => 400,'error_msg' => '拟删除的角色数据不存在'];
        }

        // 检查权限范围是否允许删除
        $can_deal = $this->isRoleCanDealByUserId($id, $act_user_info['id']);
        if (empty($can_deal)) {
            return ['error_code' => 400,'error_msg' => '您无权限删除该角色'];
        }

        // 检查有木有用户已使用该角色
        $role_user = $this->User->where('role_id', $id)->select();
        if (!$role_user->isEmpty()) {
            return ['error_code' => 400,'error_msg' => '拟删除的角色已分配用户，请先调整用户所属角色'];
        }
        // 事务进行角色数据删除
        Db::startTrans();
        try {
            Db::name('role')->where('id', $id)->delete();
            Db::name('role_menu')->where('role_id', $id)->delete();
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder(array_merge($role, $role_menu));
            // 编辑角色之后清空缓存
            Cache::clear(Role::ROLE_CACHE_TAG);
            Db::commit();
            return ['error_code' => 0,'error_msg' => '角色删除成功'];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['error_code' => 0,'error_msg' => '角色删除失败：'.$e->getMessage()];
        }
    }

    /**
     * 通过用户ID获取该用户所属角色菜单zTree源数据，内部自动处理根用户
     * @param $user_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuTreeDataByUserId($user_id)
    {
        $menu = $this->getRoleMenuListByUserId($user_id);
        return $this->dealRoleMenuListToZTree($menu);
    }

    /**
     * 通过角色ID获取角色菜单zTree源数据
     * @param null|int $role_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuTreeDataByRoleId($role_id)
    {
        $menu = $this->getMenuListByRoleId($role_id);
        return $this->dealRoleMenuListToZTree($menu);
    }

    /**
     * 获取角色id指定的处理好的菜单权限列表数据
     * ---
     * 按角色ID原本读取已设置的该角色的菜单权限数据
     * ---
     * @param int $role_id
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuListByRoleId($role_id)
    {
        // 开发模式从缓存中读取
        $role_cache_key = Role::ROLE_CACHE_PREFIX.$role_id;
        if (!Config::get('app.app_debug')) {
            $role_menu = Cache::get($role_cache_key);
            if (!empty($role_menu)) {
                return $role_menu;
            }
        }

        $role_menu = $this->getMenuListByRoleId($role_id);

        // 依据是否开发模式将结果集缓存
        if (!Config::get('app.app_debug')) {
            Cache::tag(Role::ROLE_CACHE_TAG)->set($role_cache_key, $role_menu, 3600 * 720);
        }

        return $role_menu;
    }

    /**
     * 通过用户ID读取该用户完整的角色菜单信息
     * ----
     * 1、用户不是根用户则按角色原本读取
     * 2、用户是根用户则读取全部并处理成统一格式数据
     * ----
     * @param integer $user_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuListByUserId($user_id)
    {
        // 依据开发模式自动选择是否启用户菜单缓存
        $user_menu_cache_key = Role::USER_ROLE_CACHE_PREFIX.$user_id;
        if (!Config::get('app.app_debug')) {
            $user_menu = Cache::get($user_menu_cache_key);
            if (!empty($user_menu)) {
                return $user_menu;
            }
        }

        // 没有缓存或开发模式，智能读取菜单数据并处理
        $is_root   = $this->isRootUser($user_id); // 是否根权限用户
        $user_menu = $is_root
            ? $this->getRootUserMenuListByUserId($user_id)
            : $this->getNormalUserMenuListByUserId($user_id);

        // 依据是否开发模式将结果集缓存
        if (!Config::get('app.app_debug')) {
            Cache::tag(Role::ROLE_CACHE_TAG)->set($user_menu_cache_key, $user_menu, 3600 * 720);
        }

        return $user_menu;
    }

    /**
     * 检查编辑角色的管理员的角色是否有权限编辑|处理该角色数据
     * ---
     * 1、检查当前编辑人所属角色权限是否完整包含了被编辑角色的权限范围
     * 2、每一个菜单的权限级别也是包含关系
     * 3、如果存在自定义字段，自定义字段也是当前编辑中所属角色的字段范围要包含被编辑角色字段范围
     * ---
     * @param integer $edit_role_id 被编辑的角色ID
     * @param integer $editor_id    当前编辑人用户ID
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isRoleCanDealByUserId($edit_role_id, $editor_id)
    {
        $edit_role_menu_list   = $this->getRoleMenuListByRoleId($edit_role_id);
        $editor_role_menu_list = $this->getRoleMenuListByUserId($editor_id);

        // 清除法循环对比每一个菜单和菜单权限范围及字段范围
        $check_data = $edit_role_menu_list;
        foreach ($edit_role_menu_list as $key => $value) {
            foreach ($editor_role_menu_list as $_key => $_value) {
                if ($value['id'] == $_value['id']) {
                    unset($check_data[$key]);

                    // 检查权限，不符直接返回false不再执行
                    $permissions = $this->comparePermissions($_value['permissions'], $value['permissions']);
                    if (!$permissions) {
                        return false;
                    }

                    // 检查有字段控制的菜单字段包含情况，不通过直接返回false不再执行
                    if (!empty($_value['is_column'])) {
                        $compare_column = $this->compareColumns($_value['show_columns'], $value['show_columns']);
                        if (!$compare_column) {
                            return false;
                        }
                    }
                }
            }
        }
        if (empty($check_data)) {
            return true;
        }
        return false;
    }

    /**
     * 处理角色菜单数据成zTree数据源
     * @param array $menu
     * @return array
     * @throws Exception
     */
    private function dealRoleMenuListToZTree($menu)
    {
        // 处理成3级数据
        $menu1        = [];
        $menu2        = [];
        $menu3        = [];
        $show_columns = [];// 需要收集用于角色编辑自定义可操作字段的数据
        foreach ($menu as $key => $value) {
            // 超级管理员补充菜单权限标记
            if (!isset($menu[$key]['permissions'])) {
                $value['permissions'] = 'super';
            }
            // 处理zTree所需的各种属性
            $value['_url'] = $value['url'];
            unset($value['url']);
            $value['open'] = true;
            $value['node'] = $value['id'];
            // 必选-不可取消
            if ($value['is_required']) {
                $value['checked']     = true;
                $value['chkDisabled'] = true;
            }

            // 收集处理可操作字段的指定功能
            if (!empty($value['is_column'])) {
                $_menu            = [];
                $_menu['columns'] = $value['show_columns'];
                $_menu['remark']  = $value['remark'];
                $_menu['url']     = $value['_url'];
                $_menu['menu_id'] = $value['id']; // 菜单表的ID，role_menu表ID无意义
                $_menu['name']    = $value['name']; // 菜单名称
                $show_columns[]   = $_menu;
            }

            // 仅处理三级菜单
            switch ($value['level']) {
                case 1:
                    $menu1[] = $value;
                    break;
                case 2:
                    /**
                     * 将二级菜单虚拟一个到三级中，启用联动效应
                     */
                    if ($value['is_required'] == 0) {
                        $v_value3              = $value;
                        $value['id']           = $value['id'].'_v';
                        $value['name']         = $value['name'].'*';
                        $value['remark']       = '';
                        $menu2[]               = $value;
                        $v_value3['parent_id'] = $value['id'];
                        // 如果有数据权限范围
                        if ($value['is_permissions'] == 1) {
                            $v_value3['children']  = $this->dealPermissionTreeData(
                                $value['permissions'],
                                $v_value3['id']
                            );
                        }
                        $menu3[] = $v_value3;
                    } else {
                        $menu2[] = $value;
                    }
                    break;
                case 3:
                    if ($value['is_required'] == 0) {
                        if ($value['is_permissions'] == 1) {
                            $value['children'] = $this->dealPermissionTreeData(
                                $value['permissions'],
                                $value['id']
                            );
                        }
                    }
                    $menu3[] = $value;
                    break;
            }
        }
        // 按层级处理菜单数组--仅到3级
        $tree  = [];
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
            $tree[$key1]             = $value1;
            $tree[$key1]['children'] = $_menu2;
        }
        return [$tree, $show_columns];
    }

    /**
     * 处理数据权限级别radio特定数组
     * @param $permission
     * @param $parent_id
     * @return mixed
     * @throws Exception
     */
    private function dealPermissionTreeData($permission, $parent_id)
    {
        $per = [
            'super'  => [
                [
                    'id'    => 'permissions_super_'.$parent_id,
                    'tag'   => 'super'.$parent_id,
                    'value' => 'super',
                    'name'  => '全部数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_super_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_leader_'.$parent_id,
                    'tag'   => 'leader'.$parent_id,
                    'value' => 'leader',
                    'name'  => '部门和个人数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_leader_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_staff_'.$parent_id,
                    'tag'   => 'staff'.$parent_id,
                    'value' => 'staff',
                    'name'  => '仅个人数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_staff_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_guest_'.$parent_id,
                    'tag'   => 'guest'.$parent_id,
                    'value' => 'guest',
                    'name'  => '无数据权限',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_guest_'.$parent_id,
                ],
            ],
            'leader' => [
                [
                    'id'          => 'permissions_super_' . $parent_id,
                    'tag'         => 'super' . $parent_id,
                    'value'       => 'super',
                    'name'        => '全部数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_super_' . $parent_id,
                ],
                [
                    'id'    => 'permissions_leader_'.$parent_id,
                    'tag'   => 'leader'.$parent_id,
                    'value' => 'leader',
                    'name'  => '部门和个人数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_leader_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_staff_'.$parent_id,
                    'tag'   => 'staff'.$parent_id,
                    'value' => 'staff',
                    'name'  => '仅个人数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_staff_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_guest_'.$parent_id,
                    'tag'   => 'guest'.$parent_id,
                    'value' => 'guest',
                    'name'  => '无数据权限',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_guest_'.$parent_id,
                ],
            ],
            'staff'  => [
                [
                    'id'          => 'permissions_super_' . $parent_id,
                    'tag'         => 'super' . $parent_id,
                    'value'       => 'super',
                    'name'        => '全部数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_super_' . $parent_id,
                ],
                [
                    'id'          => 'permissions_leader_' . $parent_id,
                    'tag'         => 'leader' . $parent_id,
                    'value'       => 'leader',
                    'name'        => '部门和个人数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_leader_' . $parent_id,
                ],
                [
                    'id'    => 'permissions_staff_'.$parent_id,
                    'tag'   => 'staff'.$parent_id,
                    'value' => 'staff',
                    'name'  => '仅个人数据',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_staff_'.$parent_id,
                ],
                [
                    'id'    => 'permissions_guest_'.$parent_id,
                    'tag'   => 'guest'.$parent_id,
                    'value' => 'guest',
                    'name'  => '无数据权限',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_guest_'.$parent_id,
                ],
            ],
            'guest'  => [
                [
                    'id'          => 'permissions_super_' . $parent_id,
                    'tag'         => 'super' . $parent_id,
                    'value'       => 'super',
                    'name'        => '全部数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_super_' . $parent_id,
                ],
                [
                    'id'          => 'permissions_leader_' . $parent_id,
                    'tag'         => 'leader' . $parent_id,
                    'value'       => 'leader',
                    'name'        => '部门数据和个人数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_leader_' . $parent_id,
                ],
                [
                    'id'          => 'permissions_staff_' . $parent_id,
                    'tag'         => 'staff' . $parent_id,
                    'value'       => 'staff',
                    'name'        => '仅个人数据',
                    'level'       => 4,
                    'open'        => true,
                    'chkDisabled' => true,
                    'node'        => 'permissions_staff_' . $parent_id,
                ],
                [
                    'id'    => 'permissions_guest_'.$parent_id,
                    'tag'   => 'guest'.$parent_id,
                    'value' => 'guest',
                    'name'  => '无数据权限',
                    'level' => 4,
                    'open'  => true,
                    'node'  => 'permissions_guest_'.$parent_id,
                ],
            ],
        ];
        if (empty($per[$permission])) {
            throw new Exception('权限级别数据致命错误');
        }
        return $per[$permission];
    }

    /**
     * 菜单权限比较
     * @param string $per1 预期大的权限
     * @param string $per2 预期小的权限
     * @return bool
     */
    private function comparePermissions($per1, $per2)
    {
        $per = [
            'super'  => ['super','leader','staff','guest'],
            'leader' => ['leader','staff','guest'],
            'staff'  => ['staff','guest'],
            'guest'  => ['guest'],
        ];
        if (empty($per[$per1])) {
            return false;
        }
        return in_array($per2, $per[$per1]);
    }

    /**
     * 字段范围检查比较
     * @param array $columns1 预期大的字段数组
     * @param array $columns2 预期小的字段数组
     * @return bool
     */
    private function compareColumns($columns1, $columns2)
    {
        // 两者都为空 通过
        if (empty($columns1) && empty($column2)) {
            return true;
        }
        // 预期小的为空 通过
        if (!empty($columns1) && empty($column2)) {
            return true;
        }
        // 预期小的不为空 不通过
        if (empty($columns1) && !empty($column2)) {
            return false;
        }

        // 两者都不为空 逐一比较
        $check_columns = $columns2;
        foreach ($columns2 as $key2 => $column2) {
            foreach ($columns1 as $key1 => $column1) {
                if ($column1['name'] == $columns2['name']) {
                    unset($check_columns[$key2]);
                }
            }
        }

        // 如果$check_columns被unset完了 说明$columns1是完整包含$columns2的
        if (empty($check_columns)) {
            return true;
        }
        return false;
    }

    /**
     * 检查并获取根用户处理好的菜单权限列表
     * @param int $user_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getRootUserMenuListByUserId($user_id)
    {
        // 检查是否根用户，不是的话抛出异常
        if (!$this->isRootUser($user_id)) {
            throw new Exception('非根用户，不允许获取根菜单权限');
        }
        /**
         * 根用户，从菜单表获取所有菜单并拼接所有菜单权限
         * ---
         * 1、读取所有菜单数据
         * 2、赋予所有菜单super权限
         * 3、智能转换待选字段为数组
         * 4、赋予所有待选字段为可显示字段
         * ---
         */
        $menu = $this->Menu->getMenuList();
        foreach ($menu as $key => $value) {
            $value['all_columns']       = ArrayHelper::toArray($value['all_columns']); // 待选字段信息
            $menu[$key]['permissions']  = 'super';
            $menu[$key]['all_columns']  = $value['all_columns'];
            $menu[$key]['show_columns'] = $value['all_columns'];
        }
        return $menu;
    }

    /**
     * 读取非根用户处理好的菜单权限列表
     * @param int $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getNormalUserMenuListByUserId($user_id)
    {
        $user_menu = $this->RoleMenu->getRoleMenuListByUserId($user_id);
        /**
         * 从角色读取角色的菜单权限列表并处理数据
         * ---
         * 1、从角色所属菜单读取出角色所拥有的菜单列表
         * 2、智能转换所有待选字段为数组
         * 3、智能转所有可显示字段为数组
         */
        foreach ($user_menu as $key => $value) {
            $user_menu[$key]['all_columns']  = ArrayHelper::toArray($value['all_columns']); // 智能转换为数组
            $user_menu[$key]['show_columns'] = ArrayHelper::toArray($value['show_columns']);// 智能转换为数组
        }
        return $user_menu;
    }

    /**
     * 角色ID读取该角色的处理好的菜单权限列表数据
     * @param integer $role_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getMenuListByRoleId($role_id)
    {
        $role_menu = $this->RoleMenu->getRoleMenuListByRoleId($role_id);
        /**
         * 从角色读取角色的菜单权限列表并处理数据
         * ---
         * 1、从角色所属菜单读取出角色所拥有的菜单列表
         * 2、智能转换所有待选字段为数组
         * 3、智能转所有可显示字段为数组
         */
        foreach ($role_menu as $key => $value) {
            $role_menu[$key]['all_columns']  = ArrayHelper::toArray($value['all_columns']); // 智能转换为数组
            $role_menu[$key]['show_columns'] = ArrayHelper::toArray($value['show_columns']);// 智能转换为数组
        }
        return $role_menu;
    }

    /**
     * 当前登录用户是否为根用户
     * ---
     * 如果未传user_id参数则直接读取当前登录用户的session返回
     * 如果传了且不是当前登录用户则从数据库读取
     * ---
     * @param int $user_id
     * @return bool
     */
    private function isRootUser($user_id = null)
    {
        $_user_id = Session::get('user_info.id');
        if (empty($user_id) || $_user_id == $user_id) {
            return !!Session::get('user_info.is_root');
        }
        return $this->User->isRootUser($user_id);
    }
}
