<?php
/**
 * __LIST_NAME__检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date __CREATE_TIME__
 * @file __CONTROLLER__Search.php
 */

namespace app\manage\model\search;

use think\Db;

class __CONTROLLER__Search extends BaseSearch
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
     * 前台__LIST_NAME__搜索
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
        $Query = Db::name('__CONTROLLER_UNDER_SCORE__ __CONTROLLER_UNDER_SCORE__')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   '__CONTROLLER_UNDER_SCORE__.id',

                   '__CONTROLLER_UNDER_SCORE__.create_time',
                   '__CONTROLLER_UNDER_SCORE__.remark'
               ]);
        // ->leftJoin('member_level member_level', 'member_level.id = member.member_level_id');

        // 部门检索 + 权限限制
        // $this->permissionLimitOrDeptSearch(
        //    $Query,
        //    '__CONTROLLER_UNDER_SCORE__.dept_id',
        //    '__CONTROLLER_UNDER_SCORE__.user_id',
        //    $act_member_info
        // );


        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['__CONTROLLER_UNDER_SCORE__.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        // $enable = $this->request->param('enable');
        // if (in_array($enable, ['0','1'])) {
        //    $Query->where('__CONTROLLER_UNDER_SCORE__.enable', $enable);
        //}

        // 时间范围检索
        $this->dateTimeSearch($Query, '__CONTROLLER_UNDER_SCORE__.create_time');

        // 数字范围检索
        $xx_begin = $this->request->param('xx_begin');
        $xx_end   = $this->request->param('xx_end');
        $this->rangeSearch($Query, '__CONTROLLER_UNDER_SCORE__.xx', $xx_begin, $xx_end);

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, '__CONTROLLER_UNDER_SCORE__');
        if ($Query->getOptions('order') === null) {
            $Query->order('__CONTROLLER_UNDER_SCORE__.id', 'DESC');
        }

        /**
         * 1、自动处理查询字段[表名 + 字段名 + 别名自动处理]
         * 2、自动处理可排序字段的情形
         */
        // $this->setCustomizeColumnsOptions($Query, $act_member_info);
        // 字段排序以及没有排序的情况下设定一个默认排序字段
        // if ($Query->getOptions('order') === null) {
        //    $Query->order('customer.id', 'DESC');
        // }

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
