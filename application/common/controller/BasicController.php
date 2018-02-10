<?php
/**
 * 所有控制器的顶级控制器基类
 * ---
 * 1、实现一些非常基础的所有控制器下的操作均可使用方法
 * 2、实现一些拦截逻辑
 * 3、所有开发控制器类不要直接继承该类，请继承BaseController
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-10 22:07
 * @file BasicController.php
 */

namespace app\common\controller;

use think\Controller;
use think\Response;

class BasicController extends Controller
{

    /**
     * 输出json字符串
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

}
