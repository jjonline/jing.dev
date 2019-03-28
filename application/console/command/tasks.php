<?php
/**
 * 异步任务Tcp服务器命令行入口
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-24 20:47
 * @file tasks.php
 */

namespace app\console\command;

use app\console\swoole\RedisServerManager;
use app\console\swoole\TaskEvent;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Exception;

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
        if (empty($env) || !in_array($env, ['dev','test','prod'])) {
            throw new Exception("Usage:`php think task [dev|test|prod]` Start Redis-Like Server.");
        }

        // redis-like server 管理器创建服务器
        RedisServerManager::getInstance()->createServer();

        // redis-like server 管理器绑定事件
        RedisServerManager::getInstance()->getServer()->on('Start', [TaskEvent::class, 'onStart']);
        RedisServerManager::getInstance()->getServer()->on('WorkerStart', [TaskEvent::class, 'onWorkStart']);
        RedisServerManager::getInstance()->getServer()->on('Connect', [TaskEvent::class, 'onConnect']);
        RedisServerManager::getInstance()->getServer()->on('Close', [TaskEvent::class, 'onClose']);

        // redis-like server 绑定redis客户端lpush命令
        RedisServerManager::getInstance()->getServer()->setHandler(
            'LPUSH',
            [TaskEvent::class, 'lPushReceiver']
        );

        // redis-like server 管理器开始启动运行
        RedisServerManager::getInstance()->start();
    }
}
