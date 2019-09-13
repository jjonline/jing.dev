<?php
/**
 * 部门管理服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-27 18:08
 * @file DepartmentService.php
 */

namespace app\common\service;

use app\common\helper\StringHelper;
use app\common\helper\TreeHelper;
use app\common\model\Department;
use app\common\model\User;
use app\common\service\department\Organization;
use app\common\service\department\Super;
use app\common\service\department\Utils;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Session;

class DepartmentService extends BaseService
{
    use Super;
    use Utils;
    use Organization;

    /**
     * @var Department
     */
    public $Department;
    /**
     * @var LogService
     */
    public $LogService;
    /**
     * @var User
     */
    public $User;
    /**
     * @var string 缓存所有部门列表数据的tag标识，可实现按tag标识清理缓存
     */
    public $cache_tag = 'Dept';

    public function __construct(
        Department $department,
        User $user,
        LogService $logService
    ) {
        $this->Department = $department;
        $this->LogService = $logService;
        $this->User       = $user;
    }

    /**
     * 用户id读取权限范围内的所有部门数结构
     * @param integer $user_id
     * @param bool $without_self 是否忽略指定用户所在的部门，默认不忽略
     * @return array
     */
    public function getAuthDeptTreeList($user_id, $without_self = false)
    {
        try {
            // 读取指定用户信息获得部门及子部门id数组
            $user = $this->User->getFullUserInfoById($user_id);
            if (empty($user)) {
                throw new Exception('指定用户不存在');
            }

            // 根用户能查看所有部门
            if (!empty($user['is_root'])) {
                return $this->getDeptTreeList();
            }

            // 非根用户读取后处理
            if ($without_self) {
                $auth_dept = TreeHelper::child($this->getDeptList(), $user['dept_id']);
            } else {
                $auth_dept = TreeHelper::childWithSelf($this->getDeptList(), $user['dept_id']);
            }
            $begin_level = $user['dept_level']; // 用户所属部门的层级

            // 格式化层级名称
            foreach ($auth_dept as $key => $value) {
                // 附加部门名称的层级标识
                $auth_dept[$key]['name_format1'] = StringHelper::leftPadLevel(
                    $value['name'],
                    $value['level'],
                    $begin_level
                );
                $auth_dept[$key]['name_format2'] = StringHelper::leftPadSpace(
                    $value['name'],
                    $value['level'],
                    $begin_level
                );
            }

            return $auth_dept;
        } catch (\Throwable $e) {
            dump($e);
            return [];
        }
    }

    /**
     * 获取所有部门树状列表
     * --
     * 按层级显示竖向数、通过部门名称前加标识符来区分
     * --
     * @return array
     */
    public function getDeptTreeList()
    {
        try {
            $dept = $this->getDeptList();
            foreach ($dept as $key => $value) {
                $dept[$key]['name_format1'] = $value['name'];
                $dept[$key]['name_format2'] = $value['name'];
                if ($value['level'] > 1) {
                    $dept[$key]['name_format1'] = StringHelper::leftPadLevel($value['name'], $value['level'], 1);
                    $dept[$key]['name_format2'] = StringHelper::leftPadSpace($value['name'], $value['level'], 1);
                }
            }
            return TreeHelper::vTree($dept);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 获取用户所辖部门信息，即所在部门和子部门信息
     * @param $dept_id
     * @return array|mixed
     * [
     *     'dept_id_vector' => 所在部门及子部门ID构成的索引数组,
     *     'dept_list'      => 所在部门及子部门多维数组,
     *     'dept_list_tree' => 所在部门及子部门纵向树排序的部门数据
     * ]
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAuthDeptInfoByDeptId($dept_id)
    {
        $user_id = Session::get('user_id');
        if (empty($user_id)) {
            throw new Exception('未登录状态不允许检查用户所辖部门信息');
        }
        // 生产环境缓存用户所辖部门数据
        if (!Config::get('app.app_debug')) {
            $vector = Cache::get('User_Auth_Dept'.$user_id);
            if (!empty($vector)) {
                return $vector;
            }
        }
        $vector = $this->getChildDeptInfoByParentDeptId($dept_id);
        $vector && Cache::tag($this->cache_tag)->set('User_Auth_Dept'.$user_id, $vector, 3600*12);
        return $vector;
    }

    /**
     * 获取指定部门ID下辖的所有子部门信息
     * @param $parent_id
     * @return array
     * [
     *    'dept_id_vector' => 包含能管理的部门ID的索引数组,
     *    'dept_list'      => 包含能管理的部门的多维数组,
     *    'dept_list_tree' => 按纵向树排序的部门数据
     * ]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChildDeptInfoByParentDeptId($parent_id)
    {
        $dept_list   = $this->getDeptList();
        $vector_list = TreeHelper::child($dept_list, $parent_id);
        $vector      = [];
        $begin_level = 0;//所辖部门开始层级
        // 将本部门信息也附加进去放在第一个元素
        foreach ($dept_list as $key => $value) {
            if ($parent_id == $value['id']) {
                $begin_level                = $value['level'];
                $value['name_format1']      = $value['name'];
                $value['name_format2']      = $value['name'];
                $vector['dept_id_vector'][] = $value['id'];
                $vector['dept_list'][]      = $value;
                break;
            }
        }
        foreach ($vector_list as $key => $value) {
            // 附加部门名称的层级标识
            $value['name_format1']      = StringHelper::leftPadLevel($value['name'], $value['level'], $begin_level);
            $value['name_format2']      = StringHelper::leftPadSpace($value['name'], $value['level'], $begin_level);
            $vector['dept_id_vector'][] = $value['id'];
            $vector['dept_list'][]      = $value;
        }
        // 将所辖部门处理成具有层级的纵向树顺序
        // 1、基层职员可能没有任何下辖部门的权限
        // 2、没有子部门的成员被设置成了非领导也没有下辖部门权限
        if (empty($vector)) {
            $vector = [
                'dept_id_vector' => [],
                'dept_list'      => []
            ];
        }
        $vector['dept_list_tree'] = TreeHelper::vTree($vector['dept_list']);
        return $vector;
    }

    /**
     * 带缓存的依据开发|生产环境获取所有部门列表数据方法
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getDeptList()
    {
        if (Config::get('app.app_debug')) {
            return $this->Department->getDeptList();
        }
        $dept = Cache::get('All_Dept');
        if (!empty($dept)) {
            return $dept;
        }
        $dept = $this->Department->getDeptList();
        $dept && Cache::tag($this->cache_tag)->set('All_Dept', $dept, 3600*12);// 生产环境缓存所有部门列表数据
        return $dept;
    }
}
