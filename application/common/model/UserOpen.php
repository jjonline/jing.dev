<?php
/**
 * 开放平台账号信息
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-03 17:30
 * @file UserOpen.php
 */

namespace app\common\model;

use think\Exception;
use think\Model;

class UserOpen extends Model
{
    /**
     * @var int qq开放平台登录
     */
    const OPEN_QQ = 1;
    /**
     * @var int 微信pc扫码登录
     */
    const OPEN_WX_PC = 2;
    /**
     * @var int 微信公众号登录
     */
    const OPEN_WX_MP = 3;
    /**
     * @var int 微信小程序登录
     */
    const OPEN_WX_APPLET = 4;
    /**
     * @var int 微博开放平台登录
     */
    const OPEN_WB = 5;

    /**
     * 开放平台类型map
     * @var array
     */
    public $OpenType = [
        self::OPEN_QQ        => 'QQ',
        self::OPEN_WX_PC     => '微信扫码',
        self::OPEN_WX_MP     => '微信公众号',
        self::OPEN_WX_APPLET => '微信小程序',
        self::OPEN_WB        => '微博',
    ];

    /**
     * 根据用户ID查找用户的已绑定的开放平台账号信息
     * @param $user_id
     * @param null $open_type
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserOpenListInfoByUserId($user_id, $open_type = null)
    {
        if (empty($open_type)) {
            $data = $this->where('user_id', $user_id)->select();
        } else {
            $data = $this->where(['user_id' => $user_id,'open_type' => trim($open_type)])->select();
        }
        if (!$data->isEmpty()) {
            $data = $data->toArray();
            foreach ($data as $key => $val) {
                $data[$key] = $this->parseUserOpenTypeInfo($val);
            }
            return $data;
        }
        return [];
    }

    /**
     * 依据OpenID和可能的开放平台类型查找开放平台信息
     * @param $open_id
     * @param null $open_type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserOpenInfoByOpenId($open_id, $open_type = null)
    {
        if (empty($open_type)) {
            $data = $this->where('open_id', $open_id)->find();
        } else {
            $data = $this->where(['open_id' => $open_id,'open_type' => trim($open_type)])->find();
        }
        return $data ? $data->toArray() : [];
    }

    /**
     * 检测是否存在指定标识的开放平台类型
     * @param $open_type
     * @return bool
     */
    public function hasOpenType($open_type)
    {
        return isset($this->OpenType[$open_type]);
    }

    /**
     * 分析开放平台记录的开放平台类型并将类型汉字描述补充至返回值数组
     * @param array $user_open_item
     * @return array
     * @throws Exception
     */
    protected function parseUserOpenTypeInfo($user_open_item = array())
    {
        if (empty($user_open_item) || empty($user_open_item['open_type'])) {
            throw new Exception('解析用户开放平台类型参数错误：必须有open_type标识');
        }
        $open_type              = $user_open_item['open_type'];
        $user_open_item['type'] = '未知';
        if (isset($this->OpenType[$open_type])) {
            $user_open_item['type'] = $this->OpenType[$open_type];
        }
        return $user_open_item;
    }
}
