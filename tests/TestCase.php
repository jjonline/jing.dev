<?php
/**
 * 测试用例基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 13:27
 * @file TestCase.php
 */
namespace tests;

use think\testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $baseUrl = 'http://localhost';

    /**
     * 测试接口响应是一个json
     * @param $response
     * @param string $apiName
     * @return $this
     */
    protected function assertResponseIsJson($response, $apiName = '')
    {
        // 如果响应体数据为空 则通过api名称执行一次GET请求获取到响应体
        if (empty($response)) {
            // 执行请求，断言响应码
            $res = $this->visit($apiName);

            // 获取响应内容，断言响应结果集是一个json对象字符串
            $response = $res->response->getContent();
        }
        $this->assertResponseOk();
        $this->assertJson($response, '接口响应结果集不是一个json对象');

        // 转化json，断言json的组成结构
        $_response = json_decode($response, true);
        $this->assertArrayHasKey('data', $_response, '接口['.$apiName.']响应结果集data字段不存在');
        $this->assertArrayHasKey('msg', $_response, '接口['.$apiName.']响应结果集msg字段不存在');
        $this->assertArrayHasKey('code', $_response, '接口['.$apiName.']响应结果集code字段不存在');
        $this->assertArrayHasKey('time', $_response, '接口['.$apiName.']响应结果集time字段不存在');
        return $this;
    }
}
