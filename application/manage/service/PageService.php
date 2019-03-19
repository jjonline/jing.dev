<?php
/**
 * 网站单页服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file PageService.php
 */

namespace app\manage\service;

use app\manage\model\Page;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class PageService
{
    /**
     * @var Page
     */
    public $Page;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Page $page, LogService $logService)
    {
        $this->Page = $page;
        $this->LogService = $logService;
    }

    /**
     * 单页新增|编辑config的设置参数
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_page = $request->post('page/a');
            $is_edit = !empty($_page['id']);
            $page = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Page->getDataById($_page['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的网站单页数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->Page->isUpdate($is_edit)->save($page);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_page,$page],
                ($is_edit ? "编辑" : "新增")."网站单页"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 提交单页面设置数据
     * @param Request $request
     * @return array
     */
    public function setting(Request $request)
    {
        try {
            $request->post('Setting/a');

            return [];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 网站单页快速排序
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
            $page = $this->Page->getDataById($id);
            if (empty($page)) {
                throw new Exception('拟编辑排序的网站单页数据不存在');
            }
            $effect_rows = $this->Page->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $page,
                "网站单页快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除网站单页
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id   = $request->post('id/i');
            $page = $this->Page->getDataById($id);
            if (empty($page)) {
                throw new Exception('拟删除的网站单页数据不存在');
            }

            $effect_rows = $this->Page->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $page,
                "删除网站单页"
            );
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
