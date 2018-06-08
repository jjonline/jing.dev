<?php
/**
 * Init初始化配置文件
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-16 14:44
 * @file init.php
 */

namespace app\console\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class init extends Command
{
    protected function configure()
    {
        $this->setName('init')
            //->addArgument('env', Argument::OPTIONAL, "Environments Type.[dev|test|beta]")
            ->addOption('env', null, Option::VALUE_REQUIRED, 'Set Environments Type.[dev|test|beta]')
            ->setDescription('Init Develop Environments.');
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
        $env = strtolower($input->getOption('env'));
        if(empty($env) || !in_array($env,['dev','test','beta']))
        {
            throw new Exception("Usage:`php think init --env=dev` init development Environments");
        }
        $project_dir = realpath(__DIR__.'/../../../');
        $this->circleCopyFile($project_dir.'/environments/'.$env,
                                  $project_dir.'/config',$output);
        $output->writeln("<info>Init Finish.<info>");
    }

    /**
     * 循环复制文件夹下文件
     * @param $destination
     * @param $target
     * @param Output $output
     */
    protected function circleCopyFile($destination,$target, Output $output)
    {
        $destination = rtrim($destination, '/') . '/';
        $target      = rtrim($target, '/') . '/';
        $iterator    = new \DirectoryIterator($destination);
        while($iterator->valid())
        {
            // 文件
            if($iterator->isFile() && $iterator->getExtension() == 'php')
            {
                $destination_file = $destination.$iterator->getFilename();
                $target_file      = $target.$iterator->getFilename();
                $result           = copy($destination_file,$target_file);
                if($result)
                {
                    $output->writeln('<info>Copy File:'.$iterator->getFilename().'</info>');
                }
            }
            // 目录
            if($iterator->isDir() && !$iterator->isDot())
            {
                $_target = $target.$iterator->getFilename();
                // 目录不存在，新建目录
                if(!file_exists($_target))
                {
                    mkdir($_target);
                }
                $this->circleCopyFile($destination.$iterator->getFilename(),$_target,$output);
            }
            $iterator->next();
        }
    }
}
