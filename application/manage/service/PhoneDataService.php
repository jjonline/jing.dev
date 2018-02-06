<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-19 17:05
 * @file PhoneDataService.php
 */

namespace app\manage\service;

use app\common\helpers\FilterValidHelper;
use app\common\helpers\GenerateHelper;
use app\manage\model\User;
use SplFileObject;
use app\common\model\PhoneData;
use think\Exception;
use think\facade\Log;
use think\Request;

class PhoneDataService
{
    /**
     * @var PhoneData
     */
    public $PhoneData;
    /**
     * @var User
     */
    public $User;

    public function __construct(PhoneData $phoneData,User $user)
    {
        $this->PhoneData = $phoneData;
        $this->User = $user;
    }

    /**
     * 管理员分配待加手机号给业务员
     * @param Request $request
     * @return []
     */
    public function allocationPhoneData(Request $request)
    {
        $dept1 = session('default_dept1');
        if(empty($dept1))
        {
            return ['error_code' => -1,'error_msg' => '系统异常'];
        }
        $dept_id1 = $dept1['dept_id'];
        // 1指定数量分配、2指定数量平均分配
        $type  = $request->post('type/i');
        $users = $request->post('users/a');
        $num   = $request->post('number/i');
        if(empty($type) || empty($users) || empty($num))
        {
            return ['error_code' => -2,'error_msg' => '参数错误'];
        }
        // 计算需分配总数
        $need_allocation_total = 0;
        $person_count = count($users);
        if($type == 1)
        {
            // 指定分配，每个人分配$num个
            $need_allocation_total = $num * $person_count;
        }else{
            // 平局分配，所有人均分$num个
            $need_allocation_total = $num;
        }
        // 检查待分配数据是否足够用于分配
        $UnAllocationCount = $this->PhoneData->getTotalUnAllocationByDeptId1($dept_id1);
        if($need_allocation_total < 0 || $need_allocation_total > $UnAllocationCount)
        {
            return ['error_code' => -3,'error_msg' => '未分配手机号数量不够'];
        }

        // 检查用户信息，生成分配信息
        $per_count = floor($need_allocation_total / $person_count);//向下取整计算每个人分配的数目
        // 平均分的情况不能整除的问题，直接提示，转换为字符串比较避免float和int不等的问题
        if(strval($per_count * $person_count) != strval($need_allocation_total))
        {
            return ['error_code' => -4,'error_msg' => '平均分配不够整除，系统无法处理请调整'];
        }

        // 检查待分配业务员信息，并生成每个业务员的分配信息
        $allocation_info = [];
        foreach ($users as $user_id)
        {
            $user = $this->User->getFullUserInfoById($user_id);
            if(empty($user) || empty($user['department']) || count($user['department']) > 1)
            {
                return ['error_code' => -1,'error_msg' => '待添加手机号只能分配给业务员'];
            }
            $_info = [
                'user_id'  => $user_id,
                'dept_id2' => $user['department'][0]['dept_id2']
            ];
            $allocation_info[] = $_info;
        }
        $ret = $this->PhoneData->allocatePhoneData($allocation_info,$per_count,$dept_id1);

        return $ret ? ['error_code' => 0,'error_msg' => '分配成功'] : ['error_code' => -5,'error_msg' => '系统异常，分配失败'];
    }

    /**
     * 批量导入手机号数据
     * @param $csv_file
     * @return []
     */
    public function importPhoneData($csv_file)
    {
        // sys setting
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ignore_user_abort();

        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');//导入时仅需记录公司ID，分配时才记录业态ID
        $append  = [
            'user_id'  => $user_id,
            'dept_id1' => $dept1['dept_id'],
        ];
        $step            = 1;
        $is_end          = false;
        $import_total    = 0;
        $file_line_total = $this->getCsvTotalLine($csv_file) - 1;
        while (!$is_end)
        {
            // 128行一个循环
            $data   = $this->getCsvLineData($csv_file,100,($step -1) * 100,$append);
            $is_end = $data[2];

            // 去除第一行标题
            if($step == 1)
            {
                unset($data[0][0]);
            }
            Log::record('写入MySqL开始，步骤数：'.$step."-".date_format(date_create(),'H:i:s u'));
            $ret = $this->PhoneData->batchInsert($data[0],$data[1],$user_id);// 批量事务写入
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
        $data  = [];
        $phone = [];
        $SplFileObject = new SplFileObject($file,'rb');
        $SplFileObject->seek($offset);
        while ($lines-- && !$SplFileObject->eof())
        {
            $_data  = $SplFileObject->fgets();
            $_data  = explode('----',$_data);
            if(count($_data) != 2 || !FilterValidHelper::is_phone_valid($_data[0]))
            {
                continue;
            }
            // 本批数据去重
            if(in_array($_data[0],$phone))
            {
                continue;
            }
            $phone[] = mb_convert_encoding($_data[0],'UTF-8','GBK').'';//手机号转成字符串
            // 转码检测并合并数组
            $data[]  = array_merge([
                'id'    => GenerateHelper::uuid(),
                'phone' => mb_convert_encoding($_data[0],'UTF-8','GBK').'',//手机号转成字符串
                'name'  => mb_convert_encoding($_data[1],'UTF-8','GBK')
            ],$append);
            $SplFileObject->next();
        }
        // 0 => 插入的数据 1 => 手机号构成的一维数组 2 => 是否已读取到文件末尾
        return [$data,$phone,$SplFileObject->eof()];
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
