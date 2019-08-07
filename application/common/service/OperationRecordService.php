<?php
/**
 * 具有操作流程的操作记录服务
 * ---
 * 1、采购单操作记录
 * 2、退货单操作记录
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-23 14:28
 * @file ProcessRecordService.php
 */

namespace app\common\service;

use app\common\model\User;
use app\common\model\OperationRecord;
use think\Exception;
use think\facade\Session;

class OperationRecordService
{
    /**
     * @var OperationRecord
     */
    public $OperationRecord;
    /**
     * @var User
     */
    public $User;

    public function __construct(OperationRecord $operationRecord, User $user)
    {
        $this->User            = $user;
        $this->OperationRecord = $operationRecord;
    }

    /**
     * 通过操作流程名和业务ID获取操作记录
     * @param string $business_type 操作标识，一般使用操作对应的数据表名称即可
     * @param int    $business_id   操作对应数据表的数据的id
     * @param string $order         获取记录的排序规则，默认按时间降序排列DESC，可传ASC升序
     * @return array
     * @throws \think\exception\DbException
     */
    public function getOperationRecordList($business_type, $business_id, $order = 'DESC')
    {
        return $this->OperationRecord->getOperationRecordList($business_type, $business_id, $order);
    }

    /**
     * 新增操作日志
     * @param string  $operation_name 操作简要名称
     * @param string  $operation_desc 操作较详细描述
     * @param string  $business_type  操作标识，一般使用操作对应的数据表名称即可
     * @param integer $business_id    操作对应数据表的数据的id
     * @param mixed   $business_param 操作需要额外存储的数据，数据或标量值
     * @param integer|null $operate_user_id 操作者用户id
     * @return bool
     * @throws Exception
     */
    public function save(
        $operation_name,
        $operation_desc,
        $business_type,
        $business_id,
        $business_param = null,
        $operate_user_id = null
    ) {
        if (empty($operation_name) || empty($business_type) || empty($business_id)) {
            throw new Exception('参数错误：第1个参数操作简要描述，第2个参数操作较详细描述，第3个参数操作标识，第4个参数操作对应数据主键id，第5个参数需要额外存储的数据，第6个可选参数用户id');
        }
        if (!empty($operate_user_id)) {
            $user = $this->User->getFullUserInfoById($operate_user_id);
            if (empty($user)) {
                throw new Exception('第6个参数指定的用户ID标识的用户不存在');
            }
        } else {
            $user = Session::get('user_info');
        }
        if (mb_strlen($operation_name, 'utf-8') > 64) {
            throw new Exception('第1个参数操作简要描述不得操作64字');
        }
        if (mb_strlen($operation_desc, 'utf-8') > 512) {
            throw new Exception('第2个参数操作较详细描述不得大于512字');
        }
        if (strlen($business_type) > 64) {
            throw new Exception('第3个参数操作标识不得大于64字符');
        }
        if (!is_numeric($business_id)) {
            throw new Exception('第4个参数操作对应数据主键id必须是整数');
        }
        // 构造记录数组
        $operate                       = [];
        $operate['operation_name']     = trim($operation_name);
        $operate['operation_desc']     = trim($operation_desc);
        $operate['business_type']      = $business_type;
        $operate['business_id']        = $business_id;
        $operate['business_param']     = $business_param;
        $operate['creator_id']         = $user['id'];
        $operate['creator_name']       = $user['real_name'];

        $ret = $this->OperationRecord->db()->insert($operate);

        return !!$ret;
    }
}
