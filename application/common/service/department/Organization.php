<?php
/**
 * 部门管理组织部门部分
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:28
 * @file Organization.php
 */

namespace app\common\service\department;

use app\common\helper\StringHelper;
use app\common\helper\TreeHelper;
use app\common\model\Department;
use app\common\model\Menu;
use app\common\model\User;
use app\common\service\LogService;
use think\Exception;
use think\facade\Cache;
use think\Request;

trait Organization
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

    /**
     * 组织部门管理
     * @param array $act_user
     * @return array
     */
    public function orgList(array $act_user)
    {
        try {
            // 菜单不显式权限，内部实现为部门及子部门数据模式
            $act_user['menu_auth']['is_permissions'] = 1;
            $act_user['menu_auth']['permissions']    = Menu::PERMISSION_LEADER;

            // 当前登录用户所属部门信息
            $dept_one = $this->Department->getDeptInfoById($act_user['dept_id']);
            $parent_one = $this->Department->getDeptInfoById($dept_one['parent_id']);

            // 所属用户下的子部门
            $dept = $this->Department->getAuthFullDeptList($act_user);
            foreach ($dept as $key => $value) {
                $dept[$key]['name_format1'] = StringHelper::leftPadLevel(
                    $value['name'],
                    $value['level'],
                    $dept_one['level'] - 1
                );
                $dept[$key]['name_format2'] = StringHelper::leftPadSpace(
                    $value['name'],
                    $value['level'],
                    $dept_one['level'] - 1
                );
            }

            return [TreeHelper::vTree($dept, $dept_one['parent_id']), $parent_one];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 组织部门新建|编辑
     * @param array $_dept
     * @param array $act_user
     * @return array
     */
    public function orgSave(array $_dept, array $act_user)
    {
        try {
            // 菜单不显式权限，内部实现为部门及子部门数据模式
            $act_user['menu_auth']['is_permissions'] = 1;
            $act_user['menu_auth']['permissions']    = Menu::PERMISSION_LEADER;

            $rule = [
                'parent_id' => 'require|number',
                'name'      => 'require|max:200',
                'sort'      => 'number',
                'remark'    => 'max:255',
            ];
            $column = [
                'name'      => '部门名称',
                'sort'      => '排序',
                'parent_id' => '上级部门',
                'remark'    => '备注',
            ];
            $this->checkRequestVariablesOrFail($_dept, $rule, $column);

            $is_edit = !empty($_dept['id']);
            if ($is_edit) {
                $exist_dept = $this->Department->getDeptInfoById($_dept['id']);
                if (empty($exist_dept)) {
                    throw new Exception('拟编辑部门不存在');
                }
            }

            // 构造数据
            $dept           = [];
            $dept['name']   = trim($_dept['name']);
            $dept['sort']   = intval($_dept['sort']);
            $dept['remark'] = $_dept['remark'];
            $dept['level']  = 1;

            // 允许的父部门id数组列表
            $dept_one                = $this->Department->getDeptInfoById($act_user['dept_id']);
            $allow_parent_id_array   = $this->Department->getDeptChildAndSelfIdArrayById($act_user['dept_id']);
            $allow_parent_id_array[] = $dept_one['parent_id'] ?? 0;
            if (!in_array($_dept['parent_id'], $allow_parent_id_array)) {
                throw new Exception('父部门参数有误');
            }
            if (empty($_dept['parent_id']) && empty($is_edit)) {
                // 新增模式，且父id为0不允许
                throw new Exception('不允许新建顶级部门');
            }

            // 上级部门和层级
            if ($_dept['parent_id'] != 0) {
                $parent_dept= $this->Department->getDeptInfoById($_dept['parent_id']);
                if (empty($parent_dept)) {
                    throw new Exception('所选上级部门不存在');
                }
                if ($parent_dept['level'] >= 5) {
                    throw new Exception('部门最大允许5级');
                }
                $dept['level']     = $parent_dept['level'] + 1;
                $dept['parent_id'] = $_dept['parent_id'];
            }

            if ($is_edit) {
                $dept['id'] = $_dept['id'];
            } else {
                // 新增模式补充创建人和创建人所属部门
                $dept['user_id'] = $act_user['id'];
                $dept['dept_id'] = $act_user['dept_id'];
            }
            $affected_rows = $this->Department->isUpdate($is_edit)->save($dept);

            if (false !== $affected_rows) {
                Cache::clear($this->cache_tag); // 按标签清理部门缓存
                $this->LogService->logRecorder([$_dept, $dept], $is_edit ? '编辑组织部门' :'新增组织部门');
                return ['error_code' => 0,'error_msg'   => '组织部门保存成功'];
            }

            throw new Exception('系统异常：保存数据失败');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }

    /**
     * 组织部门排序
     * @param Request $request
     * @param array $act_user
     * @return mixed
     */
    public function orgSort(Request $request, array $act_user)
    {
        try {
            // 菜单不显式权限，内部实现为部门及子部门数据模式
            $act_user['menu_auth']['is_permissions'] = 1;
            $act_user['menu_auth']['permissions']    = Menu::PERMISSION_LEADER;

            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }

            // 仅允许操作特定部门排序
            $allow_parent_id_array = $this->Department->getDeptChildAndSelfIdArrayById($act_user['dept_id']);
            if (!in_array($id, $allow_parent_id_array)) {
                throw new Exception('无权限操作该组织部门数据');
            }

            $dept = $this->Department->getDeptInfoById($id);
            if (empty($dept)) {
                throw new Exception('拟编辑排序的部门数据不存在');
            }

            $ret = $this->Department->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);

            Cache::clear($this->cache_tag); // 按标签清理部门缓存

            return $this->success2Array(false !== $ret ? '排序调整成功' : '排序调整失败：系统异常');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }

    /**
     * 删除废弃组织部门数据
     * @param Request $request
     * @param array $act_user
     * @return array
     */
    public function orgDelete(Request $request, array $act_user)
    {
        try {
            // 菜单不显式权限，内部实现为部门及子部门数据模式
            $act_user['menu_auth']['is_permissions'] = 1;
            $act_user['menu_auth']['permissions']    = Menu::PERMISSION_LEADER;

            $id   = $request->post('id/i');
            $dept = $this->Department->getDeptInfoById($id);
            if (empty($dept)) {
                throw new Exception('拟删除的部门数据不存在');
            }

            // 仅允许操作特定部门排序
            $allow_parent_id_array = $this->Department->getDeptChildAndSelfIdArrayById($act_user['dept_id']);
            if (!in_array($id, $allow_parent_id_array)) {
                throw new Exception('无权限操作该组织部门数据');
            }

            // 检查是否有子部门、检查是否有分配
            $exist_user = $this->User->db()->where('dept_id', $id)->select();
            if (!$exist_user->isEmpty()) {
                throw new Exception('无法删除：拟删除的部门已分配用户');
            }

            $exist_child = $this->Department->getDeptInfoByParentId($id);
            if (!empty($exist_child)) {
                throw new Exception('无法删除：拟删除的部门存在子部门');
            }

            $ret = $this->Department->db()->where('id', $id)->delete();
            // 日志方式备份保存原始菜单信息
            $this->LogService->logRecorder($dept, '删除部门');

            Cache::clear($this->cache_tag);// 按标签清理部门缓存

            return $this->success2Array(false !== $ret ? '部门删除成功' : '部门删除失败：系统异常');
        } catch (\Throwable $e) {
            return $this->exception2Array($e);
        }
    }
}
