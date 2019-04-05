<?php
/**
 * 网站会员模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file Customer.php
 */

namespace app\manage\model;

use think\Model;

class Customer extends Model
{
    /**
     * 主键查询
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->where('id', $id)->find();
        return empty($result) ? [] : $result->toArray();
    }
}
