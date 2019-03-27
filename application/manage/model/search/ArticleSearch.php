<?php
/**
 * 图文文章检索类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-13 22:53:00
 * @file ArticleSearch.php
 */

namespace app\manage\model\search;

use app\manage\model\ArticleCat;
use think\Db;

class ArticleSearch extends BaseSearch
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
     * 前台图文文章搜索
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
        $Query = Db::name('article article')
               ->field([
                   //'CONCAT("DT_Member_",member.id) as DT_RowId',
                   'article.id',
                   'article.title',
                   'article.cat_id',
                   'article_cat.name as article_cat',
                   'article.author',
                   'article.click',
                   'article.is_top',
                   'article.is_home',
                   'article.enable',
                   'article.sort',
                   'article.template',
                   'article.show_time',
                   'user.real_name',
                   'dept.name as dept_name',
                   'article.create_time',
                   'article.update_time',
                   'article.remark'
               ])
               ->leftJoin('article_cat article_cat', 'article_cat.id = article.cat_id')
               ->leftJoin('user user', 'user.id = article.user_id')
               ->leftJoin('department dept', 'dept.id = article.dept_id');

        // 部门检索 + 权限限制
        $this->permissionLimitOrDeptSearch(
            $Query,
            'article.dept_id',
            'article.user_id',
            $act_member_info
        );

        /**
         * 文章分类检索
         * ---
         * 即检索该自定的分类也检索该分类下的所有子分类
         */
        $article_cat = $this->request->param('article_cat');
        if (!empty($article_cat) && is_numeric($article_cat)) {
            $article_cats  = (new ArticleCat())->getChildArticleCatByParentId($article_cat);
            // 补充检索的分类ID本身
            $search_cats   = $article_cats ?: [];
            $search_cats[] = $article_cat;
            $Query->where('article.cat_id', 'IN', $search_cats);
        }

        // 指定用户检索
        $user_id = $this->request->param('user_id');
        if (!empty($user_id) && is_numeric($user_id)) {
            $Query->where('article.user_id', $user_id);
        }

        /**
         * 关键词搜索检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = [
            'article.title',
            'article.author',
            'article.source',
            'article.remark',
        ];
        $this->keywordSearch($Query, $search_columns, $this->keyword);

        // 禁用|启用状态
        $enable = $this->request->param('adv_enable');
        if (in_array($enable, ['0','1'])) {
            $Query->where('article.enable', $enable);
        }

        // is_home状态
        $is_home = $this->request->param('adv_home');
        if (in_array($is_home, ['0','1'])) {
            $Query->where('article.is_home', $is_home);
        }

        // adv_top状态
        $is_top = $this->request->param('adv_top');
        if (in_array($is_top, ['0','1'])) {
            $Query->where('article.is_top', $is_top);
        }

        // 时间范围检索--创建时间
        $this->dateTimeSearch($Query, 'article.create_time');

        // 时间范围检索--更新时间
        $this->dateTimeSearch(
            $Query,
            'article.update_time',
            $this->request->param('update_time_begin'),
            $this->request->param('update_time_end')
        );

        // 时间范围检索--显示时间
        $this->dateTimeSearch(
            $Query,
            'article.show_time',
            $this->request->param('show_time_begin'),
            $this->request->param('show_time_end')
        );

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query, 'article');
        if ($Query->getOptions('order') === null) {
            $Query->order('article.id', 'DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
