<?php
/**
 * 轮播图模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 21:23:00
 * @file Image.php
 */

namespace app\manage\model;

use think\Model;

class Image extends Model
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
