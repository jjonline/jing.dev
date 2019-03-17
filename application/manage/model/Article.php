<?php
/**
 * 图文文章模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-13 22:53:00
 * @file Article.php
 */

namespace app\manage\model;

use app\common\helper\AttachmentHelper;
use think\Model;

class Article extends Model
{
    /**
     * 主键查询
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->field(true)->where('id', $id)->find();
        return empty($result) ? [] : $result->toArray();
    }

    /**
     * 为编辑文章读取单条文章所有编辑需要的信息
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticle4EditById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->db()->alias('article')
            ->leftJoin('department dept', 'article.dept_id=dept.id')
            ->leftJoin('user user', 'article.user_id=user.id')
            ->leftJoin('article_cat article_cat', 'article.cat_id=article_cat.id')
            ->field([
                'article.*',
                'article_cat.name as article_cat_name',
                'dept.name as dept_name',
                'user.real_name',
            ])
            ->where('article.id', $id)
            ->find();
        if (empty($result)) {
            return [];
        }

        // 补充封面图
        $result['cover_path'] = '';
        if (!empty($result['cover_id'])) {
            $result['cover_path'] = AttachmentHelper::getAttachmentPathById($result['cover_id']);
        }

        return $result->toArray();
    }

    /**
     * 检查某文章分类下是否有文章
     * @param int $cat_id
     * @return bool
     */
    public function isArticleCatExistData($cat_id)
    {
        if (empty($cat_id)) {
            return false;
        }
        return !!$this->where('cat_id', $cat_id)->count();
    }
}
