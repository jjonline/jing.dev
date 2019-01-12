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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function search($act_member_info)
    {
        // 1、超级管理员菜单权限可看全部
        // 2、leader菜单权限且属于部门领导可看所属部门以及子部门下成员
        // 3、leader菜单权限但不是领导只能看本部门下的子部门的会员数据
        $menu_auth = $act_member_info['menu_auth'];
        $dept_auth = $act_member_info['dept_auth'];
        if (!in_array($menu_auth['permissions'], ['super','leader','staff'])) {
            $this->pageError = '抱歉，您没有操作权限';
            return $this->handleResult();
        }

        // 构造Query对象
        $Query = Db::name('__CONTROLLER_UNDER_SCORE__ __CONTROLLER_UNDER_SCORE__')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   '__CONTROLLER_UNDER_SCORE__.id',

                   '__CONTROLLER_UNDER_SCORE__.create_time',
                   '__CONTROLLER_UNDER_SCORE__.remark'
               ]);
               // ->leftJoin('member_level member_level', 'member_level.id = member.member_level_id');

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
        // $this->rangeSearch($Query, '__CONTROLLER_UNDER_SCORE__.xxx', $begin_range, $end_range);

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, '__CONTROLLER_UNDER_SCORE__');
        if ($Query->getOptions('order') === null) {
            $Query->order('__CONTROLLER_UNDER_SCORE__.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
