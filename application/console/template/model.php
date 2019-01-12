<?php
/**
 * __LIST_NAME__模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date __CREATE_TIME__
 * @file __CONTROLLER__.php
 */

namespace app\manage\model;

use think\Model;

class __CONTROLLER__ extends Model
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
