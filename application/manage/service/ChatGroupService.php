<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 20:59
 * @file ChatGroupService.php
 */

namespace app\manage\service;
use app\common\helpers\GenerateHelper;
use app\common\model\ChatData;
use app\common\model\ChatGroup;
use think\Request;

class ChatGroupService
{
    /**
     * @var ChatData
     */
    public $ChatData;
    /**
     * @var ChatGroup
     */
    public $ChatGroup;

    public function __construct(ChatData $chatData , ChatGroup $chatGroup)
    {
        $this->ChatGroup = $chatGroup;
        $this->ChatData  = $chatData;
    }

    /**
     * 保存互撩分组
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
        $data = $request->post('Group/a',null,'trim');
        if(empty($data['name']))
        {
            return ['error_code' => -1,'error_msg' => '参数缺失'];
        }
        // 分组名称是否重复
        $is_repeat = $this->ChatGroup->isUserChatGroupRepeat($user_id,$data['name']);
        // 编辑模式
        if(!empty($data['id']))
        {
            $exist = $this->ChatGroup->find($data['id']);
            if(empty($exist) || $exist['user_id'] != $user_id)
            {
                return ['error_code' => -1,'error_msg' => '拟编辑的分组不存在或你无权限编辑该分组'];
            }
            if($data['name'] != $exist['name'])
            {
                if($is_repeat)
                {
                    return ['error_code' => -1,'error_msg' => '修改后的分组名称重复'];
                }
            }
        }else {
            if($is_repeat)
            {
                return ['error_code' => -1,'error_msg' => '分组名称重复'];
            }
        }
        $group           = [];
        $group['name']   = $data['name'];
        $group['remark'] = $data['remark'];
        $group['dept_id1'] = $dept1['dept_id'];
        $group['dept_id2'] = $dept2['dept_id'];
        $group['sort']     = intval($data['sort']) < 0 ? 0 : intval($data['sort']);
        $group['user_id']  = $user_id;

        if(!empty($data['id']))
        {
            // 修改
            $ret = $this->ChatGroup->isUpdate(true)->save($group,['id' => $data['id']]);
        }else {
            // 新增
            $group['id'] = GenerateHelper::uuid();
            $ret = $this->ChatGroup->isUpdate(false)->save($group);
        }
        return false !== $ret ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -1,'error_msg' => '写入数据异常'];
    }

    /**
     * 删除互撩分组
     * @param Request $request
     * @throws
     * @return []
     */
    public function deleteChatGroup(Request $request)
    {
        $group = $this->ChatGroup->getChatGroupById($request->post('id'));
        if(empty($group))
        {
            return ['error_code' => -1,'error_msg' => '互聊分组数据不存在'];
        }
        // 检测该互聊分组下是否有互聊数据
        $hasChats = $this->ChatData->where(['chat_group_id' => $group['id']])->count();
        if($hasChats > 0)
        {
            return ['error_code' => -1,'error_msg' => '该互聊分组下存在互聊数据，无法删除'];
        }
        $ret = $this->ChatGroup->where('id',$group['id'])->delete();
        return $ret ? ['error_code' => 0,'error_msg' => '互聊分组已删除','data' => $group] : ['error_code' => -3,'error_msg' => '删除失败：数据库异常'];
    }

}
