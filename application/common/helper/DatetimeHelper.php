<?php
/**
 * 日期时间帮助类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-04
 *
 */

namespace app\common\helper;

class DatetimeHelper
{
    /**
     * linux时间戳转自然语言字符串 刚刚、1分钟前、3小时前、昨天9:01、9-11 11:01、2017-11-11 11:10
     * @param int $time
     * @return false|string
     */
    public static function timeToNatural($time)
    {
        $sysTime = time();
        $gap     = $sysTime - $time;
        $oBefore = strtotime(date("Y-m-d")); //今天0点时间戳
        $tBefore = strtotime(date("Y-m-d", strtotime("-1 day"))); //昨天0点时间戳
        $sBefore = strtotime(date("Y-m-d", strtotime("-2 day"))); //前天0点时间戳
        $y       = date("y", $time); // 时间的年份
        $py      = date("y", $sysTime); //今年的年份
        if ($gap < 60) {
            // 1分钟内，显示 刚刚
            $str = '刚刚';
        } elseif ($gap < 3600) {
            // 1小时内，显示 XX分钟前
            $str = round($gap / 60) . '分钟前';
        } elseif ($time > $oBefore) {
            // 1小时~昨天之前，显示 XX小时前
            $str = '今天 '.date('H:i', $time);
        } elseif ($time > $tBefore) {
            // 昨天0点~24点，显示 昨天 HH:mm
            $str = '昨天 ' . date("H:i", $time);
        } elseif ($time > $sBefore) {
            // 前天0点~24点，显示 前天 HH:mm
            $str = '前天 ' . date("H:i", $time);
        } elseif ($py <= $y) {
            // 前天0点之前，显示 MM-dd HH:mm
            $str = date("m-d H:i", $time);
        } else {
            // 不是当年的，显示 yyyy-MM-dd HH:mm
            $str = date("Y-m-d H:i", $time);
        }
        return $str;
    }

    /**
     * 获取日期字符串
     * @param string $input 完整时间字符串
     */
    public static function getDate($input, $max = null, $min = null)
    {
        if (empty($input)) {
            return '';
        } else {
            $target = strtotime($input);

            if (!empty($min)) {
                $minTime = strtotime($min);
                if ($target < $minTime) {
                    $target = $minTime;
                }
            }

            if (!empty($max)) {
                $maxTime = strtotime($max);
                if ($target > $maxTime) {
                    $target = $maxTime;
                }
            }

            return date('Y-m-d', $target);
        }
    }

    /**
     * 根据时间戳获取时间字符串
     */
    public static function formatTime($time, $timezone = 'PRC')
    {
        date_default_timezone_set($timezone);
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 获取x个小时后的整点时间
     */
    public static function getRelativeWholeTimeByHours($time, $hours)
    {
        if (strtotime(date("Y-m-d H", $time) . ":00:00") == $time) {
            return strtotime("+{$hours} hours", $time);
        } else {
            $addHours = $hours + 1;
            $relTime = strtotime("+{$addHours} hours", $time);
            return strtotime(date("Y-m-d H", $relTime) . ":00:00");
        }
    }

    /**
     * 当前系统时间，精确到秒
     * @param string $timezone 时区
     * @return bool|string
     */
    public static function now($timezone = 'PRC')
    {
        date_default_timezone_set($timezone);
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 当前系统日期
     * @param string $timezone 时区
     * @return bool|string
     */
    public static function today($timezone = 'PRC')
    {
        date_default_timezone_set($timezone);
        return date('Y-m-d', time());
    }

    /**
     * 返回年月的上下年月
     * @param string $year 年
     * @param string $month 月
     * @param string $num 正负月份数
     * @return array
     */
    public static function getYearMonthByYmn($year, $month, $num = '+1')
    {
        $date   = date('Y-m', strtotime("{$num} month", strtotime("{$year}-{$month}")));
        $result = explode('-', $date);
        return [
            'y' => intval($result[0]),
            'm' => intval($result[1]),
        ];
    }

    /**
     * 返回当月5号
     * @return bool|string
     */
    public static function fifthDayOfAMonth()
    {
        return date('Y-m-05 00:00:00');
    }

    /**
     * @param $val
     * @param string $format N时7为周日，w时0为周日
     * @return string
     */
    public static function formatDateN($val, $format = 'N')
    {
        $num = ($format === 'N') ? 7 : 0;
        switch ($val) {
            case 1:
                return '周一';
            case 2:
                return '周二';
            case 3:
                return '周三';
            case 4:
                return '周四';
            case 5:
                return '周五';
            case 6:
                return '周六';
            case $num:
                return '周日';
        }
        return '';
    }

    /**
     * 求两个日期之间周期包含的天数，比如1月1日到1月2日，周期为2天
     * @param string $day1 可转换为linux时间戳的开始时间字符串
     * @param string $day2 可主啊喂linux时间戳的结束时间字符串
     * @return number
     */
    public static function durationDays($day1, $day2)
    {
        $second1 = strtotime(date('Y-m-d', strtotime($day1)));
        $second2 = strtotime(date('Y-m-d', strtotime($day2)));
        return abs(intval(($second1 - $second2) / 86400)) + 1;
    }

    /**
     * 返回日期的年和月
     * @param string $date
     * @return array
     */
    public static function getYearMonthByDate($date)
    {
        $time = strtotime($date);
        return [
            'y' => date('Y', $time),
            'm' => date('n', $time)
        ];
    }

    /**
     * 验证日期格式
     * @param $date
     * @param string $format
     * @return bool
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }


    public static function getDurationByMonth($yearMonth)
    {
        $begin_date = $yearMonth . '-01';
        $end_date   = date('Y-m-d', (strtotime('-1 day', (strtotime("+1 months", (strtotime($yearMonth . '-01')))))));
        
        return [
            'begin_date' => $begin_date ,
            'end_date' => $end_date
        ];
    }

    /**
     * 获取日期当月最后一天
     * @param $date
     * @return false|string
     */
    public static function getMonthLastDay($date)
    {
        $firstDay = date('Y-m-01', strtotime($date));
        $lastDay  = date('Y-m-d H:i:s', strtotime("$firstDay +1 month -1 day"));
        return $lastDay;
    }

    /**
     * 获取日期当月第一天
     * @param $date
     * @return false|string
     */
    public static function getMonthFirstDay($date)
    {
        return date('Y-m-01 H:i:s', strtotime($date));
    }

    /**
     * 计算两个日期的天数差
     * @param string $date1 日期
     * @param string $date2 日期
     * @return bool|string 日期格式错误将返回false
     */
    public static function diffDays($date1, $date2)
    {
        $second1 = strtotime($date1);
        $second2 = strtotime($date2);
        if ($second1 === false || $second2 === false) {
            return false;
        }
        $second1 = strtotime(date('Y-m-d', $second1));
        $second2 = strtotime(date('Y-m-d', $second2));
        return bcadd(abs(($second1 - $second2) / 86400), 0, 0);
    }

    /**
     * 计算两个日期的月份差
     * @param string $bigDate 大的日期
     * @param string $smallDate 小的日期
     * @param bool $bcDay 是否精确到天，算天则按一个月30天算，保留两位小数。
     * @param bool $hasAbs 是否算绝对值，不算绝对值，当$bigDate小于$smallDate会返回负的差数
     * @return bool|string 日期格式错误将返回false
     */
    public static function diffMonths($bigDate, $smallDate, $bcDay = false, $hasAbs = false)
    {
        $big   = strtotime($bigDate);
        $small = strtotime($smallDate);
        if ($big === false || $small === false) {
            return false;
        }
        $m1   = (date('Y', $big) - date('Y', $small)) * 12;
        $m2   = date('n', $big) - date('n', $small);
        $diff = bcadd($m1, $m2, 0);
        if (!$bcDay) {
            return $hasAbs ? bcadd(abs($diff), 0, 0) : $diff;
        }
        $m3   = (date('j', $big) - date('j', $small)) / 30;
        $diff = bcadd($diff, $m3, 2);
        return $hasAbs ? bcadd(abs($diff), 0, 2) : $diff;
    }

    /**
     * 从指定datetime时间开始，输出下一个周几的某个日期时间或时间戳
     * 若datetime表示为周三的时间，toTime = 18:00:00
     * toWeek = 4, 则获得本周四的18:00:00
     * toWeek = 2, 则获得下周二的18:00:00
     * toWeek = 0, 则获得本周日的18:00:00
     * toWeek = 3, 当datetime指定18点之前, 则获得datetime当日（本周三）的18:00:00
     * toWeek = 3, 当datetime指定18点之后, 则获得下周三的18:00:00
     * @param $datetime string 开始时间 2017-09-01 18:00:00
     * @param $toWeek int 指定星期中的第几天 0（表示星期天）到 6（表示星期六）
     * @param $toTime string 时间点，如18:00:00
     * @param bool $isTimestamp 是否输出时间戳，否则输出具体日期时间
     * @return bool|int|string
     */
    public static function getNextWeekTime($datetime, $toWeek, $toTime, $isTimestamp = true)
    {
        $timestamp = strtotime($datetime);
        $w = date('w', $timestamp);
        if ($toWeek < 0 || $toWeek > 6) {
            return false;
        }
        if ($w == $toWeek) {
            $date = date('Y-m-d', $timestamp);
            $toTimestamp = strtotime("{$date} {$toTime}");
            if ($timestamp < $toTimestamp) {
                return $isTimestamp ? $toTimestamp : "{$date} {$toTime}";
            } else {
                $afterTime = strtotime('+7 days', $timestamp);
                return $isTimestamp ? $afterTime : date("Y-m-d {$toTime}", $afterTime);
            }
        } else {
            $addDay = $w < $toWeek ? $toWeek - $w : 7 - $w + $toWeek;
            $afterTime = strtotime("+{$addDay} days", $timestamp);
            return $isTimestamp ? $afterTime : date("Y-m-d {$toTime}", $afterTime);
        }
    }

    /**
     * 根据出生日期计算年龄
     * @param $birthday
     * @return bool|int
     */
    public static function getAge($birthday)
    {
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }
        list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
        $now = strtotime("now");
        list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
        $age = $y2 - $y1;
        if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
            $age -= 1;
        }
        return $age;
    }

    /**
     * 根据出生日期获取星座
     * @param $birthday
     * @return bool|string
     */
    public static function getOccupation($birthday)
    {
        $signs = [
            ['20' => '宝瓶座'],
            ['19' => '双鱼座'],
            ['21' => '白羊座'],
            ['20' => '金牛座'],
            ['21' => '双子座'],
            ['22' => '巨蟹座'],
            ['23' => '狮子座'],
            ['23' => '处女座'],
            ['23' => '天秤座'],
            ['24' => '天蝎座'],
            ['22' => '射手座'],
            ['22' => '摩羯座']
        ];
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }

        list($m1, $d1) = explode("-", date("m-d", $age));

        //星座
        $key = (int)$m1 - 1;
        list($startSign, $signName) = each($signs[$key]);
        if ($d1 < $startSign) {
            $key = $m1 - 2 < 0 ? $m1 = 11 : $m1 -= 2;
            $signName = current($signs[$key]);
        }
        return $signName;
    }

    /**
     * 获取两个时间段交集的天数
     * @param $beginDate1
     * @param $endDate1
     * @param $beginDate2
     * @param $endDate2
     * @return bool|int|string
     */
    public static function intersectDays($beginDate1, $endDate1, $beginDate2, $endDate2)
    {
        if (empty($beginDate1) || empty($endDate1)  || empty($beginDate2)  || empty($endDate2) || $beginDate1 >= $endDate1 || $beginDate2 >= $endDate2) {
            return false;
        }

        $days = 0;
        if ($beginDate1 < $beginDate2 && $endDate1 >= $beginDate2) {
            $endDate = min($endDate1, $endDate2);
            $days = static::diffDays($beginDate2, $endDate);
        } elseif ($beginDate1 >= $beginDate2 && $beginDate1 <= $endDate2) {
            $endDate = min($endDate1, $endDate2);
            $days = static::diffDays($beginDate1, $endDate);
        }

        return $days;
    }

    /**
     * 格式化当前时间至星期X的表示法
     * @param $time string 时间|日期字符表示法，能经strtotime转换为正常Unix时间戳
     */
    public static function formatDayToWeek($time = '')
    {
        $time = empty($time) ? 'now' : $time;
        $week = ['日','一','二','三','四','五','六'];
        $key  = date('w', strtotime($time));
        return '星期'.$week[$key];
    }

    /**
     * 获取开始时间（含）和结束时间（含）内正常日期数组
     * @param string $day1 可转换为linux时间戳的开始时间字符串
     * @param string $day2 可主啊喂linux时间戳的结束时间字符串
     * @return []
     */
    public static function getPeriodDaysArray($day1, $day2)
    {
        $begin_time = strtotime($day1);
        $end_time   = strtotime($day2);
        if ($begin_time > $end_time) {
            return [];
        }
        $duration   = self::durationDays($day1, $day2);
        $period_arr = [];
        for ($i = 0; $i < $duration; $i++) {
            $period_arr[] = date('Y-m-d', $begin_time + 86400 * $i);
        }
        return $period_arr;
    }

    /**
     * 获取今日开始时间和结束时间的一维数组
     * @return ['2018-01-16 00:00:00','2018-01-16 24:00:00']
     */
    public static function getTodayBeginAndEndArray()
    {
        return [
            date('Y-m-d 00:00:00'),
            date('Y-m-d 00:00:00', strtotime('+1 days'))
        ];
    }

    /**
     * 获取到金额日24点剩余的秒数
     * @return false|int
     */
    public static function getTodayRemainingSeconds()
    {
        $tomorrow_begin = strtotime(date('Y-m-d', strtotime('+1 days')));
        $now            = time();
        return $tomorrow_begin - $now;
    }
}
