<?php
/**
 * 文章分类服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-15 21:32:00
 * @file ArticleCatService.php
 */

namespace app\manage\service;

use app\manage\model\Article;
use app\manage\model\ArticleCat;
use app\common\service\LogService;
use think\facade\Config;
use think\Exception;
use think\facade\Cache;
use think\Request;

class ArticleCatService
{
    /**
     * @var ArticleCat
     */
    public $ArticleCat;
    /**
     * @var Article
     */
    public $Article;
    /**
     * @var LogService
     */
    public $LogService;
    /**
     * @var string 文章分类缓存tag
     */
    public $CacheTag = 'Article.Cat.Tag';

    public function __construct(Article $article, ArticleCat $articleCat, LogService $logService)
    {
        $this->Article    = $article;
        $this->ArticleCat = $articleCat;
        $this->LogService = $logService;
    }

    /**
     * 依据开发模式与否的带缓存的获取菜单列表tree
     * @return array
     */
    public function getArticleCatTreeList()
    {
        try {
            if (!Config::get('app.app_debug')) {
                $article_cats = Cache::get('All_Article_Cat');
                if (!empty($article_cats)) {
                    return $article_cats;
                }
            }
            $article_cats = $this->ArticleCat->getArticleCatListTree();
            if (!Config::get('app.app_debug')) {
                Cache::tag($this->CacheTag)->set('All_Article_Cat', $article_cats);
            }
            return $article_cats;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 文章分类新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_articleCat = $request->post('ArticleCat/a');
            if (empty($_articleCat['name']) || $_articleCat['parent_id'] == -1) {
                throw new Exception('上级分类或分类名称缺失');
            }
            $is_edit = !empty($_articleCat['id']);
            if ($is_edit) {
                $exist_cat = $this->ArticleCat->getDataById($_articleCat['id']);
                if (empty($exist_cat)) {
                    throw new Exception('拟编辑分类不存在');
                }
            }
            // 构造数据
            $ArticleCat           = [];
            $ArticleCat['name']   = trim($_articleCat['name']);
            $ArticleCat['sort']   = intval($_articleCat['sort']) < 0 ? 1 : intval($_articleCat['sort']);
            $ArticleCat['icon']   = trim($_articleCat['icon']);
            $ArticleCat['remark'] = trim($_articleCat['remark']);
            $ArticleCat['level']  = 1;
            // 处理上级分类和层级
            if ($_articleCat['parent_id'] != 0) {
                $parent_articleCat= $this->ArticleCat->getDataById($_articleCat['parent_id']);
                if (empty($parent_articleCat)) {
                    throw new Exception('所选上级分类不存在');
                }
                if ($parent_articleCat['level'] >= 3) {
                    throw new Exception('分类最大允许3级');
                }
                $ArticleCat['level']     = $parent_articleCat['level'] + 1;
                $ArticleCat['parent_id'] = $parent_articleCat['id'];
            }
            if ($is_edit) {
                $ArticleCat['id'] = $_articleCat['id'];
                $result = $this->ArticleCat->isUpdate(true)->data($ArticleCat)->save();
            } else {
                $result = $this->ArticleCat->isUpdate(false)->data($ArticleCat)->save();
            }
            if (false !== $result) {
                Cache::clear($this->CacheTag);// 按标签清理分类缓存
                $this->LogService->logRecorder($ArticleCat, $is_edit ? '编辑文章分类' :'新增文章分类');
                return ['error_code' => 0,'error_msg'   => '分类保存成功'];
            }
            return ['error_code' => 500,'error_msg' => '分类保存失败：系统异常'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 文章分类快速排序
     * @param Request $request
     * @return array
     */
    public function sort(Request $request)
    {
        try {
            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }

            $articleCat = $this->ArticleCat->getDataById($id);
            if (empty($articleCat)) {
                throw new Exception('拟编辑排序的文章分类数据不存在');
            }

            $effect_rows = $this->ArticleCat->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $articleCat,
                "文章分类快速排序"
            );
            Cache::clear($this->CacheTag);// 按标签清理分类缓存
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除文章分类
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $articleCat = $this->ArticleCat->getDataById($id);
            if (empty($articleCat)) {
                throw new Exception('拟删除的文章分类数据不存在');
            }

            // 检查该分类是否有文章
            if ($this->Article->isArticleCatExistData($id)) {
                throw new Exception('拟删除的文章分类已有文章数据，确需删除本分类请先删除该分类下的所有文章');
            }

            $effect_rows = $this->ArticleCat->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除文章分类失败：系统异常');
            }

            // 记录日志
            $this->LogService->logRecorder(
                $articleCat,
                "删除文章分类"
            );
            Cache::clear($this->CacheTag);// 按标签清理分类缓存
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
