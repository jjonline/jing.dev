<?php
/**
 * 网站会员检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file CustomerSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class CustomerSearch extends BaseSearch
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
     * 前台网站会员搜索
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
        $Query = Db::name('customer customer')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   'customer.id',

                   'customer.create_time',
                   'customer.remark'
               ]);
               // ->leftJoin('member_level member_level', 'member_level.id = member.member_level_id');

        // 部门检索 + 权限限制
        $this->permissionLimitOrDeptSearch(
            $Query,
            'customer.dept_id',
            'customer.user_id',
            $act_member_info
        );

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['customer.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        // $enable = $this->request->param('enable');
        // if (in_array($enable, ['0','1'])) {
        //    $Query->where('customer.enable', $enable);
        //}

        // 时间范围检索
        $this->dateTimeSearch($Query, 'customer.create_time');

        // 数字范围检索
        // $this->rangeSearch($Query, 'customer.xxx', $begin_range, $end_range);

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'customer');
        if ($Query->getOptions('order') === null) {
            $Query->order('customer.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
