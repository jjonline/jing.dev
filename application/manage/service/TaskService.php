<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-15 20:52
 * @file TaskService.php
 */

namespace app\manage\service;

use app\common\helpers\GenerateHelper;
use app\common\model\PhoneData;
use app\common\model\Task;
use app\common\model\TaskType;
use app\manage\model\Device;
use think\Request;

class TaskService
{
    /**
     * @var Task
     */
    public $Task;
    /**
     * @var TaskType
     */
    public $TaskType;
    /**
     * @var Device
     */
    public $Device;
    /**
     * @var PhoneData
     */
    public $PhoneData;

    public function __construct(Task $task,
                                Device $device,
                                PhoneData $phoneData,
                                TaskType $taskType)
    {
        $this->Task     = $task;
        $this->TaskType = $taskType;
        $this->Device   = $device;
        $this->PhoneData = $phoneData;
    }

    /**
     * 停止任务
     * @param $task_id
     * @return []
     */
    public function stopTask($task_id)
    {
        $task = $this->Task->getTaskById($task_id);
        if(empty($task) || $task['status'] != 0)
        {
            return ['error_code' => -1,'error_msg' => '任务数据不存在或任务已执行不允许停止'];
        }
        $ret = $this->Task->isUpdate(true)->save([
            'status' => -1
        ],['id' => $task['id']]);
        return $ret ? ['error_code' => 0,'error_msg' => '任务已停止','data' => $task] : ['error_code' => -1,'error_msg' => '操作异常'];
    }

    /**
     * 启动任务
     * @param $task_id
     * @return []
     */
    public function startTask($task_id)
    {
        $task = $this->Task->getTaskById($task_id);
        if(empty($task) || $task['status'] != -1)
        {
            return ['error_code' => -1,'error_msg' => '任务数据不存在或任务不处于停止状态'];
        }
        $ret = $this->Task->isUpdate(true)->save([
            'status' => 0
        ],['id' => $task['id']]);
        return $ret ? ['error_code' => 0,'error_msg' => '任务已启动','data' => $task] : ['error_code' => -1,'error_msg' => '操作异常'];
    }

    /**
     * 保存加好友任务
     * @param Request $request
     * @throws
     * @return []
     */
    public function saveJiaHaoYou(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['device']) || empty($data['message']))
        {
            return ['error_code' => -1,'error_msg' => '未勾选设备或未配置加好友话术'];
        }
        if(empty($data['add_period_start']) || empty($data['add_period_end']) || empty($data['words_period']) || empty($data['airplane_period']) || empty($data['phone_num']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices) || count($devices) != count($data['device']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 检查拟添加手机号分配数
        $phone_total = $this->PhoneData->getTotalUnUsedByUserId($user_id,$dept1['dept_id']);
        if(count($data['device']) * intval($data['phone_num']) > $phone_total)
        {
            return ['error_code' => -5,'error_msg' => '拟添加手机号资源不足，请找管理员申请额度'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600001');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }
        // 任务执行时间配置
        $task_data = [];
        $task_data['add_period_start'] = intval($data['add_period_start']);
        $task_data['add_period_end']   = intval($data['add_period_end']);
        $task_data['words_period']     = intval($data['words_period']);
        $task_data['airplane_period']  = intval($data['airplane_period']);
        $task_data['words']  = $data['message'];
        $task_data['sex']    = intval($data['sex']);

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = $task_data;

            $task[] = $_task;
        }
        $ret = $this->Task->addJiaHaoYou($task,$data['phone_num']);
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
                    : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 保存看新闻任务
     * @param Request $request
     * @return []
     */
    public function saveKanXinWen(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['device']))
        {
            return ['error_code' => -1,'error_msg' => '未勾选执行任务的设备'];
        }
        if(empty($data['news_num_start']) || empty($data['news_num_end']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        if(intval($data['news_num_end']) > 5)
        {
            return ['error_code' => -2,'error_msg' => '随机看新闻的最大条数不得超过5条'];
        }
        if(intval($data['news_num_end']) < intval($data['news_num_start']))
        {
            return ['error_code' => -2,'error_msg' => '随机看新闻的条数上限取值错误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices) || count($devices) != count($data['device']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600003');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['news_num_start'] = intval($data['news_num_start']);
        $task_data['news_num_end']   = intval($data['news_num_end']);

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }
        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 看订阅任务
     * @param Request $request
     */
    public function saveKanDingYue(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['device']))
        {
            return ['error_code' => -1,'error_msg' => '未勾选执行任务的设备'];
        }
        if(empty($data['gzh_num_start']) || empty($data['gzh_num_end']) || empty($data['gzh_file_num_start']) || empty($data['gzh_file_num_end']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        if(intval($data['gzh_num_end']) > 5)
        {
            return ['error_code' => -2,'error_msg' => '随机看公众号个数的上限不得超过5个'];
        }
        if(intval($data['gzh_file_num_end']) > 3)
        {
            return ['error_code' => -2,'error_msg' => '一个公众号随机看的文章数的上限不得超过3条'];
        }
        // 上下限值溢出
        if(intval($data['gzh_num_end']) < intval($data['gzh_num_start']))
        {
            return ['error_code' => -2,'error_msg' => '随机看公众号个数的上限取值错误'];
        }
        if(intval($data['gzh_file_num_end']) < intval($data['gzh_file_num_start']))
        {
            return ['error_code' => -2,'error_msg' => '一个公众号随机看的文章数的上限取值错误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices) || count($devices) != count($data['device']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600004');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['gzh_num_start'] = intval($data['gzh_num_start']);
        $task_data['gzh_num_end']   = intval($data['gzh_num_end']);
        $task_data['gzh_file_num_start'] = intval($data['gzh_file_num_start']);
        $task_data['gzh_file_num_end']   = intval($data['gzh_file_num_end']);

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }
        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 新增互撩任务
     * @param Request $request
     */
    public function saveHuLiaoTask(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['device']))
        {
            return ['error_code' => -1,'error_msg' => '未勾选执行任务的设备'];
        }
        if(empty($data['send_period_start']) || empty($data['send_period_end'])
            || empty($data['person_period_start']) || empty($data['person_period_end'])
            || empty($data['words_random_start']) || empty($data['words_random_end'])
            || empty($data['friends_random_start']) || empty($data['friends_random_end']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        // 上下限值溢出
        if(intval($data['send_period_end']) < intval($data['send_period_start']))
        {
            return ['error_code' => -2,'error_msg' => '内容随机间隔取值错误'];
        }
        if(intval($data['person_period_end']) < intval($data['person_period_start']))
        {
            return ['error_code' => -2,'error_msg' => '好友随机间隔取值错误'];
        }
        if(intval($data['words_random_end']) < intval($data['words_random_start']))
        {
            return ['error_code' => -2,'error_msg' => '随机互聊内容取值错误'];
        }
        if(intval($data['friends_random_end']) < intval($data['friends_random_start']))
        {
            return ['error_code' => -2,'error_msg' => '随机互聊对象取值错误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }

        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices) || count($devices) != count($data['device']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600006');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['send_period_start'] = intval($data['send_period_start']);
        $task_data['send_period_end']   = intval($data['send_period_end']);
        $task_data['person_period_start'] = intval($data['person_period_start']);
        $task_data['person_period_end']   = intval($data['person_period_end']);
        $task_data['words_start']   = intval($data['words_random_start']);
        $task_data['words_end']   = intval($data['words_random_end']);
        $task_data['friends_start']   = intval($data['friends_random_start']);
        $task_data['friends_end']   = intval($data['friends_random_end']);

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }
        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 保存微信上好友上报任务
     * @param Request $request
     * @return array
     */
    public function saveUploadFriends(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['device']))
        {
            return ['error_code' => -1,'error_msg' => '未勾选执行任务的设备'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices) || count($devices) != count($data['device']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600010');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        // 任务执行时间配置
        $task_data = [];

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }
        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 互撩对象下发
     * @param Request $request
     * @throws
     * @return []
     */
    public function saveHuLiaoUsers(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['radom_max']) || empty($data['radom_min']) || empty($data['device_id']) || empty($data['friends']))
        {
            return ['error_code' => -3,'error_msg' => '参数有误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceById($data['device_id']);
        if(empty($devices))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600008');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }
        if(intval($data['radom_max']) < intval($data['radom_min']))
        {
            return ['error_code' => -2,'error_msg' => '随机等待时间取值错误'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['radom_min'] = intval($data['radom_min']);
        $task_data['radom_max'] = intval($data['radom_max']);
        $task_data['friends']   = $data['friends'];//不再检测所选互撩对象是否有误 全靠操作者小心在意

        $_task = [];
        $_task['id']         = GenerateHelper::uuid();
        $_task['user_id']    = $user_id;
        $_task['device_id']  = $data['device_id'];
        $_task['dept_id1']   = $dept1['dept_id'];
        $_task['dept_id2']   = $dept2['dept_id'];
        $_task['type_id']    = $task_type['id'];
        $_task['begin_time'] = $data['begin_time'];
        $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
        $_task['remark']     = trim($data['remark']);
        $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

        $ret = $this->Task->insert($_task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 互撩内容下发
     * @param Request $request
     * @throws
     * @return []
     */
    public function saveHuLiaoNeiRong(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['devices']) || empty($data['chats']))
        {
            return ['error_code' => -3,'error_msg' => '参数有误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceListByIds($data['devices']);
        if(empty($devices) || count($devices) != count($data['devices']))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600007');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }
        // 切分互撩内容
        $_chats = array_chunk($data['chats'],2);
        $chats  = [];
        foreach ($_chats as $key => $value)
        {
            $chats[$key]['msg'] = $value[0];
            $chats[$key]['reply'] = $value[1];
        }
        // 任务执行时间配置
        $task =[];
        $task_data = [];
        $task_data['words'] = $chats;

        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }

        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 新增群发消息任务
     * @param Request $request
     * @return []
     */
    public function saveQunFaXiaoXi(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['send_period_start']) || empty($data['send_period_end'])
            || empty($data['person_period_start']) || empty($data['person_period_end'])
            || empty($data['friends']) || empty($data['chats']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        // 上下限值溢出
        if(intval($data['send_period_end']) < intval($data['send_period_start']))
        {
            return ['error_code' => -2,'error_msg' => '内容随机间隔取值错误'];
        }
        if(intval($data['person_period_end']) < intval($data['person_period_start']))
        {
            return ['error_code' => -2,'error_msg' => '好友随机间隔取值错误'];
        }
        // 设备
        if(empty($data['device_id']) || empty($data['chats']))
        {
            return ['error_code' => -3,'error_msg' => '参数有误'];
        }
        if(empty($data['begin_time']) || $data['begin_time'] != date('Y-m-d H:i:s',strtotime($data['begin_time'])))
        {
            return ['error_code' => -3,'error_msg' => '请正确设置任务开始时间'];
        }
        // 检查设备
        $devices = $this->Device->getAuthDeviceById($data['device_id']);
        if(empty($devices))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600005');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['send_period_start']   = intval($data['send_period_start']);
        $task_data['send_period_end']     = intval($data['send_period_end']);
        $task_data['person_period_start'] = intval($data['person_period_start']);
        $task_data['person_period_end']   = intval($data['person_period_end']);
        $task_data['words']   = $data['chats'];
        $task_data['friends'] = $data['friends'];

        $_task = [];
        $_task['id']         = GenerateHelper::uuid();
        $_task['user_id']    = $user_id;
        $_task['device_id']  = $data['device_id'];
        $_task['dept_id1']   = $dept1['dept_id'];
        $_task['dept_id2']   = $dept2['dept_id'];
        $_task['type_id']    = $task_type['id'];
        $_task['begin_time'] = $data['begin_time'];
        $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
        $_task['remark']     = trim($data['remark']);
        $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

        $ret = $this->Task->insert($_task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 保存发朋友圈任务
     * @param Request $request
     * @return []
     */
    public function saveFaPengYouQuan(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['resources']) || empty($data['words']) || empty($data['type'])
            || empty($data['begin_time']) || empty($data['device']))
        {
            return ['error_code' => -2,'error_msg' => '请完善任务参数配置'];
        }
        $devices = $this->Device->getAuthDeviceListByIds($data['device']);
        if(empty($devices))
        {
            return ['error_code' => -4,'error_msg' => '所选执行任务的设备信息有误'];
        }
        // 任务分类
        $task_type = $this->TaskType->getTaskTypeByCode('600002');
        if(empty($task_type))
        {
            return ['error_code' => -6,'error_msg' => '系统异常：任务分类数据丢失，请立即联系服务人员获取帮助'];
        }

        $comments = [];
        if(!empty($data['comments1']))
        {
            $comments[] = $data['comments1'];
        }
        if(!empty($data['comments2']))
        {
            $comments[] = $data['comments2'];
        }

        // 任务执行时间配置
        $task_data = [];
        $task_data['words']    = trim($data['words']);
        $task_data['comments'] = $comments;
        $task_data['type']     = $data['type'] == 'picture' ? 'picture' : 'video';
        $task_data['urls']     = $data['resources'];
        if($task_data['type'] == 'video' && count($task_data['urls']) > 1)
        {
            return ['error_code' => -6,'error_msg' => '视频朋友圈仅能发送一条视频'];
        }

        $task = [];
        foreach ($devices as $device) {
            $_task = [];
            $_task['id']         = GenerateHelper::uuid();
            $_task['user_id']    = $user_id;
            $_task['device_id']  = $device['id'];
            $_task['dept_id1']   = $dept1['dept_id'];
            $_task['dept_id2']   = $dept2['dept_id'];
            $_task['type_id']    = $task_type['id'];
            $_task['begin_time'] = $data['begin_time'];
            $_task['status']     = intval($data['run_tag']) == -1 ? -1 : 0;
            $_task['remark']     = trim($data['remark']);
            $_task['task_data']  = json_encode($task_data,JSON_UNESCAPED_UNICODE);

            $task[] = $_task;
        }
        $ret = $this->Task->insertAll($task);//返回新增行数
        return $ret ? ['error_code' => 0,'error_msg' => '保存成功']
            : ['error_code' => -7,'error_msg' => '系统异常：写入任务出现故障'];
    }

    /**
     * 删除任务
     * @param Request $request
     * @throws
     * @return []
     */
    public function deleteTask(Request $request)
    {
        // 检查归属
        $task = $this->Task->getTaskById($request->post('id'));
        if(empty($task) || !empty($task['delete_time']) || $task['status'] == 1)
        {
            return ['error_code' => -1,'error_msg' => '任务数据不存在或正在执行不允许删除'];
        }
        $ret = $this->Task->where('id',$task['id'])->update(['delete_time' => date('Y-m-d H:i:s')]);
        return $ret > 0 ? ['error_code' => 0,'error_msg' => '执行完成，任务已删除','data' => $task] : ['error_code' => -1,'error_msg' => '删除失败'];
    }

}
