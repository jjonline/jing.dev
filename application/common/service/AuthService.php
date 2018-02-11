<?php
/**
 * 授权Service服务
 * ---
 * 1、登录效验
 * 2、权限菜单效验
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:34
 * @file AuthService.php
 */

namespace app\common\service;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use app\common\helper\StringHelper;
use app\manage\model\User;
use app\common\model\Menu;
use app\manage\model\RoleMenu;
use app\common\model\Role;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;
use app\common\model\Department;

class AuthService
{
    /**
     * @var Menu
     */
    public $Menu;
    /**
     * @var User
     */
    public $User;
    /**
     * @var Role
     */
    public $Role;
    /**
     * @var RoleMenu
     */
    public $RoleMenu;
    /**
     * @var [] 高亮使用的key-value数组
     */
    protected $MenuMap = [];

    public function __construct(User $User,
                                Role $Role,
                                Department $Department)
    {
        $this->User           = $User;
        $this->Department     = $Department;
        $this->Role           = $Role;
    }

    /**
     * 获取用户管理菜单列表
     * @throws
     * @return []
     */
    public function getUserAuthMenu()
    {
        $data    = $this->getUserMenuList();
        $menu    = [];
        $menu1   = [];
        $menu2   = [];
        $menu3   = [];
        // 导航栏高亮、一级导航默认展开标记处理
        $highLight = false;//当前高亮的层级
        $request   = request();
        $now_url   = strtolower($request->controller().'/'.$request->action());
        foreach ($data as $key => $value)
        {
            $value['active']    = false;//高亮
            if($value['url'] == $now_url)
            {
                $value['active'] = true;
                $highLight       = $value;//当前高亮的菜单
            }
            if($value['level'] == 1)
            {
                $value['menu_open'] = false;//仅一级导航栏需要标记是否展开，默认不展开后方设置高亮再处理
            }
            // 仅处理三级菜单
            switch ($value['level'])
            {
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
            if(empty($value['url']))
            {
                $this->MenuMap[$value['url']] = $value;//以url为键名的数组
            }
        }
        // 按层级处理菜单数组--仅到3级
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
                    if($value2['name'] == $value3['parent_name'])
                    {
                        $_menu3[] = $value3;
                    }
                }
                $value2['children'] = $_menu3;

                if($value1['name'] == $value2['parent_name'])
                {
                    $_menu2[] = $value2;
                }
            }
            $menu[$key1] = $value1;
            $menu[$key1]['children'] = $_menu2;
        }

        //dump($menu);exit;

        //dump($this->userHasPermission());

        // 设置高亮
        $menu = $this->setHighLight($menu,$highLight);

        return $menu;
    }

    /**
     * 检查当前用户访问的url或指定url是否有权限
     * @param string $url
     * @throws
     * @return bool
     */
    public function userHasPermission($url = null)
    {
        // 未登录直接返回无权限
        $user_id = Session::get('user_id');
        if(empty($user_id))
        {
            throw new Exception('未初始化用户登录状态不可调用userHasPermission方法',500);
        }
        $request = request();
        if(empty($url))
        {
            $url = strtolower($request->controller().'/'.$request->action());
        }
        $user_menu_cache_Map_key = 'User_menu_cache_Map_key'.$user_id;
        if(!Config::get('app.app_debug'))
        {
            $user_menu_map = Cache::get($user_menu_cache_Map_key);
            if(!empty($user_menu_map))
            {
                // 查找到缓存 直接从缓存中判断
                return array_key_exists($url,$user_menu_map);
            }
        }
        $user_menu_map = $this->getUserMenuList();
        // 开发者账号，所有菜单都有权限
        if($user_id === 1)
        {
            $user_menu_map = $this->Menu->getMenuList();
        }
        if(empty($user_menu_map))
        {
            return false;
        }
        //按url分组，url成为数组的键名
        $user_menu_map = ArrayHelper::group($user_menu_map,'url');
        //依据开发模式与否将全新Map数组缓存
        if(!Config::get('app.app_debug'))
        {
            Cache::set($user_menu_cache_Map_key,$user_menu_map,3600 * 12);
        }
        return array_key_exists($url,$user_menu_map);
    }

    /**
     * 获取用户的权限菜单列表
     * @param string $user_id 可选的用户id，留空则获取当前登录用户
     * @throws
     * @return []
     */
    public function getUserMenuList($user_id = null)
    {
        $user_id   = !empty($user_id) ? $user_id : Session::get('user_id');
        if(empty($user_id))
        {
            return [];
        }
        // 开发者账号，显示所有菜单
        if($user_id === 1)
        {
            return $this->Menu->getMenuList();
        }
        // 依据开发模式自动选择是否启用户菜单缓存
        $user_menu_cache_key = 'User_Menu_Cache_Origin_key'.$user_id;
        if(!Config::get('app.app_debug'))
        {
            $user_menu = Cache::get($user_menu_cache_key);
            if(!empty($user_menu))
            {
                return $user_menu;
            }
        }
        $user_menu = Db::name('menu menu')
            ->field(['menu.*'])
            ->leftJoin('role_menu role_menu','role_menu.menu_name = menu.name')
            ->leftJoin('role role','role.name = role_menu.role_name')
            ->leftJoin('user_role user_role','user_role.role_name = role.name')
            ->leftJoin('user user','user.id = user_role.user_id')
            ->where(['user.id' => $user_id])
            ->group('menu.name')
            ->order(['menu.sort' => 'ASC','menu.level' => 'ASC'])
            ->select()->toArray();
        // 将结果集缓存
        if(!Config::get('app.app_debug'))
        {
            Cache::set($user_menu_cache_key,$user_menu,3600 * 12);//缓存12小时
        }
        return $user_menu;
    }

    /**
     * 设置导航栏高亮属性
     * @param array $UserAuthMenu 用户所具有的菜单权限数组
     * @param array $highLight 检测到的当前高亮菜单数组
     * @return []
     */
    protected function setHighLight($UserAuthMenu = [],$highLight)
    {
        if(empty($highLight))
        {
            return $UserAuthMenu;
        }
        // 1级高亮
        if($highLight['level'] == 1)
        {
            foreach ($UserAuthMenu as $key => $value) {
                if($value['name'] == $highLight['name'])
                {
                    $UserAuthMenu[$key]['menu_open'] = true;
                    $UserAuthMenu[$key]['active']    = true;
                }
            }
            return $UserAuthMenu;
        }
        // 2级高亮
        if($highLight['level'] == 2)
        {
            foreach ($UserAuthMenu as $key => $value) {
                if($value['name'] == $highLight['parent_name'])
                {
                    $UserAuthMenu[$key]['menu_open'] = true;
                    $UserAuthMenu[$key]['active']    = true;
                }
            }
            return $UserAuthMenu;
        }
        // 3级高亮
        if($highLight['level'] == 3)
        {
            foreach ($UserAuthMenu as $key1 => $value1) {
                // 遍历二级
                if(!empty($value1['children']))
                {
                    foreach ($value1['children'] as $key2 => $value2) {
                        // 遍历三级
                        if(!empty($value2['children']))
                        {
                            foreach ($value2['children'] as $key3 => $value3) {
                                if($highLight['name'] == $value3['name'])
                                {
                                    // 1级
                                    $UserAuthMenu[$key1]['menu_open'] = true;
                                    $UserAuthMenu[$key1]['active']    = true;
                                    // 2级
                                    $UserAuthMenu[$key1]['children'][$key2]['active'] = true;
                                }
                            }
                        }
                    }
                }
            }
            return $UserAuthMenu;
        }
    }
}
