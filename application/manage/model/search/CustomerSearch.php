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
               ->leftJoin('user user', 'user.id = customer.user_id')
               ->leftJoin('department department', 'department.id = customer.dept_id');

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
        $search_columns = [
            'customer.customer_name',
            'customer.real_name',
            'customer.reveal_name',
            'customer.mobile',
            'customer.email',
            'customer.remark',
        ];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 时间范围检索
        $this->dateTimeSearch($Query, 'customer.create_time');

        // 有效积分范围检索
        $points_effect_begin = $this->request->param('points_effect_begin');
        $points_effect_end   = $this->request->param('points_effect_end');
        $this->rangeSearch($Query, 'customer.points_effect', $points_effect_begin, $points_effect_end);

        // 冻结积分范围检索
        $points_freeze_begin = $this->request->param('points_freeze_begin');
        $points_freeze_end   = $this->request->param('points_freeze_end');
        $this->rangeSearch($Query, 'customer.points_freeze', $points_freeze_begin, $points_freeze_end);

        // 等级积分范围检索
        $points_level_begin = $this->request->param('points_level_begin');
        $points_level_end   = $this->request->param('points_level_end');
        $this->rangeSearch($Query, 'customer.points_level', $points_level_begin, $points_level_end);

        // 省市县检索
        $select_province = $this->request->param('select_province');
        if (!empty($select_province)) {
            $Query->where('customer.province', $select_province);
        }
        $select_city = $this->request->param('select_city');
        if (!empty($select_province)) {
            $Query->where('customer.city', $select_city);
        }
        $select_district = $this->request->param('select_district');
        if (!empty($select_province)) {
            $Query->where('customer.district', $select_district);
        }

        // 禁用|启用状态检索
        $enable = $this->request->param('adv_enable');
        if (in_array($enable, ['0','1'])) {
            $Query->where('customer.enable', $enable);
        }

        // 性别检索
        $gender = $this->request->param('adv_gender');
        if (in_array($gender, ['0','1', '-1'])) {
            $Query->where('customer.gender', $gender);
        }

        /**
         * 1、自动处理查询字段[表名 + 字段名 + 别名自动处理]
         * 2、自动处理可排序字段的情形
         */
        $this->setCustomizeColumnsOptions($Query, $act_member_info);
        // 字段排序以及没有排序的情况下设定一个默认排序字段
        if ($Query->getOptions('order') === null) {
            $Query->order('customer.id', 'DESC');
        }

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
