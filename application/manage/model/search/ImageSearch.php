<?php
/**
 * 轮播图检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 21:23:00
 * @file ImageSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class ImageSearch extends BaseSearch
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
     * 前台轮播图搜索
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
        $Query = Db::name('image image')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   'image.id',
                   'image.tag',
                   'image.title',
                   'image.cover_id',
                   'attachment.file_path as cover',
                   'image.url',
                   'image.enable',
                   'image.remark',
                   'image.sort',
                   'image.create_time',
                   'image.update_time',
                   'image.remark'
               ])
               ->leftJoin('attachment attachment', 'attachment.id = image.cover_id');

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['image.tag','image.title','image.remark'];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        $enable = $this->request->param('adv_enable');
        if (in_array($enable, ['0','1'])) {
            $Query->where('image.enable', $enable);
        }

        // 时间范围检索
        $this->dateTimeSearch($Query, 'image.create_time');

        // 时间范围检索--更新时间
        $this->dateTimeSearch(
            $Query,
            'image.update_time',
            $this->request->param('update_time_begin'),
            $this->request->param('update_time_end')
        );

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'image');
        if ($Query->getOptions('order') === null) {
            $Query->order('image.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
