<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 12:08
 * @file MessageService.php
 */

namespace app\manage\service;

use app\common\helpers\GenerateHelper;
use app\common\model\MessageData;
use think\Request;

class MessageDataService
{
    /**
     * @var MessageData
     */
    public $MessageData;

    public function __construct(MessageData $messageData)
    {
        $this->MessageData = $messageData;
    }

    /**
     * 保存、编辑
     * @param Request $request
     * @throws
     * @return []
     */
    public function saveData(Request $request)
    {
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post('Message/a');
        if(empty($data['message']) || empty($data['message_type']))
        {
            return ['error_code' => -1,'error_msg' => '参数缺失'];
        }
        // 编辑
        if(!empty($data['id']))
        {
            $exist = $this->MessageData->find($data['id']);
            if(empty($exist))
            {
                return ['error_code' => -2,'error_msg' => '待编辑数据不存在'];
            }
        }
        $message = [];
        $message['message_type'] = $data['message_type'];
        $message['message'] = trim($data['message']);
        $message['remark'] = trim($data['remark']);
        $message['user_id'] = $user_id;
        $message['dept_id1'] = $dept1['dept_id'];
        $message['dept_id2'] = $dept2['dept_id'];

        if(isset($exist))
        {
            // 编辑
            $ret = $this->MessageData->isUpdate(true)->save($message,['id' => $exist['id']]);
        }else{
            // 新增
            $message['id'] = GenerateHelper::uuid();
            $ret = $this->MessageData->isUpdate(false)->save($message);
        }
        return $ret !== false ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -3,'error_msg' => '保存失败，写入数据异常'];
    }

    /**
     * 删除话术数据
     * @param Request $request
     * @throws
     * @return []
     */
    public function deleteMessageData(Request $request)
    {
        $message = $this->MessageData->getMessageDataById($request->post('id'));
        if(empty($message))
        {
            return ['error_code' => -1,'error_msg' => '未找到话术数据'];
        }
        $ret = $this->MessageData->where('id',$message['id'])->delete();
        return $ret ? ['error_code' => 0,'error_msg' => '话术数据已删除','data' => $message] : ['error_code' => -3,'error_msg' => '删除失败：数据库异常'];
    }
}
