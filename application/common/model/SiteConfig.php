<?php
/**
 * 站点自定义配置表
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-06-16 15:02
 * @file SiteConfig.php
 */

namespace app\common\model;

use think\Model;

class SiteConfig extends Model
{
    protected $json = ['val'];

    /**
     * 主键查询站点配置单条记录
     * @param  int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSiteConfigById($id)
    {
        if (empty($id)) {
            return [];
        }
        $data = $this->where('id', $id)->find();
        return empty($data) ? [] : $data->toArray();
    }

    /**
     * 获取所有站点配置项列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSiteConfigList()
    {
        $data = $this->order(['flag' => 'ASC','sort'=>'ASC','create_time' => 'DESC'])->select();
        return $data->isEmpty() ? [] : $data->toArray();
    }

    /**
     * 配置项查找配置配置记录
     * @param string $key
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSiteConfigByKey($key)
    {
        if (empty($key)) {
            return [];
        }
        $data = $this->where('key', $key)->find();
        return empty($data) ? [] : $data->toArray();
    }

    /**
     * 依据站点配置key查找配置值，第2个可选参数可传一个默认值当查找不到时返回该默认值
     * @param string $key
     * @param mixed  $default_val
     * @return mixed
     */
    public function getSitConfigValueByKey($key, $default_val = null)
    {
        if (empty($key)) {
            return $default_val;
        }
        $value = $this->where('key', $key)->value('value');
        return is_null($value) ? $default_val : $value;
    }
}
