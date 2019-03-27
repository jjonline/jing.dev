<?php
/**
 * 单例Trait
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27 23:15
 * @file SingleTone.php
 */
namespace app\console\swoole\framework;

use think\console\Output;

trait SingletonTrait
{
    /**
     * @var self 单例对象
     */
    private static $instance;
    /**
     * @var Output 输出方法对象
     */
    private static $outPut;

    /**
     * 单例，禁用构造函数直接调用
     * SingletonTrait constructor.
     * @param mixed ...$args
     */
    protected function __construct(...$args)
    {
    }

    /**
     * 获取单例对象
     * @param mixed ...$args
     * @return self
     */
    public static function getInstance(...$args)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static(...$args);
            self::$outPut   = new Output();
        }
        return self::$instance;
    }

    /**
     * 输出日志
     * @param mixed $log
     * @param string $tag
     */
    public function log($log, $tag = 'info')
    {
        self::$outPut->writeln($this->dealLog($log, $tag));
    }

    /**
     * 输出绿色文字log-如果支持的话
     * @param mixed $log
     * @param string $tag
     */
    public function greenLog($log, $tag = 'info')
    {
        self::$outPut->info($this->dealLog($log, $tag));
    }

    /**
     * 组装日志
     * @param mixed $log
     * @param string $tag
     * @return string
     */
    private function dealLog($log, $tag)
    {
        $date_time = date_create();
        $_log      = "[{$tag}][{$date_time->format('m-d H:i:s.u')}]";
        if (is_string($log)) {
            $_log .= $log;
        } else {
            $_log .= json_encode($log, JSON_UNESCAPED_UNICODE);
        }
        unset($date_time);
        return $_log;
    }
}
