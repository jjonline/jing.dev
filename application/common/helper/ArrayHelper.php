<?php
/**
 * 数组转换帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04
 *
 */

namespace app\common\helper;

class ArrayHelper {

    /**
     * 将二维数组转化为字典
     * @param array $array 待转化的数组 
     * @param string $key 需要做为键的字段名
     */
    public static function convertToDictionary(array $array, $key) {
        if (empty($array) || empty($key)) {
            return $array;
        }

        $dic = [];

        foreach ($array as $item) {
            $dic[$item[$key]] = $item;
        }

        return $dic;
    }

    /**
     * 将二维数组中某个键的值提权转换为一维索引数组
     * @param array $array 待转化的数组
     * @param string $key  提取的建名
     */
    public static function extractToVector(array $array, $key)
    {
        if (empty($array) || empty($key)) {
            return $array;
        }
        $vector = [];

        foreach ($array as $item) {
            $vector[] = $item[$key];
        }

        return $vector;
    }

    /**
     * 支持对数组按某个key或属性进行分组
     * @param array $arr
     * @param callable|string $key_selector
     * @return array
     */
    public static function group(array $arr, $key_selector) {
        if (!isset($arr))
            return null;

        $result = array();
        foreach ($arr as $i) {
            $key = $i[$key_selector];
            $result[$key][] = $i;
        }
        return $result;
    }

    /**
     * 通过回调函数对数组分组，回调函数用于获取分组的Key名称
     * @param array    $arr      分组的数组
     * @param callable $callable 回调函数
     * @return array|null
     */
    public static function groupByCallable(array $arr, $callable)
    {
        if (!isset($arr))
            return null;

        $isSelector = is_callable($callable);
        $result = array();
        foreach ($arr as $i) {
            if ($isSelector) {
                $key = call_user_func($callable, $i);
                if ($key) {
                    $result[$key][] = $i;
                }
            } else {
                $key = $i[$callable];
                $result[$key][] = $i;
            }
        }
        return $result;
    }

    public static function assoc_unique($arr, $key) {
        $tmp_arr = [];
        foreach ($arr as $v) {
            if (!array_key_exists($tmp_arr, $v[$key])) {
                $tmp_arr[$v[$key]] = $v;
            }
        }

        return array_values($tmp_arr);
    }

    /**
     * 数据XML编码
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    public static function toXml($data, $item = 'item', $id = 'id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? self::toXml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * 无限分类数据按层级、分叉进行排序
     * @param $data  []     数组
     * @param $first_level_data  []  第一级别的元素数组
     * @param $parent_key string 无限分类的父分类键名
     * @param $child_key  string 无限分类的子分类键名
     */
    public static function sortMultiTree($data,$first_level_data,$parent_key,$child_key)
    {
        $sort_data = [];
        foreach ($first_level_data as $first_level_datum) {
            $item     = static::sortRoleArray($data,$first_level_datum[$parent_key],$parent_key,$child_key);
            $sort_data[$first_level_datum[$parent_key]] = $first_level_datum;
            $sort_data = $sort_data + $item;//array_merge合并数组的话会产生索引键名覆盖的问题
        }
        return $sort_data;
    }

    /**
     * 辅助sortMultiTree方法的内部无限排序
     * @param $role
     * @param $name
     * @param $parent_key
     * @param $child_key
     * @return array
     */
    public static function sortRoleArray($data,$name,$parent_key,$child_key)
    {
        foreach($data as $key => $value)
        {
            if($value[$child_key] == $name)
            {
                $_data[$value[$parent_key]] = $value;
                $_data  += static::sortRoleArray($data,$value[$parent_key],$parent_key,$child_key);
            }
        }
        return isset($_data) ? $_data : [];
    }

}
