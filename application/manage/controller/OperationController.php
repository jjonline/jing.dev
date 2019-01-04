<?php
/**
 * 通用操作记录控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-04 10:57
 * @file Operation.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\OperationRecordService;

class OperationController extends BaseController
{
    /**
     * ajax分页获取通用操作记录数据
     * ---
     * URL格式：/manage/operation/record?name=xxx&id=xxx&page=xxx
     * 请求方式：get
     * ---
     * @param OperationRecordService $operationRecordService
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recordAction(OperationRecordService $operationRecordService)
    {
        if ($this->request->isAjax()) {
            $operate_name = $this->request->get('name');
            $business_id  = $this->request->get('id');
            $result = $operationRecordService->getOperationRecordList($operate_name, $business_id);
            return $this->asJson(['error_code' => 0 ,'error_msg' => 'ok','data' => $result]);
        }
    }
}
