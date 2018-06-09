<?php
/**
 * 角色服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-23 11:45
 * @file RoleService.php
 */

namespace app\common\service;

use app\common\model\Menu;
use app\common\model\Role;
use app\common\model\RoleMenu;
use app\common\model\User;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Session;
use think\Request;

class RoleService
{
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
    /**
     * @var string 菜单、权限的缓存tag
     */
    public $cache_tag = 'auth';

    public function __construct(Role $role ,
                                User $user ,
                                Menu $menu ,
                                RoleMenu $roleMenu ,
                                LogService $logService)
    {
        $this->Role       = $role;
        $this->Menu       = $menu;
        $this->RoleMenu   = $roleMenu;
        $this->User       = $user;
        $this->LogService = $logService;
    }

    /**
     * 获取角色权限下的分层级菜单数据
     * @param null|int $role_id 角色ID，未传则取当前登录用户的角色ID
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuList($role_id = null)
    {
        // 未传角色ID则读取当前登录用户的role_id
        $user_info = Session::get('user_info');
        if(empty($role_id) && empty($user_info))
        {
            throw new Exception('用户未登录且未传参数role_id');
        }
        if(!empty($user_info) && $user_info['id'] == 1 && empty($role_id))
        {
            // 超级管理员所有菜单权限
            $menu = $this->Menu->getMenuList();
        }else{
            // 普通管理员按角色权限分配进行读取
            if(empty($role_id))
            {
                $role_id = $user_info['role_id'];
            }
            $menu = $this->RoleMenu->getRoleMenuListByRoleId($role_id);
        }
        // 处理成3级数据
        $menu1 = [];
        $menu2 = [];
        $menu3 = [];
        foreach ($menu as $key => $value) {
            // 超级管理员补充菜单权限标记
            if(!isset($menu[$key]['permissions']))
            {
                $value['permissions'] = 'super';
            }
            // 仅处理三级菜单
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
        $_menu = [];
        foreach ($menu1 as $key1 => $value1)
        {
            // 二级菜单
            $_menu2 = [];
            foreach ($menu2 as $key2 => $value2)
            {
                // 三级菜单
                $_menu3 = [];
                foreach ($menu3 as $key3 => $value3)
                {
                    if($value2['id'] == $value3['parent_id'])
                    {
                        $_menu3[] = $value3;
                    }
                }
                $value2['children'] = $_menu3;

                if($value1['id'] == $value2['parent_id'])
                {
                    $_menu2[] = $value2;
                }
            }
            $_menu[$key1]             = $value1;
            $_menu[$key1]['children'] = $_menu2;
        }
        return $_menu;
    }

    /**
     * 通过角色ID（角色与用户一对多）获取角色菜单zTree所使用的源数据
     * @param null|int $role_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleMenuTreeDataByRoleId($role_id = null)
    {
        // 未传角色ID则读取当前登录用户的role_id
        $user_info = Session::get('user_info');
        if(empty($role_id) && empty($user_info))
        {
            throw new Exception('用户未登录且未传参数role_id');
        }
        if(!empty($user_info) && $user_info['id'] == 1 && empty($role_id))
        {
            // 超级管理员所有菜单权限
            $menu = $this->Menu->getMenuList();
        }else{
            // 普通管理员按角色权限分配进行读取
            if(empty($role_id))
            {
                $role_id = $user_info['role_id'];
            }
            $menu = $this->RoleMenu->getRoleMenuListByRoleId($role_id);
        }
        // 处理成3级数据
        $menu1 = [];
        $menu2 = [];
        $menu3 = [];
        foreach ($menu as $key => $value) {
            // 超级管理员补充菜单权限标记
            if(!isset($menu[$key]['permissions']))
            {
                $value['permissions'] = 'super';
            }
            // 处理zTree所需的各种属性
            $value['_url'] = $value['url'];
            unset($value['url']);
            $value['open'] = true;
            $value['node'] = $value['id'];
            // 必选-不可取消
            if($value['is_required'])
            {
                $value['checked']     = true;
                $value['chkDisabled'] = true;
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
                    if($value['is_required'] == 0)
                    {
                        $v_value3              = $value;
                        $value['id']           = $value['id'].'_v';
                        $value['name']         = $value['name'].'*';
                        $menu2[]               = $value;
                        $v_value3['parent_id'] = $value['id'];
                        $v_value3['children']  = $this->getPermissionTreeData($value['permissions'],$v_value3['id']);
                        $menu3[]               = $v_value3;
                    }else {
                        $menu2[]               = $value;
                    }
                    break;
                case 3:
                    if($value['is_required'] == 0) {
                        $value['children'] = $this->getPermissionTreeData($value['permissions'], $value['id']);
                    }
                    $menu3[]            = $value;
                    break;
            }
        }
        // 按层级处理菜单数组--仅到3级
        $tree  = [];
        foreach ($menu1 as $key1 => $value1)
        {
            // 二级菜单
            $_menu2 = [];
            foreach ($menu2 as $key2 => $value2)
            {
                // 三级菜单
                $_menu3 = [];
                foreach ($menu3 as $key3 => $value3)
                {
                    if($value2['id'] == $value3['parent_id'])
                    {
                        $_menu3[] = $value3;
                    }
                }
                $value2['children'] = $_menu3;

                if($value1['id'] == $value2['parent_id'])
                {
                    $_menu2[] = $value2;
                }
            }
            $tree[$key1]             = $value1;
            $tree[$key1]['children'] = $_menu2;
        }
        return $tree;
    }

    /**
     * 获取数据权限级别radio
     * @param $permission
     * @param $parent_id
     * @return mixed
     * @throws Exception
     */
    protected function getPermissionTreeData($permission,$parent_id)
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
        if(empty($per[$permission]))
        {
            throw new Exception('权限级别数据致命错误');
        }
        return $per[$permission];
    }

    /**
     * 检查编辑角色的管理员的角色是否有权限编辑该角色数据
     * ---
     * 1、菜单列表包含关系
     * 2、每一个菜单的权限级别也是包含关系
     * ---
     * @param $edit_role_id
     * @param $editor_role_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkRoleEditorAuth($edit_role_id,$editor_role_id)
    {
        $edit_role_menu_list   = $this->RoleMenu->getRoleMenuListByRoleId($edit_role_id);
        $editor_role_menu_list = $this->RoleMenu->getRoleMenuListByRoleId($editor_role_id);
        // 循环对比和检查
        $check_data = $edit_role_menu_list;
        foreach ($edit_role_menu_list as $key => $value)
        {
            foreach ($editor_role_menu_list as $_key => $_value)
            {
                if($value['id'] == $_value['id'])
                {
                    unset($check_data[$key]);
                    // 检查权限，不符直接返回false不再执行
                    $permissions = $this->comparePermissions($_value['permissions'],$value['permissions']);
                    if(!$permissions)
                    {
                        return false;
                    }
                }
            }
        }
        if(empty($check_data))
        {
            return true;
        }
        return false;
    }

    /**
     * 菜单权限比较
     * @param string $per1 预期大的权限
     * @param string $per2 预期小的权限
     * @return bool
     */
    protected function comparePermissions($per1,$per2)
    {
        $per = [
            'super'  => ['super','leader','staff','guest'],
            'leader' => ['leader','staff','guest'],
            'staff'  => ['staff','guest'],
            'guest'  => ['guest'],
        ];
        if(empty($per[$per1]))
        {
            return false;
        }
        return in_array($per2,$per[$per1]);
    }

    /**
     * 保存角色数据||编辑+新增
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(Request $request)
    {
        $data = $request->post();
        if(empty($data['name']))
        {
            return ['error_code' => 400,'error_msg' => '角色名称不得为空'];
        }
        // 是否编辑模式
        $is_edit    = $request->has('id','post');
        $exist_role = $this->Role->getRoleInfoByName($data['name']);
        if($is_edit)
        {
            // 检查拟编辑角色是否存在
            $repeat_role = $this->Role->getRoleInfoById($request->post('id'));
            if(empty($repeat_role))
            {
                return ['error_code' => 400,'error_msg' => '拟编辑角色不存在'];
            }
            if($repeat_role['name'] != trim($data['name']) && !empty($exist_role))
            {
                return ['error_code' => 400,'error_msg' => '角色名称已存在，角色名称不能重复'];
            }
        }else {
            if(!empty($exist_role))
            {
                return ['error_code' => 400,'error_msg' => '角色名称已存在，角色名称不能重复'];
            }
        }
        // 检查权限菜单是否存在
        $menu_ids = $request->post('ids/a');
        $menus    = $this->Menu->db()->where('id','IN',$menu_ids)->select();
        if(count($menus) != count($menu_ids))
        {
            return ['error_code' => 400,'error_msg' => '菜单数据已变动请刷新页面后再试'];
        }
        $permissions = $request->post('permissions/a');
        if(count($permissions) != count($menu_ids))
        {
            return ['error_code' => 400,'error_msg' => '菜单权限数据有误'];
        }
        // 角色菜单及权限
        $role_menu = [];
        foreach ($menu_ids as $key => $menu_id) {
            $_role_menu                = [];
            $_role_menu['menu_id']     = $menu_id;
            $_role_menu['permissions'] = $permissions[$key];
            $role_menu[]               = $_role_menu;
        }

        // 检查当前用户所具备的菜单权限级别是否超限，开发者角色具有所有权限
        if(Session::get('user_info.role_id') != 1)
        {
            // 开发者角色仅允许拥有开发者角色的账号进行编辑
            if($is_edit == 1)
            {
                return ['error_code' => 400, 'error_msg' => '开发者角色不允许非开发者编辑'];
            }
            $user_role_menu = $this->RoleMenu->getRoleMenuListByRoleId(Session::get('user_info.role_id'));
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
            // 如果菜单列表不为空，则添加了额外的没有权限的菜单列表
            if (!empty($checked_menu)) {
                return ['error_code' => 400, 'error_msg' => '拟分配的菜单不存在或您没有分配该菜单的权限'];
            }
        }

        // 角色数据
        $role           = [];
        $role['name']   = trim($data['name']);
        $role['sort']   = intval($data['sort']) >= 0 ? intval($data['sort']) : 0;
        $role['remark'] = !empty($data['remark']) ? trim($data['remark']) : '';
        // 事务开始写入角色数据
        Db::startTrans();
        try{
            if($is_edit)
            {
                $role_id = $repeat_role['id'];
                // 更新角色
                Db::name('role')->where('id',$role_id)->update($role);
                // 编辑模式 删除原先角色的菜单数据
                Db::name('role_menu')->where('role_id',$role_id)->delete();
            }else{
                $role_id = Db::name('role')->insertGetId($role);
            }
            // insert新增角色的菜单权限
            foreach ($role_menu as $key => $value) {
                $role_menu[$key]['role_id'] = $role_id;
            }
            Db::name('role_menu')->insertAll($role_menu);
            $this->LogService->logRecorder([$role,$role_menu],$is_edit ? '编辑角色' : '新增角色');
            // 编辑角色之后清空缓存
            Cache::clear($this->cache_tag);
            Db::commit();
            return ['error_code' => 0,'error_msg' => '保存成功'];
        }catch (\Throwable $e) {
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
        if($sort <= 0)
        {
            return ['error_code' => 400,'error_msg' => '排序数字有误'];
        }
        $role = $this->Role->getRoleInfoById($id);
        if(empty($role))
        {
            return ['error_code' => 400,'error_msg' => '拟编辑排序的角色数据不存在'];
        }
        $ret = $this->Role->isUpdate(true)->save(['sort' => intval($sort)],['id' => $id]);
        // 编辑角色之后清空缓存
        Cache::clear($this->cache_tag);
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
        $role = $this->Role->getRoleInfoById($id);
        $role_menu = $this->RoleMenu->getRoleMenuListByRoleId($id);
        if($id == 1)
        {
            return ['error_code' => 400,'error_msg' => '开发者角色不允许删除'];
        }
        if(empty($role))
        {
            return ['error_code' => 400,'error_msg' => '拟删除的角色数据不存在'];
        }
        // 检查有木有用户已使用该角色
        $role_user = $this->User->where('role_id',$id)->select();
        if(!$role_user->isEmpty())
        {
            return ['error_code' => 400,'error_msg' => '拟删除的角色已分配用户，请先调整用户所属角色'];
        }
        // 事务进行角色数据删除
        Db::startTrans();
        try {
            Db::name('role')->where('id',$id)->delete();
            Db::name('role_menu')->where('role_id',$id)->delete();
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder(array_merge($role,$role_menu));
            // 编辑角色之后清空缓存
            Cache::clear($this->cache_tag);
            Db::commit();
            return ['error_code' => 0,'error_msg' => '角色删除成功'];
        }catch (\Throwable $e) {
            Db::rollback();
            return ['error_code' => 0,'error_msg' => '角色删除失败：'.$e->getMessage()];
        }
    }
}
