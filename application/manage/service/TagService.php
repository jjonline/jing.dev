<?php
/**
 * 关键词服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-16 17:57:00
 * @file TagService.php
 */

namespace app\manage\service;

use app\manage\model\Tag;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class TagService
{
    /**
     * @var Tag
     */
    public $Tag;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Tag $tag, LogService $logService)
    {
        $this->Tag = $tag;
        $this->LogService = $logService;
    }

    /**
     * 关键词新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_tag = $request->post('tag/a');
            $is_edit = !empty($_tag['id']);
            $tag = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Tag->getDataById($_tag['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的关键词数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->Tag->isUpdate($is_edit)->save($tag);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_tag,$tag],
                ($is_edit ? "编辑" : "新增")."关键词"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 关键词快速排序
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
            $tag = $this->Tag->getDataById($id);
            if (empty($tag)) {
                throw new Exception('拟编辑排序的关键词数据不存在');
            }
            $effect_rows = $this->Tag->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $tag,
                "关键词快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除关键词
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $tag = $this->Tag->getDataById($id);
            if (empty($tag)) {
                throw new Exception('拟删除的关键词数据不存在');
            }

            // todo 删除的其他检查

            $effect_rows = $this->Tag->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $tag,
                "删除关键词"
            );
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 关键词自动存储读取
     * @param string $tag tag1|tag2 形式的多个关键词
     * @param bool $is_update 使用tag的文章、单独页等是否处于更新模式
     * @return string 1,3,5 形式的字符串
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function autoSaveTags($tags_str, $is_update = false)
    {
        return $this->Tag->autoSaveTags($tags_str, $is_update);
    }

    /**
     * 检索关键词
     * @param $keyword
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchTagList($keyword, $act_user_info)
    {
        if (empty($act_user_info) || empty($act_user_info['dept_auth'])) {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        if (empty($keyword)) {
            $data = $this->Tag->db()->alias('tag')
                ->leftJoin('user user', 'user.id = tag.user_id')
                ->order('tag.quota', 'DESC')
                ->order('tag.id', 'DESC')
                ->field([
                    'tag.id',
                    'user.real_name',
                    'tag.tag',
                    'tag.quota',
                    'tag.create_time'
                ])
                ->limit(20)
                ->select();
            return ['error_code' => 0,'error_msg'   => '请求成功','data' => $data];
        }
        $data = $this->Tag->db()->alias('tag')
            ->leftJoin('user user', 'user.id = tag.user_id')
            ->order('tag.quota', 'DESC')
            ->order('tag.id', 'DESC')
            ->field([
                'tag.id',
                'user.real_name',
                'tag.tag',
                'tag.quota',
                'tag.create_time'
            ])
            ->where('tag.tag|tag.id', 'LIKE', '%'.$keyword.'%')
            ->limit(20)
            ->select();
        return ['error_code' => 0,'error_msg' => '请求成功','data' => $data];
    }
}
