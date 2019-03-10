<?php
/**
 * 后台用户列表
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:16
 * @file UserSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class UserSearch extends BaseSearch
{
    public function lists($act_user_info)
    {
        try {
            return $this->search($act_user_info);
        } catch (\Throwable $e) {
            $this->pageError = $e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 后台管理员列表
     * ----
     * 1、菜单权限必须是super或leader才能显示数据
     * 2、如果是super显示所有
     * 3、如果是leader仅显示该部门下的员工账号数据
     * ----
     * @param $user_info
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function search($user_info)
    {
        // 构造Query对象
        $Query = Db::name('user user')
               ->field([
                   'user.id',
                   'user.user_name',
                   'user.role_id',
                   'user.dept_id',
                   'user.real_name',
                   'user.mobile',
                   'user.telephone',
                   'user.is_leader',
                   'user.email',
                   'user.gender',
                   'user.enable',
                   'user.create_time',
                   'user.remark',
                   'role.name as role_name',
                   'department.name as dept_name',
               ])
               ->leftJoin('role role', 'role.id = user.role_id')
               ->leftJoin('department department', 'department.id = user.dept_id');

        // 权限限定 + 可能的部门检索
        $dept_columns = 'user.dept_id';
        $user_columns = 'user.id'; // user_id就是主键ID
        $this->permissionLimitOrDeptSearch($Query, $dept_columns, $user_columns, $user_info);

        /**
         * 检索条件
         */
        // 关键词搜索
        $search_columns = ['user.user_name', 'user.real_name', 'user.mobile', 'user.email', 'user.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 时间范围检索
        $this->dateTimeSearch($Query, 'user.create_time');

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
