<?php
/**
 * 关键词检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-16 17:57:00
 * @file TagSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class TagSearch extends BaseSearch
{
    /**
     * 前台不呈现异常信息
     * @param $act_member_info
     * @return array
     */
    public function lists($act_member_info)
    {
        try {
            return $this->search($act_member_info);
        } catch (\Throwable $e) {
            $this->pageError = '出现异常：'.$e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 关键词搜索
     * @param $act_member_info
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function search($act_member_info)
    {
        // 构造Query对象
        $Query = Db::name('tag tag')
           ->field([
               //'CONCAT("DT_Member_",member.id) as DT_RowId',
               'tag.id',
               'tag.tag',
               'tag.cover_id',
               'tag.excerpt',
               'tag.quota',
               'tag.sort',
               'tag.user_id',
               'tag.dept_id',
               'user.real_name',
               'dept.name as dept_name',
               'tag.create_time',
               'tag.update_time',
           ])
           ->leftJoin('user user', 'user.id = tag.user_id')
           ->leftJoin('department dept', 'dept.id = tag.dept_id');

        // 部门检索 + 权限限制
        $this->permissionLimitOrDeptSearch(
            $Query,
            'tag.dept_id',
            'tag.user_id',
            $act_member_info
        );

        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['tag.tag','tag.excerpt','tag.id'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 指定用户检索
        $user_id = $this->request->param('user_id');
        if (!empty($user_id) && is_numeric($user_id)) {
            $Query->where('tag.user_id', $user_id);
        }

        // 时间范围检索
        $this->dateTimeSearch($Query, 'tag.create_time');

        // 引用范围检索
        $begin_quota = $this->request->param('quota_begin');
        $end_quota = $this->request->param('quota_end');
        $this->rangeSearch($Query, 'tag.quota', $begin_quota, $end_quota);

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'tag');
        if ($Query->getOptions('order') === null) {
            $Query->order('tag.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
