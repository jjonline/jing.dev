<?php
/**
 * swoole相关帮助函数
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-28 22:59
 * @file SwooleHelper.php
 */

namespace app\console\swoole\framework;

class SwooleHelper
{
    public static $server_id;
    /**
     * @var bool 记录是否存在cli_set_process_title函数避免多次调用函数检查
     */
    public static $exist_cli_func;

    /**
     * 待缓存的获取服务器编号sid，用于
     * @return string
     */
    public static function getServerSid()
    {
        if (empty(self::$server_id)) {
            self::$server_id = md5(gethostname());
        }
        return self::$server_id;
    }

    /**
     * cli设置进程名称
     * @param string $process_name
     * @return bool
     */
    public static function setProcessName($process_name = '')
    {
        // mac不支持
        if (PHP_OS == 'Darwin') {
            return false;
        }
        if (empty($process_name)) {
            return false;
        }
        if (null === self::$exist_cli_func) {
            self::$exist_cli_func = function_exists('cli_set_process_title');
        }
        if (self::$exist_cli_func) {
            cli_set_process_title($process_name);
        } else {
            swoole_set_process_name($process_name);
        }
        return true;
    }
}
