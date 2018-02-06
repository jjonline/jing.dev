<?php
/**
 * 角色服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-11 21:46
 * @file RoleService.php
 */

namespace app\manage\service;


use app\common\helpers\GenerateHelper;
use app\manage\model\Role;
use think\Db;
use think\facade\Cache;
use think\Request;

class RoleService
{
    /**
     * @var Role
     */
    public $Role;

    public function __construct(Role $Role)
    {
        $this->Role = $Role;
    }

    /**
     * 保存角色
     * @param Request $request
     * @param bool $isAdd 是否新增模式
     * @return []
     */
    public function saveRole(Request $request , $isAdd = false)
    {
        $data = $request->post();
        if(empty($data['name']) || empty($data['menu']))
        {
            return ['error_code' => -1,'error_msg' => '角色名称或角色菜单权限缺失'];
        }
        // 编辑模式会传原name，post字段名为origin_name
        $name  = isset($data['origin_name']) ? trim($data['origin_name']) : trim($data['name']);
        $exist = $this->Role->getRoleByName($name);
        if($isAdd && $exist)
        {
            return ['error_code' => -1,'error_msg' => '角色名称已存在'];
        }
        if(!$isAdd && !$exist)
        {
            return ['error_code' => -1,'error_msg' => '拟修改的角色名称不存在'];
        }
        $role           = [];
        $role['name']   = trim($data['name']);
        $role['sort']   = intval($data['sort']) < 0 ? 0 : intval($data['sort']);
        $role['remark'] = trim($data['remark']);

        // 角色权限
        $role_menu = [];
        foreach ($data['menu'] as $menu) {
            $_role_menu = [];
            $_role_menu['id']        = GenerateHelper::uuid();
            $_role_menu['menu_name'] = $menu;
            $_role_menu['role_name'] = $role['name'];
            $role_menu[]             = $_role_menu;
        }

        // 事务操作 新增角色后写入角色权限
        Db::startTrans();
        try {
            // 编辑更新的情况 删除老数据
            if($exist)
            {
                Db::name('role')->data($role)->where(['name' => $name])->update();//更新角色
                Db::name('role_menu')->where(['role_name' => $name])->delete();//删除老角色下的菜单权限
            }else {
                Db::name('role')->insert($role);
            }
            Db::name('role_menu')->insertAll($role_menu);
            // 提交事务
            Db::commit();
            // 编辑角色之后清空缓存
            Cache::clear();
            return ['error_code' => 0,'error_msg' => '角色保存成功'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['error_code' => -1,'error_msg' => '角色保存出现错误'];
        }
    }
}