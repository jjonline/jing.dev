<?php
/**
 *
 * @member Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-29 14:35
 * @file MemberSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class MemberSearch extends BaseSearch
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
        }catch (\Throwable $e) {
            $this->pageError = '出现异常：'.$e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 前台用户搜索
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
        if(!in_array($menu_auth['permissions'],['super','leader','staff']))
        {
            $this->pageError = '抱歉，您没有操作权限';
            return $this->handleResult();
        }

        // 构造Query对象
        $Query = Db::name('member member')
               ->field([
                    //'CONCAT("DT_Member_",member.id) as DT_RowId',
                    'member.id',
                    'member.user_name',
                    'member.real_name',
                    'member.mobile',
                    'member.telephone',
                    'member.email',
                    'member.gender',
                    'member.enable',
                    'member.province',
                    'member.member_level_id',
                    'member_level.name as level_name',
                    'member.city',
                    'member.district',
                    'member.address',
                    'member.current_points',
                    'member.accumulate_points',
                    'member.create_time',
                    'member.remark'
               ])
               ->leftJoin('member_level member_level','member_level.id = member.member_level_id');

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['member.user_name', 'member.real_name', 'member.mobile', 'member.email', 'member.remark'];
        $this->keywordSearch($Query,$search_columns,$this->keyword);

        // 时间范围检索
        $this->dateTimeSearch($Query,'member.create_time');

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query,'member');
        if ($Query->getOptions('order') === null) {
            $Query->order('member.create_time','DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
