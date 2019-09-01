<?php
/**
 * 模型层具备数据权限控制的统一trait，便于做数据范围权限控制
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-08-12 10:49
 * @file PermissionsTrait.php
 */

namespace app\common\model;

use app\common\helper\ArrayHelper;
use think\db\Query;
use think\Exception;

trait PermissionsTrait
{
    /**
     * 数据范围限制和部门检索融合方法
     * ----
     * 1、有数据范围限定的执行范围限定逻辑
     * 2、有部门检索需求的执行部门及子部门数据检索
     * ----
     * @param Query  $query
     * @param string $dept_column  当前被检索的表的部门字段名称，一般是dept_id，可能会有别名
     * @param string $user_column  当前数据表的用户ID字段，可能会有别名
     * @param array  $user_info    当前登录用户信息，由控制器传递过来
     * @param int    $search_dept_id  可能的检索的部门id参数
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function permissionsLimitOrDeptSearch(
        Query &$query,
        $dept_column,
        $user_column,
        array $user_info,
        $search_dept_id = 0
    ) {
        // 有数据范围限制 + 部门检索条件的处理
        if ($user_info['menu_auth']['is_permissions']) {
            // 全部数据--部门及子部门检索[根用户在菜单权限处理时已处理为super]
            if (Menu::PERMISSION_SUPER == $user_info['menu_auth']['permissions']) {
                // 全部数据且没有部门检索要求返回
                if (empty($search_dept_id)) {
                    return;
                }
                // 获取拟查找部门的所有子部门
                /**
                 * @var Department $departModel
                 */
                $departModel = app(Department::class);
                $search_dept = $departModel->getDeptChildAndSelfIdArrayById($search_dept_id);

                // 去重+重排数字索引后按部门检索
                $query->where($dept_column, 'IN', ArrayHelper::filterByCallableThenUnique($search_dept, 'intval'));
            }
            // 部门及子部门数据范围--部门及子部门限定条件下的检索指定部门数据
            if (Menu::PERMISSION_LEADER == $user_info['menu_auth']['permissions']) {
                // 部门及子部门数据范围，没有检索部门条件，将用户所属部门设置成部门检索条件
                if (empty($search_dept_id)) {
                    $search_dept_id = $user_info['dept_id'];
                }
                // 没有该部门查看权限，只显示该部门下可能的属于个人的数据
                if (!in_array($search_dept_id, $user_info['dept_auth']['dept_id_vector'])) {
                    $query->where(function (Query $subQuery) use (
                        $search_dept_id,
                        $dept_column,
                        $user_column,
                        $user_info
                    ) {
                        $subQuery->where($user_column, $user_info['id'])
                            ->where($dept_column, $search_dept_id);
                    });
                    return;
                }

                // 获取拟查找部门的所有子部门
                /**
                 * @var Department $departModel
                 */
                $departModel   = app(Department::class);
                $search_dept   = $departModel->getDeptChildAndSelfIdArrayById($search_dept_id);

                $search_dept   = ArrayHelper::filterByCallableThenUnique($search_dept, 'intval');

                /**
                 * leader数据范围的内涵条件：
                 * 1、如果有部门检索条件则仅检索指定部门[及其子部门]的数据
                 * 2、如果没有部门检索条件，则检索部门及子部门数据 ++ 个人数据
                 */
                $query->where(function (Query $subQuery) use (
                    $dept_column,
                    $user_column,
                    $search_dept,
                    $user_info,
                    $search_dept_id
                ) {
                    if ($search_dept_id) {
                        $subQuery->where($dept_column, 'IN', $search_dept);
                    } else {
                        $subQuery->where($dept_column, 'IN', $search_dept)
                            ->whereOr($user_column, $user_info['id']);
                    }
                });
            }
            // 个人数据--只能查看个人数据，用户ID条件必选
            if (Menu::PERMISSION_STAFF == $user_info['menu_auth']['permissions']) {
                if (empty($search_dept_id)) {
                    /**
                     * 没有部门检索，仅检索个人数据，这样任意部门存在的个人数据都会显示
                     */
                    $query->where($user_column, $user_info['id']);
                } else {
                    /**
                     * 子部门可能存在个人数据，按部门筛选个人数据的需求
                     */
                    $query->where(function (Query $subQuery) use (
                        $search_dept_id,
                        $dept_column,
                        $user_column,
                        $user_info
                    ) {
                        $subQuery->where($user_column, $user_info['id'])
                            ->where($dept_column, $search_dept_id);
                    });
                }
            }
            // 访客权限，不允许查看任何数据
            if (Menu::PERMISSION_GUEST == $user_info['menu_auth']['permissions']) {
                throw new Exception('您没有查看数据的权限');
            }
            return;
        }

        /**
         * 菜单中没有数据范围限制，但可能需要按部门来筛选数据
         */
        // 没有部门检索条件 返回
        if (empty($search_dept_id)) {
            return;
        }
        // 获取拟查找部门的所有子部门
        /**
         * @var Department $departModel
         */
        $departModel   = app(Department::class);
        $child_dept    = $departModel->getChildDeptByParentId($search_dept_id);
        // 检索的部门id索引数组并检索检索部门
        $search_dept   = $child_dept ?: [];
        $search_dept[] = $search_dept_id;
        // 去重+重排数字索引后按部门检索
        $query->where($dept_column, 'IN', ArrayHelper::filterByCallableThenUnique($search_dept, 'intval'));
    }
}
