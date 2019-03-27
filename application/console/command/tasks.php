<?php
/**
 * 异步任务Tcp服务器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-24 20:47
 * @file tasks.php
 */

namespace app\console\command;

use app\common\helper\ArrayHelper;
use app\console\service\SwooleService;
use app\console\swoole\RedisServerManager;
use Swoole\Redis\Server;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Container;
use think\Exception;
use think\facade\Config;
use think\facade\Log;

class Tasks extends Command
{
    /**
     * 配置命令行参数、参数说明
     */
    protected function configure()
    {
        $this->setName('tasks')
             ->addArgument('env', Argument::OPTIONAL, "Environments Type.[dev|test|beta|prod]")
             ->setDescription('ASYNC Task Redis-Like Server.');
    }

    /**
     * 执行`php think tasks`的入口方法
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        if (!extension_loaded('swoole')) {
            throw new Exception("`php think task` need swoole php extension.");
        }
        $env = trim($input->getArgument('env'));
        $env = $env ?: 'dev';
        if (empty($env) || !in_array($env, ['dev','test','beta','prod'])) {
            throw new Exception("Usage:`php think task [dev|test|beta|prod]` Start Task TCP Server.");
        }

        RedisServerManager::getInstance()->createServer();
        RedisServerManager::getInstance()->start();
        return;

        // callBack functions
        $swooleService = new SwooleService();
        // init TCP Server
        $socket = Config::get('swoole.socket');
        $port   = Config::get('swoole.port');
        $ip     = Config::get('swoole.ip');
        if (!empty($ip)) {
            $output->writeln('IP模式开始执行:'.$ip);
            Log::record('IP模式开始执行:'.$ip);
            $server = new Server($ip, Config::get('swoole.port'), SWOOLE_BASE);
        } else {
            $output->writeln('Unix Socket模式开始执行:'.$socket);
            Log::record('Unix Socket模式开始执行:'.$socket);
            $server = new Server($socket, $port, SWOOLE_BASE, SWOOLE_UNIX_STREAM);
        }

        // Config
        $config = [
            'daemonize'       => Config::get('swoole.daemonize'),
            'pid_file'        => Config::get('swoole.pid_file'),
            'log_file'        => Container::get('app')->getRuntimePath().'/log/swoole.log',
            'task_worker_num' => Config::get('swoole.task_worker_num'),
            'worker_num'      => Config::get('swoole.worker_num'),
        ];
        // 判断是否启用子进程指定用户
        if (Config::get('swoole.user')) {
            $config['user']  = Config::get('swoole.user');
        }
        $server->set($config);

        // onWorkerStart Event
        $server->on('WorkerStart', [$swooleService,'onWorkerStart']);

        // onConnect Event 连接时触发的事件，主要用于debug
        $server->on('connect', [$swooleService,'onConnect']);

        // lpush往队列推任务的handler--redis的lpush命令处理函数
        $server->setHandler('LPUSH', function ($fd, $data) use ($server, $output) {
            try {
                /**
                 * 确保传递给task异步执行的参数是pair形式的数组
                 */
                if (!is_array($data) || count($data) < 2) {
                    throw new Exception('写入队列的数据格式有误:'.json_encode($data, JSON_UNESCAPED_UNICODE));
                }
                // 一次往队列里塞入多个值的情况，拆分成多个任务塞进去
                if (is_array($data) && count($data) > 2) {
                    $result = ArrayHelper::segmentToPairArray($data);
                } else {
                    $result = [$data];
                }
                foreach ($result as $key => $value) {
                    $task_id  = $server->task($value);
                    if ($task_id === false) {
                        throw new Exception('拒绝任务：'.json_encode($value, JSON_UNESCAPED_UNICODE));
                    }
                    $value['task_id'] = $task_id;
                    $output->writeln('接收任务：List='.$value[0].',RunningId='.$task_id);
                    Log::record('接收任务'.$value[0].':'.json_encode($value, JSON_UNESCAPED_UNICODE));
                }

                // 向客户端返回队列长度
                $server->send($fd, Server::format(Server::INT, count($result)));
            } catch (\Throwable $e) {
                $output->writeln($e->getMessage());
                Log::record($e->getMessage());
                $server->send($fd, Server::format(Server::ERROR));
            }
        });

        // Run Task 使用swoole的task进程异步执行任务的核心
        $server->on('task', [$swooleService,'onTask']);

        // onFinish Event
        $server->on('finish', [$swooleService,'onFinish']);

        // onClose Event TCP连接关闭事件
        $server->on('close', [$swooleService,'onClose']);

        // start Server
        $server->start();
    }
}
