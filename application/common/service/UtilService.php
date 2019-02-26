<?php
/**
 * 工具类方法汇总服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-17 11:43
 * @file UtilService.php
 */

namespace app\common\service;

use app\common\helper\FilterValidHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use think\Exception;

class UtilService
{
    /**
     * @var Client
     */
    public $GuzzleHttpClient;

    /**
     * 通过手机号查询区域信息
     * @param $mobile
     * @return array
     */
    public function getAreaInfoByMobile($mobile)
    {
        if (empty($mobile) || !FilterValidHelper::is_phone_valid($mobile)) {
            return ['error_code' => 500,'error_msg' => '手机号格式有误'];
        }
        try {
            if (empty($this->GuzzleHttpClient)) {
                $jar = new CookieJar();
                $this->GuzzleHttpClient = new Client([
                    'base_uri' => 'http://m.ip138.com',
                    'cookies'  => $jar,
                    'timeout'  => 15
                ]);
            }
            // deploy request
            $response = $this->GuzzleHttpClient->request('GET', '/mobile.asp', [
                'query'   => ['mobile' => $mobile],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) '
                        .'AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
                    'Referer'    => 'http://m.ip138.com/'
                ]
            ]);
            $code = $response->getStatusCode();
            if ($code != 200) {
                throw new Exception('获取远程数据失败');
            }
            $content = $response->getBody()->getContents();
            $state   = $result = preg_match_all('/<tr><td>.*<span>(.*)<\/span><\/td><\/tr>/', $content, $match);
            if (false === $state || count($match) !=2 || count($match[1]) != 4) {
                throw new Exception('解析远程数据失败');
            }
            $result            = $match[1];
            $data              = [];
            $data['mobile']    = $mobile;
            $data['location']  = trim($result[0]);
            $data['zip_code']  = trim($result[3]);
            $data['area_code'] = trim($result[2]);
            if (false !== mb_strpos($result[1], '移动')) {
                $data['tel_com'] = '中国移动';
            }
            if (false !== mb_strpos($result[1], '电信')) {
                $data['tel_com'] = '中国电信';
            }
            if (false !== mb_strpos($result[1], '联通')) {
                $data['tel_com'] = '中国联通';
            }
            return $data;
        } catch (\Throwable $e) {
            return ['error_code' => 500,'error_msg' => '请求失败：'.$e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error_code' => 500,'error_msg' => '请求失败：'.$e->getMessage()];
        }
    }
}
