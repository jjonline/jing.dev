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
     * 开放平台类型，添加类型后注意同步修改user_open表的枚举类型字段type的待选值
     * @var array
     */
    public $OpenType = [
        'qq'          => 'QQ',
        'pc_weixin'   => '微信扫码',
        'mp_weixin'   => '微信公众号',
        'xiaochengxu' => '小程序',
        'weibo'       => '微博',
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
    public function getUserOpenListInfoByUserId($user_id,$open_type = null)
    {
        if(empty($open_type))
        {
            $data = $this->where('user_id',$user_id)->select();
        }else {
            $data = $this->where(['user_id' => $user_id,'open_type' => trim($open_type)])->select();
        }
        if(!$data->isEmpty())
        {
            $data = $data->toArray();
            foreach ($data as $key => $val) {
                $data[$key] = $this->parseUserOpenInfo($val);
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
    public function getUserOpenInfoByOpenId($open_id,$open_type = null)
    {
        if(empty($open_type))
        {
            $data = $this->where('open_id',$open_id)->find();
        }else {
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
    protected function parseUserOpenInfo($user_open_item = array())
    {
        if(empty($user_open_item) || empty($user_open_item['open_type']))
        {
            throw new Exception('解析用户开放平台类型参数错误：必须有open_type标识');
        }
        $open_type              = $user_open_item['open_type'];
        $user_open_item['type'] = '未知';
        if(isset($this->OpenType[$open_type]))
        {
            $user_open_item['type'] = $this->OpenType[$open_type];
        }
        return $user_open_item;
    }
}
