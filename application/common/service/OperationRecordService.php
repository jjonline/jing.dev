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

    public function __construct(OperationRecord $operationRecord , User $user)
    {
        $this->User            = $user;
        $this->OperationRecord = $operationRecord;
    }

    /**
     * 通过操作流程名和业务ID获取操作记录
     * @param string $operate_name 操作流程名称，一般是对应业务的数据表表名
     * @param int    $business_id  具体的业务ID
     * @param string $order        获取记录的排序规则，默认按时间降序排列DESC，可传ASC升序
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOperationRecordList($operate_name,$business_id,$order = 'DESC')
    {
        return $this->OperationRecord->getOperationRecordList($operate_name,$business_id,$order);
    }

    /**
     * 新增具有操作流程的操作记录
     * @param string $operation_name 操作流程的标识，一般使用具体的业务数据表的完整表名称
     * @param int    $business_id    操作流程的业务ID，例如：采购单的业务流程记录时该ID即为采购单ID
     * @param string $title          操作流程的操作简要说明标题
     * @param string $desc           操作流程的操作说明
     * @param int|null $operate_user_id 可选的操作者用户ID，默认不传则取当前登录用户信息
     * @return bool
     * @throws Exception
     */
    public function save($operation_name,$business_id,$title,$desc,$operate_user_id = null)
    {
        if(empty($operation_name) || empty($business_id) || empty($title) || empty($desc))
        {
            throw new Exception('参数错误：第1个参数操作流程标识，第2个参数业务ID，第3个参数业务标题，第4个参数业务描述');
        }
        if(!empty($operate_user_id))
        {
            $user = $this->User->getFullUserInfoById($operate_user_id);
            if(empty($user))
            {
                throw new Exception('第5个参数指定的用户ID标识的用户不存在');
            }
        }else {
            $user = Session::get('user_info');
        }
        if(mb_strlen($operation_name,'utf-8') > 64)
        {
            throw new Exception('第1个参数操作简要标题不得操作64字');
        }
        if(mb_strlen($desc,'utf-8') > 512)
        {
            throw new Exception('第4个参数操作流程的说明不得大于512字');
        }
        // 构造记录数组
        $operate                       = [];
        $operate['operation_name']     = $operation_name;
        $operate['business_id']        = $business_id;
        $operate['title']              = trim($title);
        $operate['desc']               = trim($desc);
        $operate['creator']            = $user['id'];
        $operate['creator_name']       = $user['real_name'];
        $operate['creator_dept_id']    = $user['dept_id'];
        $operate['creator_dept_name']  = $user['dept_name'];

        $ret = $this->OperationRecord->db()->insert($operate);

        return !!$ret;
    }

}
