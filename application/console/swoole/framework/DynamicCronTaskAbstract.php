<?php
/**
 * 基于swoole的定时任务实现抽象类
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * 开发和使用步骤：
 * ---
 * 1、实现本抽象类
 * 2、在实现的子类中实现`run`方法：即在定时到达时刻被执行的业务逻辑
 * 3、使用TaskHelper::deliveryCronTask()在fpm进程中投递定时任务，注意参数说明
 * 4、使用TaskHelper::clearCronTask()在fpm进程中清除取消定时任务，注意参数说明
 * ---
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-10-02
 * @file DynamicCronTaskAbstract.php
 */

namespace app\console\swoole\framework;

abstract class DynamicCronTaskAbstract
{
    /**
     * 动态定时触发的动作执行逻辑
     * @param mixed $param 任务参数
     * @return bool
     */
    abstract public static function run($param):bool;
}
