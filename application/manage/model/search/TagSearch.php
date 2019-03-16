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
     * 前台关键词搜索
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
        $Query = Db::name('tag tag')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   'tag.id',

                   'tag.create_time',
                   'tag.remark'
               ]);
               // ->leftJoin('member_level member_level', 'member_level.id = member.member_level_id');

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['tag.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        // $enable = $this->request->param('enable');
        // if (in_array($enable, ['0','1'])) {
        //    $Query->where('tag.enable', $enable);
        //}

        // 时间范围检索
        $this->dateTimeSearch($Query, 'tag.create_time');

        // 数字范围检索
        // $this->rangeSearch($Query, 'tag.xxx', $begin_range, $end_range);

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
