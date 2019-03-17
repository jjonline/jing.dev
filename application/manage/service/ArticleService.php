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
use app\manage\model\ArticleCat;
use app\manage\model\Tag;
use app\manage\model\User;
use think\Exception;
use think\facade\Session;
use think\Request;

class ArticleService
{
    /**
     * @var Article
     */
    public $Article;
    /**
     * @var ArticleCat
     */
    public $ArticleCat;
    /**
     * @var Tag
     */
    public $Tag;
    /**
     * @var User
     */
    public $User;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(
        Article $article,
        ArticleCat $articleCat,
        Tag $tag,
        User $user,
        LogService $logService
    ) {
        $this->Article    = $article;
        $this->ArticleCat = $articleCat;
        $this->Tag        = $tag;
        $this->User       = $user;
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
            $_article = $request->post('Article/a');
            if (empty($_article['title']) || mb_strlen($_article['title']) > 128) {
                throw new Exception('标题不得为空或大于128字符');
            }
            if (empty($_article['excerpt']) || mb_strlen($_article['excerpt']) > 140) {
                throw new Exception('摘要不得为空或大于140字符');
            }
            if (empty($_article['content']) || mb_strlen($_article['content']) > 65535) {
                throw new Exception('内容不得为空或大于6万个字符');
            }
            if (!empty($_article['remark']) && mb_strlen($_article['remark']) > 255) {
                throw new Exception('备注不得大于255字符');
            }
            if (!empty($_article['author']) && mb_strlen($_article['author']) > 32) {
                throw new Exception('作者不得大于32字符');
            }
            if (!empty($_article['source']) && mb_strlen($_article['source']) > 128) {
                throw new Exception('来源不得大于128字符');
            }
            if (!empty($_article['template']) && mb_strlen($_article['template']) > 32) {
                throw new Exception('自定义模板不得大于32字符');
            }
            // 检查文章分类
            $exist_cat = $this->ArticleCat->getDataById($_article['cat_id']);
            if (empty($exist_cat)) {
                throw new Exception('所选文章分类不存在');
            }

            $is_edit = !empty($_article['id']);
            $article = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Article->getDataById($_article['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的文章数据不存在');
                }
                $article['id'] = $_article['id'];
            } else {
                // 新增模式 补充默认的创建者和所属部门
                $article['user_id'] = Session::get('user_info.id');
                $article['dept_id'] = Session::get('user_info.dept_id');
            }

            // 依据是否设置创建人变更文章创建者和所属部门
            if (!empty($_article['create_user_id'])) {
                /**
                 * 这里暂时不检查当前处理用户是否有权限指定创建人，记录下日志
                 */
                $assign_user = $this->User->getUserById($_article['create_user_id']);
                if (!empty($assign_user)) {
                    $article['user_id'] = $assign_user['id'];
                    $article['dept_id'] = $assign_user['dept_id'];
                    $this->LogService->logRecorder($assign_user, ($is_edit ? "编辑" : "新增")."文章指定创建人");
                }
            }

            // 构造文章数据
            $article['title']    = $_article['title'];
            $article['cat_id']   = $_article['cat_id'];
            $article['cover_id'] = $_article['cover_id'] ?: '';
            $article['excerpt']  = $_article['excerpt'];
            $article['content']  = $_article['content'];
            $article['author']   = $_article['author'];
            $article['source']   = $_article['source'];
            $article['template'] = $_article['template'];
            $article['remark']   = $_article['remark'];
            $article['click']    = $_article['click'] ? intval($_article['click']) : 0;
            $article['enable']   = empty($_article['enable']) ? 1 : 0;
            $article['is_home']  = empty($_article['is_home']) ? 0 : 1;
            $article['is_top']   = empty($_article['is_top']) ? 0 : 1;

            // 处理tags关键词
            if (empty($_article['tags'])) {
                $article['tags'] = '';
            } else {
                // 智能读取或新增tag并返回半角逗号分隔的文章tag字段值
                $article['tags'] = $this->Tag->autoSaveTags($_article['tags'], $is_edit);
            }

            $effect_rows = $this->Article->isUpdate($is_edit)->save($article);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_article,$article],
                ($is_edit ? "编辑" : "新增")."文章"
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

    /**
     * 有权限的读取文章
     * @param int $id
     * @param array $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAuthArticleById($id, $act_user_info)
    {
        $article = $this->Article->getArticle4EditById($id);
        if (empty($article)) {
            return [];
        }
        // 补充关键词信息--统一转换为数组
        if (!empty($article['tags'])) {
            $tags = $this->Tag->db()->where('id', 'IN', $article['tags'])->column('tag');
            $article['tags'] = $tags;
        } else {
            $article['tags'] = [];
        }
        /**
         * 所属用户对应或根用户直接返回
         */
        if ($act_user_info['id'] == $article['user_id'] || $act_user_info['is_root']) {
            return $article;
        }
        /**
         * 属于下辖部门
         */
        if (in_array($article['dept_id'], $act_user_info['dept_auth']['dept_id_vector'])) {
            return $article;
        }
        return [];
    }
}
