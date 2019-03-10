<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-18 23:04
 * @file NumberHelper.php
 */

namespace app\common\helper;

class NumberHelper
{
    /**
     * 四舍五入保留小数处理
     * 与PHP原生round函数相似，只不过添加了兼容带半角逗号的数字类型的参数以及返回值为字符串类型
     * @param mixed $num     数字以及带半角逗号的数字
     * @param int $precision 保留小数点的精度位数
     * @param int $mode      模式
     * @return string
     */
    public static function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP)
    {
        $num = str_replace(',', '', $num);//去除带半角逗号的数字计数法字符串中的半角逗号
        // 返回字符串形式的小数
        return round($num, $precision, $mode).'';
    }
}
