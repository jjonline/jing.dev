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
use app\common\model\Menu;
use app\common\model\Role;
use app\manage\service\RoleService;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Session;
use think\facade\Url;

class AuthService
{
    /**
     * @var RoleService
     */
    public $RoleService;

    public function __construct(RoleService $roleService)
    {
        $this->RoleService = $roleService;
    }

    /**
     * 获取用户具有层级结构的左侧管理菜单列表，内部自动处理根用户情况
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserAuthMenu()
    {
        $user_id = Session::get('user_info.id');
        $data    = $this->RoleService->getRoleMenuListByUserId($user_id);
        $menu    = [];
        $menu1   = [];
        $menu2   = [];
        $menu3   = [];

        // 导航栏高亮、一级导航默认展开标记处理
        $highLight = [];//当前高亮的层级
        $now_url   = $this->generateRequestMenuUrl();
        foreach ($data as $key => $value) {
            $value['active']     = false;//高亮
            if ($value['url'] == $now_url) {
                $value['active'] = true;
                $highLight       = $value;//当前高亮的菜单
            }
            if ($value['level'] == 1) {
                $value['menu_open'] = false;//仅一级导航栏需要标记是否展开，默认不展开后方设置高亮再处理
            }

            // 减少数据量
            unset(
                $value['is_required'],
                $value['is_system'],
                $value['is_permissions'],
                $value['is_column'],
                $value['sort'],
                $value['remark'],
                $value['permissions'],
                $value['create_time'],
                $value['update_time'],
                $value['show_columns'],
                $value['all_columns']
            );

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
            $menu[$key1] = $value1;
            $menu[$key1]['children'] = $_menu2;
        }

        // 设置高亮返回
        return $this->setHighLight($menu, $highLight);
    }

    /**
     * 检查当前用户访问的url或指定url是否有权限
     * @param string $auth_tag 检查权限的Url或该菜单对应为全局唯一的字符串即菜单的tag字符串，为null则检查当前Url
     * @return bool
     * @throws Exception
     */
    public function userHasPermission($auth_tag = null)
    {
        // 未登录直接抛异常终止执行
        $user_id = Session::get('user_id');
        if (empty($user_id)) {
            throw new Exception('用户登录状态未初始化不可调用userHasPermission方法', 500);
        }

        try {
            // 如果未传参则拼接当前url
            if (empty($auth_tag)) {
                $auth_tag = $this->generateRequestMenuUrl();
            }

            // 缓存数据的Key
            $user_menu_map = $this->getRoleMenuMapByUserId($user_id);

            return isset($user_menu_map[$auth_tag]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 获取指定Url的当前用户的单个菜单的权限信息
     * --
     * 大部分时候无参数调用
     * --
     * @param string $auth_tag
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserSingleMenuInfo($auth_tag = null)
    {
        if (!$this->userHasPermission($auth_tag)) {
            throw new Exception('用户无该菜单权限，获取菜单权限信息失败', 500);
        }

        // 当前用户ID
        $user_id = Session::get('user_id');

        // 没有显式给要检查的tag或url时自动生成当前页面的tag
        if (empty($auth_tag)) {
            $auth_tag = $this->generateRequestMenuUrl();
        }

        // 读取权限Map返回指定查找的权限数据情况
        $user_menu_map = $this->getRoleMenuMapByUserId($user_id);
        // ArrayHelper::group分组帮助函数生成的结构是键名构成的多维数组
        // 方法体前方进行权限判断，此处绝对存在下标为0的元素
        return $user_menu_map[$auth_tag][0];
    }

    /**
     * 获取用户指定Url的权限标记，即返回：['super','leader','staff','guest']中的一者
     * @param mixed $tag 待检查的菜单标签名称或菜单无前缀url
     * @return string 一下4个中的1个-super|leader|staff|guest
     */
    public function getUserPermissionsTag($tag = null)
    {
        try {
            $menu = $this->getUserSingleMenuInfo($tag);
            return $menu['permissions'];
        } catch (\Throwable $e) {
            return Menu::PERMISSION_GUEST;
        }
    }

    /**
     * 按用户ID读取这个用户所属角色菜单按tag和url为键名的map
     * @param integer $user_id
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getRoleMenuMapByUserId($user_id)
    {
        /**
         * 因为存在根用户权限，无法仅仅依靠角色tag和url为键名的map来获得权限，必须按用户来缓存这个map
         */
        $user_menu_cache_Map_key = Role::ROLE_CACHE_MAP_PREFIX.$user_id;

        // 生产环境优先从缓存中读取
        if (!Config::get('app.app_debug')) {
            $user_menu_map = Cache::get($user_menu_cache_Map_key);
            if (!empty($user_menu_map)) {
                return $user_menu_map;
            }
        }

        // 缓存不存在或非生产
        $user_menu_map = $this->RoleService->getRoleMenuListByUserId($user_id);

        // 该用户没有任何菜单权限 返回空数组
        if (empty($user_menu_map)) {
            return [];
        }

        // 按url和tag分组，url和tag成为数组的键名
        $user_menu_map1 = ArrayHelper::group($user_menu_map, 'url');
        $user_menu_map2 = ArrayHelper::group($user_menu_map, 'tag');
        $user_menu_map  = array_merge($user_menu_map1, $user_menu_map2);

        // 生产环境 按用户将map缓存
        if (!Config::get('app.app_debug')) {
            Cache::tag(Role::ROLE_CACHE_TAG)->set($user_menu_cache_Map_key, $user_menu_map, 3600 * 720);
        }

        return $user_menu_map;
    }

    /**
     * 设置导航栏高亮属性
     * @param array $UserAuthMenu 用户所具有的菜单权限数组
     * @param array $highLight 检测到的当前高亮菜单数组
     * @return array
     */
    private function setHighLight($UserAuthMenu, $highLight)
    {
        if (empty($highLight)) {
            return $UserAuthMenu;
        }
        // 1级高亮
        if ($highLight['level'] == 1) {
            foreach ($UserAuthMenu as $key => $value) {
                if ($value['id'] == $highLight['id']) {
                    $UserAuthMenu[$key]['menu_open'] = true;
                    $UserAuthMenu[$key]['active']    = true;
                }
            }
            return $UserAuthMenu;
        }
        // 2级高亮
        if ($highLight['level'] == 2) {
            foreach ($UserAuthMenu as $key => $value) {
                if ($value['id'] == $highLight['parent_id']) {
                    $UserAuthMenu[$key]['menu_open'] = true;
                    $UserAuthMenu[$key]['active']    = true;
                }
            }
            return $UserAuthMenu;
        }
        // 3级高亮
        if ($highLight['level'] == 3) {
            foreach ($UserAuthMenu as $key1 => $value1) {
                // 遍历二级
                if (!empty($value1['children'])) {
                    foreach ($value1['children'] as $key2 => $value2) {
                        // 遍历三级
                        if (!empty($value2['children'])) {
                            foreach ($value2['children'] as $key3 => $value3) {
                                if ($highLight['id'] == $value3['id']) {
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
        // 菜单数据异常，原样返回
        return $UserAuthMenu;
    }

    /**
     * 依据当前模块、控制器、操作生成与菜单权限对应的无斜杠前缀、无文件后缀的Url组成部分
     * @return string
     */
    private function generateRequestMenuUrl()
    {
        $request   = request();
        $component = $request->module().'/'.$request->controller().'/'.$request->action();
        return trim(Url::build($component, '', ''), '/');
    }
}
