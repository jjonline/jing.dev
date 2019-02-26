<?php
/**
 * User: 杨晶晶(jingjingyang@hk01.com)
 * Time: 2018-11-08
 * File: HttpHelper.php
 */
namespace app\common\helper;

use GuzzleHttp\Client;
use think\Exception;

class HttpHelper
{
    /**
     * @var Client
     */
    protected static $guzzleHttpClient;

    /**
     * 执行guzzleHttp的请求方法，与guzzleHttp参数非常类似
     * @param  string $method  请求方式
     * @param  string $api     请求的api|guzzleHttp原生传入请求的URL，因为继承类需要优先设置baseUrl固api即可
     * @param  array  $options [guzzleHttp数组形式的参数]
     * @return array
     * @throws Exception
     */
    public static function request($method, $api = '', array $options = [])
    {
        if (is_null(self::$guzzleHttpClient)) {
            self::$guzzleHttpClient = new Client([
                'base_uri'         => '',
                'timeout'          => 30,    // 建立起连接后等待数据返回的会超时时间--单位：秒
                'connect_timeout'  => 5,     // 建立连接超时时间--单位：秒
                'force_ip_resolve' => 'v4',  // 强制使用ipV4协议
                'http_errors'      => false, // http非200状态不抛出异常
                'allow_redirects'  => false, // http重定向不执行
                'decode_content'   => false, // 是否解码结果集
                'headers'          => [
                    'user-agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                    'Referer'    => 'https://www.google.com/',
                ],
            ]);
        }
        // 将不标准的GuzzleException转换为Exception
        try {
            $response = self::$guzzleHttpClient->request($method, trim($api, '/'), $options);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        // 处理结果集
        $result           = [];
        $result['code']   = $response->getStatusCode();
        $result['header'] = $response->getHeaders();
        $result['body']   = $response->getBody()->getContents();

        // 返回统一的数组结果集
        return $result;
    }

    /**
     * 执行get请求
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function get($api, array $query = [], array $header = [])
    {
        return self::request('GET', $api, ['headers' => $header,'query' => $query]);
    }

    /**
     * 默认post请求用于发送application/x-www-form-urlencoded形式的表单数据-即post方法为postFormFiled方法的别名
     * @param string $api 请求地址，完整的url
     * @param array  $query  需附带在url中的键值对
     * @param array  $header post提交时需附带在header中的键值对
     * @param array  $body   post提交的键值对数组
     * @return array
     * @throws Exception
     */
    public static function post($api, array $query = [], array $header = [], array $body = [])
    {
        return self::postFormFiled($api, $query, $header, $body);
    }

    /**
     * 执行post发送application/x-www-form-urlencoded形式的表单数据
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postFormFiled($api, array $query = [], array $header = [], array $body = [])
    {
        return self::request('POST', $api, [
            'headers'     => $header,
            'query'       => $query,
            'form_params' => $body
        ]);
    }

    /**
     * put发送表单、putFormFiled的别名
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function put($api, array $query = [], array $header = [], array $body = [])
    {
        return self::putFormFiled($api, $query, $header, $body);
    }

    /**
     * 执行put发送application/x-www-form-urlencoded形式的表单数据
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function putFormFiled($api, array $query = [], array $header = [], array $body = [])
    {
        return self::request('PUT', $api, [
            'headers'     => $header,
            'query'       => $query,
            'form_params' => $body
        ]);
    }

    /**
     * 执行post发送multipart/form-data形式文件
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * body参数必须是以下数组单元构成的多维数组：
     * [
     *     'name'     => 'other_file',//必须
     *     'contents' => 'hello',//必须
     *     'filename' => 'filename.txt',//可选
     *     'headers'  => [
     *       'X-Foo' => 'this is an extra header to include'
     *     ]//可选
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postFormData($api, array $query = [], array $header = [], array $body = [])
    {
        return self::request('POST', $api, [
            'headers'     => $header,
            'query'       => $query,
            'multipart'   => $body
        ]);
    }

    /**
     * 执行post发送json字符串body的请求
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postJson($api, array $query = [], array $header = [], array $body = [])
    {
        // 如果没有设置任何header，则强制设置请求体类型为application/json
        if (empty($header)) {
            $header = [
                'Content-Type' => 'application/json'
            ];
        }
        return self::request('POST', $api, [
            'headers' => $header,
            'query'   => $query,
            'json'    => $body
        ]);
    }

    /**
     * put方式提交json
     * @param  string $api      请求的api
     * @param  array  $query    Query的数组键值对，即用于url中的get变量
     * @param  array  $header   发送请求中的header数组键值对
     * @param  array  $body     发送的body参数数组
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function putJson($api, array $query = [], array $header = [], array $body = [])
    {
        return self::request('PUT', $api, [
            'headers' => $header,
            'query'   => $query,
            'json'    => $body
        ]);
    }
}
