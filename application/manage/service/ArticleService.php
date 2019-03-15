<?php
/**
 * 图文文章服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-13 22:53:00
 * @file ArticleService.php
 */

namespace app\manage\service;

use app\manage\model\Article;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class ArticleService
{
    /**
     * @var Article
     */
    public $Article;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Article $article, LogService $logService)
    {
        $this->Article = $article;
        $this->LogService = $logService;
    }

    /**
     * 图文文章新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_article = $request->post('article/a');
            $is_edit = !empty($_article['id']);
            $article = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Article->getDataById($_article['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的文章数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->Article->isUpdate($is_edit)->save($article);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_article,$article],
                ($is_edit ? "编辑" : "新增")."图文文章"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 图文文章快速排序
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
            $article = $this->Article->getDataById($id);
            if (empty($article)) {
                throw new Exception('拟编辑排序的文章数据不存在');
            }
            $effect_rows = $this->Article->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $article,
                "图文文章快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 删除图文文章
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $article = $this->Article->getDataById($id);
            if (empty($article)) {
                throw new Exception('拟删除的文章数据不存在');
            }
            $effect_rows = $this->Article->db()->where('id', $id)->delete();
            if (false == $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $article,
                "删除文章"
            );
            return ['error_code' => 0, 'error_msg' => '已删除', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 启用|禁用文章
     * @param Request $request
     * @return array
     */
    public function enable(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $article = $this->Article->getDataById($id);
            if (empty($article)) {
                throw new Exception('拟启用或禁用的文章数据不存在');
            }

            $effect_rows = $this->Article->db()->where('id', $id)->update([
                'enable' => $article['enable'] ? 0 : 1
            ]);
            if (false == $effect_rows) {
                throw new Exception($article['enable'] ? '禁用失败：系统异常' : '启用失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $article,
                $article['enable'] ? '禁用文章' : '启用文章'
            );
            return ['error_code' => 0, 'error_msg' => $article['enable'] ? '已禁用文章' : '已启用文章', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }
}
