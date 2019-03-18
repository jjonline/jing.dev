<?php
/**
 * 网站单页模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file Page.php
 */

namespace app\manage\model;

use think\Model;

class Page extends Model
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
