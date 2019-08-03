<?php
/**
 * 数组转换帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04
 *
 */

namespace app\common\helper;

class ArrayHelper
{
    /**
     * 将目标转换为异步任务结果集的一维数组结构
     * @param $origin
     * @return array
     */
    public static function toAsyncResultArray($origin)
    {
        if (!is_array($origin)) {
            return $origin;
        }
        $result = [];
        foreach ($origin as $key => $value) {
            $prefix   = is_numeric($key) ? '' : "$key => ";
            $val      = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            $result[] = $prefix . $val;
        }
        return $result;
    }

    /**
     * 一维数组去除等价false值 + 去重 + 每个元素去除两端空白
     * @param array $origin 一维数组
     * @return array
     */
    public static function filterArrayThenUnique($origin)
    {
        // 去除等价false值 去除空值 返回唯一值
        return array_unique(array_filter(array_map('trim', $origin)));
    }

    /**
     * 字符串或stdClass转换为纯数组
     * ---
     * 1、字符串形式的
     * 2、stdClass形式的
     * 3、数组的stdClass形式的
     * ---
     * @param $origin
     * @return array
     */
    public static function toArray($origin)
    {
        if (empty($origin)) {
            return $origin;//原样返回
        }
        if (is_string($origin)) {
            return json_decode($origin, true);
        }
        if ($origin instanceof \stdClass) {
            return json_decode(json_encode($origin), true);
        }
        if (is_array($origin)) {
            foreach ($origin as $key => $value) {
                if ($value instanceof \stdClass) {
                    $origin[$key] =  json_decode(json_encode($value), true);
                }
            }
        }
        return $origin;
    }

    /**
     * 二维数组按照二维中某个键指向的值进行制定排序规则的排序
     * @param array $multi_array 二维数组
     * @param mixed $level_2_key 二维数组中用于排序依据的排序
     * @param int $sort_order    排序规则升序或降序，常量：SORT_DESC、SORT_ASC
     * @param int $sort_flags    算法常量：
     * SORT_REGULAR、SORT_NUMERIC、SORT_STRING、SORT_LOCALE_STRING、SORT_NATURAL、SORT_FLAG_CASE
     * @return mixed
     */
    public static function multiSortByKey(
        $multi_array,
        $level_2_key,
        $sort_order = SORT_DESC,
        $sort_flags = SORT_REGULAR
    ) {
        $sort_array = [];
        foreach ($multi_array as $key => $value) {
            if (isset($value[$level_2_key])) {
                $sort_array[] = $value[$level_2_key];
            }
        }
        if (empty($sort_array)) {
            return $multi_array;
        }
        // 引用形式排序处理
        array_multisort($sort_array, $sort_order, $sort_flags, $multi_array);
        return $multi_array;
    }

    /**
     * 一维数组多元素切成成对的二维数组，仅供swoole中redis队列参数转换使用
     * ---
     * 例如：['a','b','c']
     * 调用后切成：
     * [
     *    [a,b],
     *    [a,c]
     * ]
     * ---
     * 传入参数数组务必事先检查元素个数
     * @param array $array
     * @return array
     */
    public static function segmentToPairArray(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $_item    = [];
            $_item[0] = $array[0];
            if ($key > 0) {
                $_item[1] = $value;
                $result[] = $_item;
            }
        }
        return $result;
    }

    /**
     * 将二维数组转化为字典
     * @param array $array 待转化的数组
     * @param string $key 需要做为键的字段名
     * @return array
     */
    public static function convertToDictionary(array $array, $key)
    {
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
     * @return array
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
    public static function group(array $arr, $key_selector)
    {
        if (!isset($arr)) {
            return null;
        }

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
        if (!isset($arr)) {
            return null;
        }

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

    /**
     * @param $arr
     * @param $key
     * @return array
     */
    public static function assocUnique($arr, $key)
    {
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
    public static function toXml($data, $item = 'item', $id = 'id')
    {
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
     * @return array
     */
    public static function sortMultiTree($data, $first_level_data, $parent_key, $child_key)
    {
        $sort_data = [];
        foreach ($first_level_data as $first_level_datum) {
            $item     = static::sortRoleArray($data, $first_level_datum[$parent_key], $parent_key, $child_key);
            $sort_data[$first_level_datum[$parent_key]] = $first_level_datum;
            $sort_data = $sort_data + $item;//array_merge合并数组的话会产生索引键名覆盖的问题
        }
        return $sort_data;
    }

    /**
     * 辅助sortMultiTree方法的内部无限排序
     * @param $data
     * @param $name
     * @param $parent_key
     * @param $child_key
     * @return array
     */
    public static function sortRoleArray($data, $name, $parent_key, $child_key)
    {
        foreach ($data as $key => $value) {
            if ($value[$child_key] == $name) {
                $_data[$value[$parent_key]] = $value;
                $_data  += static::sortRoleArray($data, $value[$parent_key], $parent_key, $child_key);
            }
        }
        return isset($_data) ? $_data : [];
    }
}
