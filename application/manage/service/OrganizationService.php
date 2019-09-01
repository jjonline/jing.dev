<?php
/**
 * 组织部门服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 19:58:00
 * @file OrganizationService.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\manage\model\Organization;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class OrganizationService
{
    /**
     * @var Organization
     */
    public $Organization;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Organization $organization, LogService $logService)
    {
        $this->Organization = $organization;
        $this->LogService = $logService;
    }

    /**
     * 组织部门新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_organization = $request->post('organization/a');
            $is_edit = !empty($_organization['id']);
            $organization = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Organization->getDataById($_organization['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的组织部门数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->Organization->isUpdate($is_edit)->save($organization);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_organization,$organization],
                ($is_edit ? "编辑" : "新增")."组织部门"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 组织部门快速排序
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
            $organization = $this->Organization->getDataById($id);
            if (empty($organization)) {
                throw new Exception('拟编辑排序的组织部门数据不存在');
            }
            $effect_rows = $this->Organization->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $organization,
                "组织部门快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除组织部门[单个-->id，批量-->multi_id]
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            // 单个
            $id = $request->post('id/i');
            if (!empty($id)) {
                $organization = $this->Organization->getDataById($id);
                if (empty($organization)) {
                    throw new Exception('拟删除的组织部门数据不存在');
                }

                // todo 删除的其他检查

                $effect_rows = $this->Organization->db()->where('id', $id)->delete();
                if (false === $effect_rows) {
                    throw new Exception('删除操作失败：系统异常');
                }
                // 记录日志
                $this->LogService->logRecorder(
                    $organization,
                    "删除组织部门"
                );
            } else {
                $multi_id_array = $request->post('multi_id/a');
                if (empty($multi_id_array)) {
                    throw new Exception('批量参数有误删除失败');
                }
                $effect_rows = $this->Organization->db()
                    ->where('id', 'in', ArrayHelper::filterByCallableThenUnique($multi_id_array, 'intval'))
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
