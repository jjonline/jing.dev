<?php
/**
 * 无线分类树帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-28 11:26
 * @file TreeHelper.php
 */

namespace app\common\helper;

class TreeHelper
{
    /**
     * @param array $arr    树数组
     * @param int $paren_id 需要查找所有子节点
     * @return array|mixed
     */
    public static function child($arr, $paren_id)
    {
        // $child = [];
        foreach ($arr as $key => $value) {
            if ($value['parent_id'] == $paren_id) {
                $child[$value['id']] = $value;
                $child              += self::child($arr, $value['id']);
            }
        }
        return isset($child) ? $child : [];
    }

    /**
     * 分类排序（降序）
     * @param $arr
     * @param $cols
     * @return mixed
     */
    public static function sort($arr, $cols)
    {
        //子分类排序
        $sort = [];
        foreach ($arr as $k => &$v) {
            if (!empty($v['children'])) {
                $v['children'] = self::sort($v['children'], $cols);
            }
            $sort[$k] = $v[$cols];
        }
        if (!empty($sort)) {
            array_multisort($sort, SORT_DESC, $arr);
        }
        return $arr;
    }

    /**
     * 横向分类树
     * @param array $arr 待处理成树状的二维数组
     * @param int $parent_id 默认0表示按parent_id字段来处理，传值则只获取该父ID指定的单棵树
     * @return array
     */
    public static function hTree($arr, $parent_id = 0)
    {
        foreach ($arr as $k => $v) {
            if ($v['parent_id'] == $parent_id) {
                $data[$v['id']]        = $v;
                $data[$v['id']]['children'] = self::hTree($arr, $v['id']);
            }
        }
        return isset($data) ? $data : array();
    }

    /**
     * 纵向分类树
     * @param array $arr 待处理成树状的二维数组
     * @param int $parent_id 默认0表示按parent_id字段来处理，传值则只获取该父ID指定的单棵树
     * @return array|mixed
     */
    public static function vTree($arr, $parent_id = 0)
    {
        foreach ($arr as $k => $v) {
            if ($v['parent_id'] == $parent_id) {
                $data[$v['id']] = $v;
                $data += self::vTree($arr, $v['id']);
            }
        }
        return isset($data) ? $data : array();
    }
}
