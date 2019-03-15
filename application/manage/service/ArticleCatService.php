<?php
/**
 * 文章分类服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-15 21:32:00
 * @file ArticleCatService.php
 */

namespace app\manage\service;

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
     * @var LogService
     */
    public $LogService;
    /**
     * @var string 文章分类缓存tag
     */
    public $CacheTag = 'Article.Cat.Tag';

    public function __construct(ArticleCat $articleCat, LogService $logService)
    {
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
            $_articleCat = $request->post('articleCat/a');
            $is_edit = !empty($_articleCat['id']);
            $articleCat = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->ArticleCat->getDataById($_articleCat['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的文章分类数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->ArticleCat->isUpdate($is_edit)->save($articleCat);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_articleCat,$articleCat],
                ($is_edit ? "编辑" : "新增")."文章分类"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
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
            return ['error_code' => 0, 'error_msg' => '排序调整成功', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
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

            // todo 删除的其他检查

            $effect_rows = $this->ArticleCat->db()->where('id', $id)->delete();
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $articleCat,
                "删除文章分类"
            );
            return ['error_code' => 0, 'error_msg' => '已删除', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }
}
