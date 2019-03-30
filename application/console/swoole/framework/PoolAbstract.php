<?php
/**
 * 连接池抽象类，实现连接池管理实现该类即可
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-30 12:44
 * @file PoolAbstract.php
 */

namespace app\console\swoole\framework;

use Swoole\Coroutine\Channel;

abstract class PoolAbstract
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var integer 连接池最大尺寸
     */
    protected $pool_size;

    /**
     * @param int $pool_size 连接池的尺寸
     */
    public function __construct($pool_size = 100)
    {
        $this->pool_size = $pool_size;
    }

    /**
     * 压入需连接池维护的对象至pool中
     * @param $pool_handler
     */
    public function put($pool_handler)
    {
        $this->pool->push($pool_handler);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->pool->pop();
    }

    /**
     * 关闭连接池
     */
    public function close()
    {
        $this->pool->close();
        $this->pool = null;
    }
}
