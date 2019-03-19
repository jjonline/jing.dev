<?php
/**
 * 网站单页检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file PageSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class PageSearch extends BaseSearch
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
     * 前台网站单页搜索
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
        $Query = Db::name('page page')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   'page.id',
                   'page.flag',
                   'page.title',
                   'page.enable',
                   'page.template',
                   'page.sort',
                   'page.create_time',
                   'page.update_time',
               ]);

        // 部门检索 + 权限限制 [单页面没有部门限制，此处调用直接返回]
        $this->permissionLimitOrDeptSearch(
            $Query,
            'page.dept_id',
            'page.user_id',
            $act_member_info
        );

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['page.title','page.flag'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 设置列表只显示启用状态的数据
        $Query->where('page.enable', 1);

        // 时间范围检索
        $this->dateTimeSearch($Query, 'page.create_time');


        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'page');
        if ($Query->getOptions('order') === null) {
            $Query->order('page.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }

    /**
     * 配置单页面各项设置属性的列表页面
     * @param $act_member_info
     * @return array
     */
    public function config($act_member_info)
    {
        try {
            return $this->configSearch($act_member_info);
        } catch (\Throwable $e) {
            $this->pageError = '出现异常：'.$e->getMessage();
            return $this->handleResult();
        }
    }

    /**
     * 前台网站单页搜索
     * @param $act_member_info
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function configSearch($act_member_info)
    {
        // 构造Query对象
        $Query = Db::name('page page')
            ->field([
                //'CONCAT("DT_Member_",member.id) as DT_RowId',
                'page.id',
                'page.flag',
                'page.title',
                'page.enable',
                'page.template',
                'page.sort',
                'page.create_time',
                'page.update_time',
            ]);

        // 部门检索 + 权限限制 [单页面没有部门限制，此处调用直接返回]
        $this->permissionLimitOrDeptSearch(
            $Query,
            'page.dept_id',
            'page.user_id',
            $act_member_info
        );

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['page.title','page.flag'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        $enable = $this->request->param('adv_enable');
        if (in_array($enable, ['0','1'])) {
            $Query->where('page.enable', $enable);
        }

        // 时间范围检索
        $this->dateTimeSearch($Query, 'page.create_time');


        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'page');
        if ($Query->getOptions('order') === null) {
            $Query->order('page.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
