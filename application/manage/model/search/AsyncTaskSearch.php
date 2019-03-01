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
            $this->pageError = $e->getMessage();
            return $this->handleResult();
        }
    }

    /**
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

        // 权限限定 + 可能的部门检索
        $dept_columns = 'async_task.dept_id';
        $user_columns = 'async_task.user_id';
        $this->permissionLimitOrDeptSearch($Query, $dept_columns, $user_columns, $user_info);

        // 关键词搜索
        $search_columns = [
            'user.real_name',
            'async_task.title',
        ];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 任务状态检索
        if ($this->request->has('task_status', 'GET') && is_numeric($this->request->param('task_status'))) {
            $Query->where('async_task.task_status', $this->request->param('task_status'));
        }

        // 时间范围检索--创建时间
        $this->dateTimeSearch(
            $Query,
            'async_task.create_time',
            $this->request->param('create_time_begin'),
            $this->request->param('create_time_end')
        );

        // 时间范围检索--任务开始时间
        $this->dateTimeSearch(
            $Query,
            'async_task.delivery_time',
            $this->request->param('delivery_time_begin'),
            $this->request->param('delivery_time_end')
        );

        // 时间范围检索--任务结束时间
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
