<?php
/**
 * 基于swoole的定时任务实现抽象类
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * 开发和使用步骤：
 * ---
 * 1、在目录app\console\task\cron\下新建继承本抽象类的定时任务类
 * 2、实现rule方法，方法体返回cron定时规则字符串即可
 * 3、实现name方法，方法体返回本定时任务的字符串名称，建议使用不带命名空间的类名
 * 4、实现run方法，方法体是在rule方法指定的定时规则下被具体执行的任务
 * ---
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-25
 * @file StaticCronTaskAbstract.php
 */
namespace app\console\swoole\framework;

abstract class StaticCronTaskAbstract
{
    /**
     * 设置cron形式的定时规则字符串即可，即在方法中设置定时执行的时间规则并return
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *
     *  *    *    *    *    *
     *  -    -    -    -    -
     *  |    |    |    |    |
     *  |    |    |    |    |
     *  |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
     *  |    |    |    +---------- month (1 - 12)
     *  |    |    +--------------- day of month (1 - 31)
     *  |    +-------------------- hour (0 - 23)
     *  +------------------------- min (0 - 59)
     *
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @return string
     */
    abstract public static function rule():string;

    /**
     * 设置cron任务的名称，字符串，同名会被覆盖，建议使用类名
     * @return string
     */
    abstract public static function name():string;

    /**
     * rule规则制定的定时被执行的任务方法，定时被执行的任务在此方法中实现
     * 返回数组：
     *  1、第一个元素bool值true执行成功false执行失败
     *  2、第二个元素需要回写至Db中的结果内容，字符串或数组
     * 注意try-catch异常
     * @return [bool,mixed]
     */
    abstract public static function run():array;
}
