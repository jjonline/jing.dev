<?php
/**
 * 非CronTaskAbstract类型cron定时任务管理器，管理自定义类型定时任务
 * ---
 * 1、进程重载时已有未完成任务丢失需对应重载
 * 2、Redis作为接收任务的FIFO通道和桥梁，但Redis仍存在重载数据丢失的可能
 * 3、使用Db作为持久存储方案
 * ---
 */

namespace app\console\swoole\framework;

use app\common\helper\TaskHelper;
use app\console\swoole\RedisServerManager;
use Cron\CronExpression;
use Swoole\Coroutine;
use Swoole\Timer;
use think\facade\Cache;

class DynamicCronManager
{
    /**
     * redis list开始定时任务存储结构[通过redis投递动态定时任务的通道队列]
     * [
     *      'subClass Name with namespace',
     *      'param of subClass Method of run',
     *      'cron expression',
     *      'business_id',
     *      'perhaps exit cron time',
     * ]
     */

    /**
     * redis hash存储结构[映射定时任务与time ticker编号的哈希表]
     * [
     *      'subClass Name with namespace',
     *      'business_id',
     *      [
     *           'tick_id' => 'swoole time ticker id',
     *           'task'    => [
     *                  'subClass Name with namespace',
     *                  'param of subClass Method of run',
     *                  'cron expression',
     *                  'business_id',
     *                  'perhaps exit cron time',
     *            ]
     *      ],
     * ]
     */

    /**
     * redis list终止定时任务存储结构[通过redis投递动态定时任务的通道队列]
     * [
     *      'subClass Name with namespace',
     *      'business_id',
     * ]
     */

    /**
     * @var string 动态定时任务队列名
     */
    const CRON_QUEUE_NAME = 'dynamic_cron_queue';
    /**
     * @var string 动态定时任务哈希表名前缀
     */
    const CRON_HASH_NAME  = 'dynamic_cron_hash';
    /**
     * @var string 动态定时任务管理（清理）队列名
     */
    const CRON_MANAGE_NAME  = 'dynamic_manage_queue';

    /**
     * @var \Redis
     */
    private static $Redis;

    /**
     * 动态定时任务唯一入口
     */
    public static function init()
    {
        self::$Redis = Cache::handler();

        // 检查进程重载导致的丢失任务并重载
        self::checkInterruptByReload();

        // 清理、控制循环进程
        self::beginManageLoop();

        // 开始处理任务队列循环
        self::beginQueueLoop();
    }

    /**
     * 检查被进程重载中断的定时任务并重载
     */
    protected static function checkInterruptByReload()
    {
        // 在迭代完成之前，不返回空值
        self::$Redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $iterator  = null;
        $is_reload = false;
        while($elements = self::$Redis->hScan(self::CRON_HASH_NAME, $iterator)) {
            foreach($elements as $key => $value) {
                $is_reload = true;
                $hash_one = json_decode($value, true);
                if (!empty($hash_one) && 2 == count($hash_one)) {
                    self::setOneCronTask($hash_one['task']);
                }
            }
        }

        if ($is_reload) {
            RedisServerManager::getInstance()->logGreen(
                RedisServerManager::CRON_PROCESS." dynamic cron check and reload",
                'cron'
            );
        } else {
            RedisServerManager::getInstance()->log(
                RedisServerManager::CRON_PROCESS." dynamic cron check and do not need reload",
                'cron'
            );
        }
    }

    /**
     * 开始每秒的定时任务管理进程loop
     */
    protected static function beginManageLoop()
    {
        // 每1s处理1次定时任务队列，半实时
        Timer::tick(1000, function () {
            $one_manage = self::$Redis->lPop(self::CRON_MANAGE_NAME);
            if (!empty($one_manage)) {
                $manage = json_decode($one_manage, true);
                if (!empty($manage) && 2 == count($manage)) {
                    list($class_name, $business_id) = $manage;
                    self::clearOneCronTask($class_name, $business_id);
                }
            }
        });
    }

    /**
     * 轮询redis list队列中的任务
     */
    protected static function beginQueueLoop()
    {
        // 每29s处理1次定时任务队列，分钟级任务
        Timer::tick(29000, function () {
            // 带递归的处理定时任务队列
            self::dealCronTaskQueue();
        });
    }

    /**
     * 处理定时任务队列，内部自动判断递归，避免队列任务过多导致排队时间过长
     */
    protected static function dealCronTaskQueue()
    {
        $one_task = self::$Redis->lPop(self::CRON_QUEUE_NAME);
        if (!empty($one_task)) {
            // 处理1条定时任务
            self::setOneCronTask(json_decode($one_task, true));

            // 检查队列长度，队列不为空时则再次递归
            $queue_len = self::$Redis->lLen(self::CRON_QUEUE_NAME);
            if (!empty($queue_len) && $queue_len > 0) {
                // 继续执行
                self::dealCronTaskQueue();
            }
            return true;
        }
        return false;
    }

    /**
     * 设置1条定时任务，采用倒计时的方式实现
     * @param mixed $one_item
     * @return bool
     */
    protected static function setOneCronTask($one_item)
    {
        try {
            if (empty($one_item) || 5 != count($one_item)) {
                return false;
            }

            // 拆分队列item
            list($class_name, $param, $expression, $business_id, $perhaps_exit_time) = $one_item;
            if (empty($class_name)) {
                return false;
            }

            /**
             * 检查定时任务规则，是否需要设置定1个倒计时任务
             * ---
             * 1、生成倒计时人物的计时毫秒数
             * 2、若任务不需要再次执行则清理该ticker的哈希表记录
             * ---
             */
            $after_time = self::parseCronExpressionToOnce($expression, $perhaps_exit_time);
            if (empty($after_time)) {
                // 清理该tick循环
                return self::clearOneCronTask($class_name, $business_id);
            }

            // 检查任务类型
            $reflect = new \ReflectionClass($class_name);
            if (!$reflect->isSubclassOf(DynamicCronTaskAbstract::class)) {
                return false;
            }

            // 构造任务参数
            $task_data          = [];
            $task_data['task']  = $class_name;
            $task_data['param'] = $param;
            $task_data['data']  = $one_item;

            // 开始1个timer
            $tick_id = Timer::after($after_time, function () use ($task_data) {
                // 倒计时触发之后通过投递异步任务到task进程执行
                $send_status = RedisServerManager::getInstance()->processAsync($task_data);
                if ($send_status) {
                    RedisServerManager::getInstance()->logGreen(
                        RedisServerManager::CRON_PROCESS." Send `{$task_data['task']}` Success",
                        'cron'
                    );
                } else {
                    RedisServerManager::getInstance()->logError(
                        RedisServerManager::CRON_PROCESS." Send `{$task_data['task']}` Fail.",
                        'cron'
                    );
                }

                // 等待0.1秒后再次投递
                Coroutine::sleep(0.1);

                // tick完毕，通过任务投递形式再次投递尝试执行或销毁，避免不同进程间直接set任务导致tick在不同进程无法准确clear
                list($class_name, $param, $expression, $business_id, $perhaps_exit_time) = $task_data['data'];
                TaskHelper::deliveryCronTask($class_name, $param, $expression, $business_id, $perhaps_exit_time);
            });

            // 维护hash
            $hash_field = md5($class_name) . $business_id;
            $hash_item  = [
                'tick_id' => $tick_id,
                'task'    => $one_item
            ];

            // 设置任务
            RedisServerManager::getInstance()->logGreen("set tick with {$class_name} and {$business_id} success");

            return self::$Redis->hSet(self::CRON_HASH_NAME, $hash_field, json_encode($hash_item));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 解析日期时间|标准cron表达式出最近1次执行时间剩余的毫秒数
     * @param string $expression cron表达式或定时执行的日期时间
     * @param string $perhaps_exit_time 可能的无限循环的终止时间
     * @return bool|int
     */
    protected static function parseCronExpressionToOnce($expression, $perhaps_exit_time = null)
    {
        try {
            // 如果有设置终止时刻，切当前时刻已过终止时刻则终止
            if (!empty($perhaps_exit_time)) {
                $exit_time = is_numeric($perhaps_exit_time) ? $perhaps_exit_time : strtotime($perhaps_exit_time);
                if (time() >= $exit_time) {
                    return false;
                }
            }

            // 时间戳类型，仅在指定时间执行1次
            if (is_numeric($expression)) {
                $timer = $expression - time(); // 剩余秒数
                return $timer <= 0 ? false : (int)$timer * 1000; // 转换为毫秒返回
            }

            // 检查是否是一个日期时间字符串
            $timer = strtotime($expression);
            if (!empty($timer)) {
                $timer = $timer - time(); // 剩余秒数
                return $timer <= 0 ? false : (int)$timer * 1000; // 转换为毫秒返回
            }

            // 可能是1个标准的cron表达式，则获取最近1次需执行的时刻
            $next_run_time = CronExpression::factory($expression)->getNextRunDate();
            return (int)($next_run_time->getTimestamp() - time()) * 1000;
        } catch (\Throwable $e) {
            RedisServerManager::getInstance()->logError(
                RedisServerManager::CRON_PROCESS." parse expression `{$expression}` error: " . $e->getMessage(),
                'DynamicCRon'
            );
            return false;
        }
    }

    /**
     * 清理掉1条已执行的任务
     * @param string $class_name  任务类class名
     * @param mixed  $business_id 任务业务id
     * @return bool
     */
    protected static function clearOneCronTask($class_name, $business_id)
    {
        try {
            // 支持业务id为0的设置
            if (empty($class_name) || is_null($business_id)) {
                return false;
            }

            $hash_field = md5($class_name) . $business_id;
            $hash_item  = self::$Redis->hGet(self::CRON_HASH_NAME, $hash_field);
            if (empty($hash_item)) {
                return false;
            }

            // 清理掉该hash的item
            self::$Redis->hDel(self::CRON_HASH_NAME, $hash_field);

            $task_map = json_decode($hash_item, true);
            if (empty($task_map) || 2 != count($task_map)) {
                return false;
            }

            // 清理tick
            $tick_id = $task_map['tick_id'] ?? 0;
            if (empty($tick_id)) {
                return false;
            }

            // 完成了清理日志
            RedisServerManager::getInstance()->logGreen("clear tick with {$class_name} and {$business_id}");

            // 无需检查该tick是否存在
            return Timer::clear($tick_id);
        } catch (\Throwable $e) {
            RedisServerManager::getInstance()->logError(
                RedisServerManager::CRON_PROCESS." clear timer ticker error: " . $e->getMessage(),
                'DynamicCRon'
            );
            return false;
        }
    }
}
