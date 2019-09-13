<?php
/**
 * 组织账号列表 -- 底层沿用后台用户列表
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:16
 * @file OrganizationUserSearch.php
 */

namespace app\manage\model\search;

use app\common\model\Menu;
use think\Db;

class OrganizationUserSearch extends BaseSearch
{
    public function lists($act_user_info)
    {
        try {
            // 菜单不显式权限，内部实现为部门及子部门数据模式
            $act_user_info['menu_auth']['is_permissions'] = 1;
            $act_user_info['menu_auth']['permissions']    = Menu::PERMISSION_LEADER;

            return $this->search($act_user_info);
        } catch (\Throwable $e) {
            $this->pageError = $e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 组织账号列表
     * @param array $act_user
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function search($act_user)
    {
        // 构造Query对象
        $Query = Db::name('user user')
            ->field([
                'user.id',
                'user.user_name',
                'create_user.user_name as create_user_name',
                'user.role_id',
                'user.dept_id',
                'user.real_name',
                'user.mobile',
                'user.telephone',
                'user.is_leader',
                'user.email',
                'user.gender',
                'user.enable',
                'user.sort',
                'user.create_time',
                'user.update_time',
                'user.remark',
                'role.name as role_name',
                'department.name as dept_name',
            ])
            ->leftJoin('role role', 'role.id = user.role_id')
            ->leftJoin('user create_user', 'user.create_user_id = create_user.id')
            ->leftJoin('department department', 'department.id = user.dept_id');

        // 权限限定 + 可能的部门检索
        $dept_columns = 'user.dept_id';
        $user_columns = 'user.id';
        $this->permissionLimitOrDeptSearch($Query, $dept_columns, $user_columns, $act_user);

        /**
         * 检索条件
         */
        // 关键词搜索
        $search_columns = ['user.user_name', 'user.real_name', 'user.mobile', 'user.email', 'user.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        $enable = $this->request->param('enable');
        if (in_array($enable, ['0','1'])) {
            $Query->where('user.enable', $enable);
        }

        // 时间范围检索
        $this->dateTimeSearch($Query, 'user.create_time');
        // 时间范围检索--更新时间范围检索
        $this->dateTimeSearch(
            $Query,
            'user.update_time',
            $this->request->param('update_time_begin'),
            $this->request->param('update_time_end')
        );

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'user');
        if ($Query->getOptions('order') === null) {
            $Query->order('user.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
