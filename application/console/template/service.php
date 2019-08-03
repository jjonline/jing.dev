<?php
/**
 * __LIST_NAME__服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date __CREATE_TIME__
 * @file __CONTROLLER__Service.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\manage\model\__CONTROLLER__;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class __CONTROLLER__Service
{
    /**
     * @var __CONTROLLER__
     */
    public $__CONTROLLER__;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(__CONTROLLER__ $__CONTROLLER_LOWER__, LogService $logService)
    {
        $this->__CONTROLLER__ = $__CONTROLLER_LOWER__;
        $this->LogService = $logService;
    }

    /**
     * __LIST_NAME__新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $___CONTROLLER_LOWER__ = $request->post('__CONTROLLER_LOWER__/a');
            $is_edit = !empty($___CONTROLLER_LOWER__['id']);
            $__CONTROLLER_LOWER__ = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->__CONTROLLER__->getDataById($___CONTROLLER_LOWER__['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的__LIST_NAME__数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->__CONTROLLER__->isUpdate($is_edit)->save($__CONTROLLER_LOWER__);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$___CONTROLLER_LOWER__,$__CONTROLLER_LOWER__],
                ($is_edit ? "编辑" : "新增")."__LIST_NAME__"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * __LIST_NAME__快速排序
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
            $__CONTROLLER_LOWER__ = $this->__CONTROLLER__->getDataById($id);
            if (empty($__CONTROLLER_LOWER__)) {
                throw new Exception('拟编辑排序的__LIST_NAME__数据不存在');
            }
            $effect_rows = $this->__CONTROLLER__->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $__CONTROLLER_LOWER__,
                "__LIST_NAME__快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除__LIST_NAME__[单个-->id，批量-->multi_id]
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            // 单个
            $id = $request->post('id/i');
            if (!empty($id)) {
                $__CONTROLLER_LOWER__ = $this->__CONTROLLER__->getDataById($id);
                if (empty($__CONTROLLER_LOWER__)) {
                    throw new Exception('拟删除的__LIST_NAME__数据不存在');
                }

                // todo 删除的其他检查

                $effect_rows = $this->__CONTROLLER__->db()->where('id', $id)->delete();
                if (false === $effect_rows) {
                    throw new Exception('删除操作失败：系统异常');
                }
                // 记录日志
                $this->LogService->logRecorder(
                    $__CONTROLLER_LOWER__,
                    "删除__LIST_NAME__"
                );
            } else {
                $multi_id_array = $request->post('multi_id/a');
                if (empty($multi_id_array)) {
                    throw new Exception('批量参数有误删除失败');
                }
                $effect_rows = $this->__CONTROLLER__->db()
                    ->where('id', 'in', ArrayHelper::filterArrayThenUnique($multi_id_array))
                    ->delete();
                if (false === $effect_rows) {
                    throw new Exception('删除操作失败：系统异常');
                }
            }
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
