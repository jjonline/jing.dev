<?php
/**
 * 异步任务列表
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:16
 * @file AsyncTaskSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class AsyncTaskSearch extends BaseSearch
{
    public function lists($act_user_info)
    {
        try {
            return $this->search($act_user_info);
        } catch (\Throwable $e) {
            $this->pageError = '出现异常：'.$e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 查看异步任务状态
     * @param $user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function search($user_info)
    {
        // 1、超级管理员菜单权限可看全部
        // 2、leader菜单权限且属于部门领导可看所属部门以及子部门下成员
        // 3、leader菜单权限但不是领导只能看本部门下的子部门的职员数据
        $menu_auth = $user_info['menu_auth'];
        $dept_auth = $user_info['dept_auth'];
        if (!in_array($menu_auth['permissions'], ['super','leader','staff'])) {
            $this->pageError = '抱歉，您没有操作权限';
            $this->handleResult();
        }

        // 构造Query对象
        $Query = Db::name('async_task async_task')
            ->field([
                'async_task.id',
                'async_task.title',
                'async_task.task_status',
                'async_task.delivery_time',
                'async_task.finish_time',
                'async_task.create_time',
                'user.real_name',
                'department.name as dept_name',
            ])
            ->leftJoin('user user', 'user.id = async_task.user_id')
            ->leftJoin('department department', 'department.id = async_task.dept_id');

        if ($menu_auth['permissions'] == 'leader') {
            $Query->where('async_task.dept_id', 'IN', $dept_auth['dept_id_vector']);
        }
        if ($menu_auth['permissions'] == 'staff') {
            $Query->where('async_task.user_id', '=', $user_info['id']);
        }
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = [
            'user.real_name',
            'async_task.title',
        ];
        $this->keywordSearch($Query, $search_columns, $this->keyword);
        // 选择了部门
        $select_dept_id = $this->request->param('dept_id/i');
        if (!empty($select_dept_id)) {
            $Query->where('async_task.dept_id', $select_dept_id);
        }
        if ($this->request->has('task_status', 'GET') && is_numeric($this->request->param('task_status'))) {
            $Query->where('async_task.task_status', $this->request->param('task_status'));
        }
        // 时间范围检索
        $this->dateTimeSearch(
            $Query,
            'async_task.create_time',
            $this->request->param('create_time_begin'),
            $this->request->param('create_time_end')
        );
        $this->dateTimeSearch(
            $Query,
            'async_task.delivery_time',
            $this->request->param('delivery_time_begin'),
            $this->request->param('delivery_time_end')
        );
        $this->dateTimeSearch(
            $Query,
            'async_task.finish_time',
            $this->request->param('finish_time_begin'),
            $this->request->param('finish_time_end')
        );

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'async_task');
        if ($Query->getOptions('order') === null) {
            $Query->order('async_task.create_time', 'DESC');
        }
        // 查询当前分页列表数据
        $this->results = $Query->limit($this->start, $this->length)->select();
        // 处理结果集
        return $this->handleResult();
    }
}
