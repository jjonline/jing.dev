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
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Session;
use think\Request;

class DepartmentService
{
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
     * 新增|编辑部门数据
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(Request $request)
    {
        $dept = $request->post('Dept/a');
        if (empty($dept['name']) || $dept['parent_id'] == -1) {
            return ['error_code' => 400,'error_msg' => '上级部门或部门名称缺失'];
        }
        $is_edit = !empty($dept['id']);
        if ($is_edit) {
            $exist_dept = $this->Department->getDeptInfoById($dept['id']);
            if (empty($exist_dept)) {
                return ['error_code' => 400,'error_msg' => '拟编辑部门不存在'];
            }
        }
        // 构造数据
        $Dept           = [];
        $Dept['name']   = trim($dept['name']);
        $Dept['sort']   = intval($dept['sort']) < 0 ? 1 : intval($dept['sort']);
        $Dept['remark'] = trim($dept['remark']);
        $Dept['level']  = 1;
        // 上级部门和层级
        if ($dept['parent_id'] != 0) {
            $parent_dept= $this->Department->getDeptInfoById($dept['parent_id']);
            if (empty($parent_dept)) {
                return ['error_code' => 400,'error_msg' => '所选上级部门不存在'];
            }
            if ($parent_dept['level'] >= 5) {
                return ['error_code' => 400,'error_msg' => '部门最大允许5级'];
            }
            $Dept['level']     = $parent_dept['level'] + 1;
            $Dept['parent_id'] = $dept['parent_id'];
        }
        if ($is_edit) {
            $Dept['id'] = $dept['id'];
            $result     = $this->Department->isUpdate(true)->data($Dept)->save();
        } else {
            $result     = $this->Department->isUpdate(false)->data($Dept)->save();
        }
        if ($result >= 0) {
            Cache::clear($this->cache_tag);// 按标签清理部门缓存
            $this->LogService->logRecorder($result, $is_edit ? '编辑部门' :'新增部门');
            return ['error_code' => 0,'error_msg'   => '部门保存成功'];
        }
        return ['error_code' => 500,'error_msg' => '部门保存失败：系统异常'];
    }

    /**
     * 跳转部门排序字段
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
        $dept = $this->Department->getDeptInfoById($id);
        if (empty($dept)) {
            return ['error_code' => 400,'error_msg' => '拟编辑排序的部门数据不存在'];
        }
        $ret = $this->Department->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
        Cache::clear($this->cache_tag);// 按标签清理部门缓存
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '排序调整成功'] :
            ['error_code' => 500,'error_msg' => '排序调整失败：系统异常'];
    }

    /**
     * 删除废弃部门数据
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
        $dept = $this->Department->getDeptInfoById($id);
        if (empty($dept)) {
            return ['error_code' => 400,'error_msg' => '拟删除的部门数据不存在'];
        }
        // 检查是否有子部门、检查是否有分配
        $exist_user = $this->User->db()->where('dept_id', $id)->select();
        if (!$exist_user->isEmpty()) {
            return ['error_code' => 400,'error_msg' => '无法删除：拟删除的部门已分配用户'];
        }
        $exist_child = $this->Department->getDeptInfoByParentId($id);
        if (!empty($exist_child)) {
            return ['error_code' => 400,'error_msg' => '无法删除：拟删除的部门存在子部门'];
        }
        $ret = $this->Department->db()->where('id', $id)->delete();
        // 日志方式备份保存原始菜单信息
        $this->LogService->logRecorder($dept, '删除部门');
        Cache::clear($this->cache_tag);// 按标签清理部门缓存
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '部门删除成功'] :
            ['error_code' => 500,'error_msg' => '部门删除失败：系统异常'];
    }

    /**
     * 用户id读取权限范围内的所有部门数结构
     * @param integer $user_id
     * @return array
     */
    public function getAuthDeptTreeList($user_id)
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
            $auth_dept   = TreeHelper::childWithSelf($this->getDeptList(), $user['dept_id']);
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
