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
use think\Exception;
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

    public function save(Request $request)
    {

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
        $ret = $this->Role->db()->where('id',$id)->delete();
        // 日志方式备份保存原始菜单信息
        $this->LogService->logRecorder($role);
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '菜单删除成功'] :
            ['error_code' => 500,'error_msg' => '菜单删除失败：系统异常'];
    }
}
