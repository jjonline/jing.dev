<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 17:47
 * @file ChatDataService.php
 */

namespace app\manage\service;

use app\common\helpers\GenerateHelper;
use app\common\model\ChatData;
use app\common\model\ChatGroup;
use think\Request;
use SplFileObject;
use think\facade\Log;

class ChatDataService
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
     * 保存话术数据--新增或修改
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
        $data = $request->post('Chat/a',null,'trim');
        if(empty($data['send']) || empty($data['reply']))
        {
            return ['error_code' => -1,'error_msg' => '参数缺失'];
        }
        // 检查A-A类型
        if($data['send'] == $data['reply'])
        {
            return ['error_code' => -2,'error_msg' => '发出内容与回复内容不得相同'];
        }
        // 检查B-A类型是否存在
        $isReverse = $this->ChatData->isReverseChatData($data['send'],$data['reply'],$user_id);
        if($isReverse)
        {
            return ['error_code' => -2,'error_msg' => '发出内容与回复内容互换后的数据已存在，可能会导致死循环，请修改'];
        }
        // 检查分组ID
        $chat = [];
        if(!empty($data['chat_group_id']))
        {
            $group = $this->ChatGroup->getAuthChatGroupById($data['chat_group_id']);
            if(empty($group))
            {
                return ['error_code' => -2,'error_msg' => '互聊分组信息不存在'];
            }
            $chat['chat_group_id'] = $data['chat_group_id'];
        }
        if(!empty($data['id']))
        {
            $exist = $this->ChatData->find($data['id']);
            if(empty($exist))
            {
                return ['error_code' => -3,'error_msg' => '拟编辑互聊数据不存在'];
            }
        }

        $chat['send'] = $data['send'];
        $chat['reply'] = $data['reply'];
        $chat['remark'] = $data['remark'];
        $chat['user_id'] = $user_id;
        $chat['dept_id1'] = $dept1['dept_id'];
        $chat['dept_id2'] = $dept2['dept_id'];

        if(isset($exist))
        {
            // 编辑
            $ret = $this->ChatData->isUpdate(true)->save($chat,['id' => $data['id']]);
        }else {
            // 新增
            $chat['id'] = GenerateHelper::uuid();
            $ret = $this->ChatData->isUpdate(false)->save($chat);
        }
        return $ret !== false ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -3,'error_msg' => '保存失败，写入数据异常'];
    }

    /**
     * 删除互撩数据
     * @param Request $request
     * @throws
     * @return []
     */
    public function deleteChat(Request $request)
    {
        $id = $request->post('id');
        $chat = $this->ChatData->find($id);
        if(empty($chat))
        {
            return ['error_code' => -1,'error_msg' => '未找到互聊数据'];
        }
        $ret = $this->ChatData->where(['id' => $id])->delete();
        return $ret ? ['error_code' => 0,'error_msg' => '互聊数据已删除','data' => $chat->toArray()] : ['error_code' => -3,'error_msg' => '删除失败：数据库异常'];
    }

    /**
     * 批量导入互撩数据
     * @param $csv_file
     * @return array
     */
    public function importChatData($csv_file)
    {
        // sys setting
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ignore_user_abort();

        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $append  = [
            'user_id'  => $user_id,
            'dept_id1' => $dept1['dept_id'],
            'dept_id2' => $dept2['dept_id'],
        ];
        $step            = 1;
        $is_end          = false;
        $import_total    = 0;
        $file_line_total = $this->getCsvTotalLine($csv_file) - 1;
        while (!$is_end)
        {
            // 100行一个循环
            $data   = $this->getCsvLineData($csv_file,100,($step -1) * 100,$append);
            $is_end = $data[1];

            // 批量事务写入
            Log::record('写入MySqL开始，步骤数：'.$step."-".date_format(date_create(),'H:i:s u'));
            // 去除第一行标题
            if($step == 1)
            {
                unset($data[0][0]);
            }
            $ret = $this->ChatData->batchInsert($data[0],$user_id,$dept1['dept_id'],$dept2['dept_id']);
            if(!$ret)
            {
                return ['error_code' => -1,'error_msg' => '写入数据异常，导入失败'];
            }
            Log::record('写入MySqL结束，步骤数：'.$step."-".date_format(date_create(),'H:i:s u'));
            $import_total += count($data[0]);
            // 清理缓存
            unset($data);
            $step++;
        }
        return ['error_code' => 0,'error_msg' => '成功导入或更新'.$import_total.'条记录，csv文件有效行数：'.$file_line_total.'行'];
    }

    /**
     * 分片读取csv文件
     * @param string $file csv文件路径
     * @param int $lines   一次读取的行数
     * @param int $offset  偏移量
     * @param []  $append  添加进返回二维数组元素中的其他值
     * @throws
     * @return []
     */
    private function getCsvLineData($file, $lines, $offset = 0 ,$append = [])
    {
        $data   = [];
        $repeat = [];//检查本批次重复数组1
        $reverse_repeat = [];//检查本批次重复数组2
        $SplFileObject = new SplFileObject($file,'rb');
        $SplFileObject->seek($offset);
        while ($lines-- && !$SplFileObject->eof())
        {
            $_data  = $SplFileObject->fgets();
            $_data  = explode('----',$_data);
            if(count($_data) != 3)
            {
                continue;
            }
            if(empty($_data[0]) || empty($_data[1]))
            {
                continue;
            }
            // 本批数据去重A-B 和 B-A以及A-A
            if($_data[0] == $_data[1] || in_array($_data[0].$_data[1],$repeat) || in_array($_data[0].$_data[1],$reverse_repeat))
            {
                continue;
            }
            // 收集本批次数据检查重复
            $repeat[] = $_data[0].$_data[1];//无需转码
            $reverse_repeat[] = $_data[1].$_data[0];//无需转码

            // 转码并合并数组
            $data[]  = array_merge([
                'id'     => GenerateHelper::uuid(),
                'send'   => trim(mb_convert_encoding($_data[0],'UTF-8','GBK')),
                'reply'  => trim(mb_convert_encoding($_data[1],'UTF-8','GBK')),
                'remark'  => '批量csv导入',
                'group_name' => trim(mb_convert_encoding($_data[2],'UTF-8','GBK')) //分组名称
            ],$append);
            $SplFileObject->next();
        }
        // 0 => 插入的数据  2 => 是否已读取到文件末尾
        return [$data,$SplFileObject->eof()];
    }

    /**
     * 获取csv文件总行数
     * @param $file
     * @return int
     */
    private function getCsvTotalLine($file)
    {
        $SplFileObject = new SplFileObject($file,'rb');
        $SplFileObject->seek(filesize($file));
        return $SplFileObject->key();
    }

}
