<?php
/**
 * 发送短信异步任务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 13:25
 * @file SendSMSTask.php
 */

namespace app\console\task;

use think\Exception;
use Aliyun\Core\Config as AliyunConfig;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

class SendSMSTask extends BaseTask
{
    /**
     * @var string 固定的任务标题
     */
    public $title = '发送短信';
    /**
     * @var array
     */
    protected $task_data;

    /**
     * 异步任务执行的入口
     * @param array $task_data
     * @return boolean true执行成功、false执行失败或出异常
     */
    public function execute(array $task_data)
    {
        try {
            $this->task_data = $task_data;
            // 标记任务开始
            $this->start($task_data['async_task_id']);

            foreach ($task_data['receiver'] as $key => $phone) {
                $this->log('接收短信手机号'.($key + 1) .'：'. $phone);
            }

            $this->log('短信内容：'.$task_data['content']);

            $send_status = $this->sendSMS($task_data);
            if (false === $send_status) {
                throw new Exception('短信接口调用失败');
            }
            // 标记任务成功结束
            $this->finishSuccess($task_data['async_task_id']);

            return true;
        } catch (\Throwable $e) {
            $this->finishFail('发送短信失败：'.$e->getMessage());
        }
    }

    /**
     * 执行阿里云通信接口调用请求
     * @param $task_data
     * @return bool
     */
    protected function sendSMS($task_data)
    {
        $appKey            = $task_data['app_key'];
        $appSecret         = $task_data['app_secret'];
        $signName          = $task_data['sign'];
        $template_code     = $task_data['code'];
        $json_string_param = $task_data['json'];
        // 接收短信的手机号码
        $phone             = implode(',', $task_data['receiver']);

        AliyunConfig::load();

        $profile   = DefaultProfile::getProfile("cn-hangzhou", $appKey, $appSecret);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "Dysmsapi", "dysmsapi.aliyuncs.com");
        $acsClient = new DefaultAcsClient($profile);
        $request   = new SendSmsRequest();
        $request->setPhoneNumbers($phone);
        $request->setSignName($signName);
        $request->setTemplateCode($template_code);

        if (!empty($json_string_param)) {
            $request->setTemplateParam($json_string_param);
        }

        $acsResponse =  $acsClient->getAcsResponse($request);
        if ($acsResponse && strtolower($acsResponse->Code) == 'ok') {
            $this->log("发送成功，返回描述：".$acsResponse->Message);
            $this->log("阿里业务ID：".$acsResponse->RequestId);
            return true;
        }
        $this->log("发送失败，报错文本：".$acsResponse->Message);
        $this->log("阿里业务ID：".$acsResponse->RequestId);
        return false;
    }
}
