<?php
/**
 * 网站会员服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file CustomerService.php
 */

namespace app\manage\service;

use app\manage\model\Customer;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class CustomerService
{
    /**
     * @var Customer
     */
    public $Customer;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Customer $customer, LogService $logService)
    {
        $this->Customer = $customer;
        $this->LogService = $logService;
    }

    /**
     * 网站会员新增|编辑
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_customer = $request->post('customer/a');
            $is_edit = !empty($_customer['id']);
            $customer = [];
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Customer->getDataById($_customer['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的网站会员数据不存在');
                }

            } else {
                // 新增模式

            }

            $effect_rows = $this->Customer->isUpdate($is_edit)->save($customer);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_customer,$customer],
                ($is_edit ? "编辑" : "新增")."网站会员"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 网站会员快速排序
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
            $customer = $this->Customer->getDataById($id);
            if (empty($customer)) {
                throw new Exception('拟编辑排序的网站会员数据不存在');
            }
            $effect_rows = $this->Customer->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $customer,
                "网站会员快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除网站会员
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $customer = $this->Customer->getDataById($id);
            if (empty($customer)) {
                throw new Exception('拟删除的网站会员数据不存在');
            }

            // todo 删除的其他检查

            $effect_rows = $this->Customer->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $customer,
                "删除网站会员"
            );
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
