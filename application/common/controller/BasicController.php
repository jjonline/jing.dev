<?php
/**
 * 所有控制器的顶级控制器基类
 * ---
 * 1、实现一些非常基础的所有控制器下的操作均可使用的方法
 * 2、实现一些拦截逻辑
 * 3、所有开发控制器类不要直接继承该类，请继承BaseController
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:07
 * @file BasicController.php
 */

namespace app\common\controller;

use app\common\service\LogService;
use think\Container;
use think\Controller;
use think\Response;

class BasicController extends Controller
{
    /**
     * @var LogService
     */
    protected $LogService;

    public function initialize()
    {
        parent::initialize();
        // 初始化操作日志服务，封装控制器下直接可使用的日志记录方法
        $this->LogService  = Container::get('app\common\service\LogService');
    }

    protected function logRecorder($data = null)
    {
        return $this->LogService->logRecorder($data);
    }

    /**
     * 定制输出json字符串方法封装
     * @param array $data    json输出的数组
     * @param int $code      http状态码
     * @param array $header  可选的header头数组
     * @param array $options 可选的参数数组
     * @return mixed
     */
    protected function asJson($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code, $header, $options);
    }

    /**
     * 按约定输出json
     * @param int    $error_code json自定义错误码
     * @param string $error_msg  json自定义错误描述信息
     * @param mixed  $data       json自定义输出数据内容
     * @return Response
     */
    protected function renderJson($error_code,$error_msg,$data)
    {
        return Response::create([
            'error_code' => $error_code,
            'error_msg'  => $error_msg,
            'data'       => $data
        ], 'json', 200);
    }

}
