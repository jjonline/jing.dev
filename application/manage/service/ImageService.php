<?php
/**
 * 轮播图服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 21:23:00
 * @file ImageService.php
 */

namespace app\manage\service;

use app\common\helper\FilterValidHelper;
use app\manage\model\Image;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class ImageService
{
    /**
     * @var Image
     */
    public $Image;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Image $image, LogService $logService)
    {
        $this->Image = $image;
        $this->LogService = $logService;
    }

    /**
     * 轮播图新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_image = $request->post('Image/a');
            if (empty($_image['title']) || mb_strlen($_image['title']) > 32) {
                throw new Exception('标题不得为空或大于32字符', 400);
            }
            if (empty($_image['tag']) || mb_strlen($_image['tag']) > 64) {
                throw new Exception('标题不得为空或大于64字符', 400);
            }
            if (empty($_image['cover_id']) || strlen($_image['cover_id']) != 36) {
                throw new Exception('请上传轮播图', 400);
            }
            if (!empty($_image['url']) && !FilterValidHelper::is_url_valid($_image['url'])) {
                throw new Exception('链接必须是完整的网址', 400);
            }

            $is_edit = !empty($_image['id']);
            $image   = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Image->getDataById($_image['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的轮播图数据不存在');
                }
                $image['id']   = $_image['id'];
            }
            $image['tag']      = $_image['tag'];
            $image['title']    = $_image['title'];
            $image['cover_id'] = $_image['cover_id'];
            $image['url']      = $_image['url'] ?? '';
            $image['remark']   = $_image['remark'] ?? '';
            $image['sort']     = intval($_image['sort']);
            $image['enable']   = empty($_image['enable']) ? 0 : 1;

            $effect_rows = $this->Image->isUpdate($is_edit)->save($image);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_image,$image],
                ($is_edit ? "编辑" : "新增")."轮播图"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 轮播图快速排序
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
            $image = $this->Image->getDataById($id);
            if (empty($image)) {
                throw new Exception('拟编辑排序的轮播图数据不存在');
            }
            $effect_rows = $this->Image->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $image,
                "轮播图快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除轮播图
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $image = $this->Image->getDataById($id);
            if (empty($image)) {
                throw new Exception('拟删除的轮播图数据不存在');
            }
            $effect_rows = $this->Image->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $image,
                "删除轮播图"
            );
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 启用|禁用
     * @param Request $request
     * @return array
     */
    public function enable(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $article = $this->Image->getDataById($id);
            if (empty($article)) {
                throw new Exception('拟启用或禁用的轮播图数据不存在');
            }

            $effect_rows = $this->Image->db()->where('id', $id)->update([
                'enable' => $article['enable'] ? 0 : 1
            ]);
            if (false == $effect_rows) {
                throw new Exception($article['enable'] ? '禁用失败：系统异常' : '启用失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $article,
                $article['enable'] ? '禁用轮播图' : '启用轮播图章'
            );
            return ['error_code' => 0, 'error_msg' => $article['enable'] ? '已禁用轮播图' : '已启用轮播图', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }
}
