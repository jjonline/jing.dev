<?php
/**
 * 具有操作流程的操作记录表模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-23 14:38
 * @file OperationRecord.php
 */

namespace app\common\model;

use think\Model;

class OperationRecord extends Model
{
    /**
     * 通过操作流程名和业务ID获取操作记录
     * @param string $operate_name 操作流程名称，一般是对应业务的数据表表名
     * @param int    $business_id  具体的业务ID
     * @param string $order        获取记录的排序规则，默认按时间降序排列DESC，可传ASC升序
     * @return array
     * @throws \think\exception\DbException
     */
    public function getOperationRecordList($operate_name, $business_id, $order = 'DESC')
    {
        if (empty($operate_name) || empty($business_id)) {
            return [];
        }
        $order = $order == 'DESC' ? 'DESC' : 'ASC';
        $data  = $this->where(['operation_name' => $operate_name,'business_id' => $business_id])
               ->order('create_time', $order)
               ->paginate(10, false, [
                   'query' => [
                       'name' => $operate_name,
                       'id'   => $business_id,
                   ]
               ]);
        $paginate         = $data->render();
        $data             = $data->toArray();
        $data['paginate'] = $paginate;
        return $data;
    }
}
