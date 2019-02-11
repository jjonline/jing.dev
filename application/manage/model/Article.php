<?php
/**
 * 前台图文模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-02-11 11:19:00
 * @file Article.php
 */

namespace app\manage\model;

use think\Model;

class Article extends Model
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
        return empty($result) ? [] : $result;
    }
}
