<?php
/**
 * Init初始化配置文件
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-16 14:44
 * @file init.php
 */

namespace app\console\command;

use app\common\helper\FilterValidHelper;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class password extends Command
{
    protected function configure()
    {
        $this->setName('password')
            //->addArgument('env', Argument::OPTIONAL, "Environments Type.[dev|test|beta]")
            ->addOption('text', null, Option::VALUE_REQUIRED, 'Crypt Password Text.')
            ->setDescription('Generate Password Encrypt Depend on Config.');
    }

    /**
     * cli模式下项目初始化
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $text = strtolower($input->getOption('text'));
        if (empty($text)) {
            throw new Exception("Usage:`php think password --text=Your Password Text` Generate Password Crypt String.");
        }
        // 检查密码是否符合规范--字符和数字
        if (!FilterValidHelper::is_password_valid($text)) {
            throw new Exception("Password Text Format Not Allowed.Text Need Word And Number,length at least 8");
        }
        $crypt_text = password_hash(config('local.auth_key').trim($text), PASSWORD_BCRYPT);
        $output->writeln($crypt_text);
    }
}
