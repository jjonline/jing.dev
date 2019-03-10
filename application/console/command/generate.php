<?php
/**
 * 自动生成后台列表相关控制器、服务、模型、视图和js文件
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-01-12 20:39
 * @file generate.php
 */
namespace app\console\command;

use app\common\helper\StringHelper;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class generate extends Command
{
    protected function configure()
    {
        $this->setName('generate')
            //->addArgument('env', Argument::OPTIONAL, "Environments Type.[dev|test|beta]")
            ->addOption('controller', null, Option::VALUE_REQUIRED, '设置控制器，例如：Member')
            ->addOption('name', null, Option::VALUE_REQUIRED, '设置列表名称，例如：会员')
            ->addOption('force', null, Option::VALUE_REQUIRED, '设置是否覆盖型生成，传值1则覆盖，默认不覆盖')
            ->setDescription('开发工具，一键自动生成后台列表类相关代码.');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $controller = $input->getOption('controller');
        if (empty($controller)) {
            throw new Exception("Usage:`php think generate --controller=xxx --name=xxx`");
        }
        $name = strtolower($input->getOption('name'));
        if (empty($name)) {
            throw new Exception("Usage:`php think generate --controller=xxx --name=xxx`");
        }
        $force = !empty($input->getOption('force'));
        $this->circleGenerateFile(
            $controller,
            $name,
            $force,
            $output
        );
        $output->writeln("<info>Generate Finish.<info>");
    }

    /**
     * 循环按模板生成文件
     * @param $controller
     * @param $name
     * @param $forece
     * @param Output $output
     * @throws Exception
     */
    protected function circleGenerateFile($controller, $name, $force, Output $output)
    {
        // 目标文件名称和目录
        $project_dir         = realpath(__DIR__ . '/../../../');
        $project_dir         = rtrim($project_dir, '/') . '/';
        $file_name           = StringHelper::toUcCamelCase($controller); // 控制器首字母大写名称
        $file_lower_name     = StringHelper::toCamelCase($controller);// 控制器首字母小写名称
        $file_under_name     = StringHelper::toUnderScore($controller);// 控制器转为全小写的下划线风格
        $controller_file_dir = $project_dir . 'application/manage/controller/';// 控制器文件路径
        $model_file_dir      = $project_dir . 'application/manage/model/';// 模型文件路径
        $service_file_dir    = $project_dir . 'application/manage/service/';// 服务文件路径
        $search_file_dir     = $project_dir . 'application/manage/model/search/';// 检索文件路径
        $html_file_dir       = $project_dir . 'application/manage/view/' . $file_under_name . '/';// html文件路径
        $js_file_dir         = $project_dir . 'public/public/js/manage/' . $file_under_name . '/';// js文件路径
        $controller_file     = $file_name . 'Controller.php';
        $model_file          = $file_name . '.php';
        $service_file        = $file_name . 'Service.php';
        $search_file         = $file_name . 'Search.php';
        $html_file           = 'list.html'; // html文件名称
        $js_file             = 'list.js'; // js文件名称

        // 模板文件
        $temp_dir             = $project_dir . 'application/console/template/';
        $temp_controller_file = $temp_dir . 'controller.php';
        $temp_model_file      = $temp_dir . 'model.php';
        $temp_service_file    = $temp_dir . 'service.php';
        $temp_search_file     = $temp_dir . 'search.php';
        $temp_html_file       = $temp_dir . 'html.html'; // html文件名称
        $temp_js_file         = $temp_dir . 'js.js'; // js文件名称

        // 不指定强制覆盖，检查是否存在文件
        if (!$force) {
            if (is_file($controller_file_dir.$controller_file)) {
                throw new Exception('控制器文件已存在，可以加--force参数强制覆盖'.'['.$controller_file.']');
            }
            if (is_file($model_file_dir.$model_file)) {
                throw new Exception('模型文件已存在，可以加--force参数强制覆盖'.'['.$model_file.']');
            }
            if (is_file($service_file_dir.$service_file)) {
                throw new Exception('服务文件已存在，可以加--force参数强制覆盖'.'['.$service_file.']');
            }
            if (is_file($search_file_dir.$search_file)) {
                throw new Exception('列表文件已存在，可以加--force参数强制覆盖'.'['.$search_file.']');
            }
            if (is_file($html_file_dir.$html_file)) {
                throw new Exception('html视图文件已存在，可以加--force参数强制覆盖'.'['.$html_file.']');
            }
            if (is_file($js_file_dir.$js_file)) {
                throw new Exception('js文件已存在，可以加--force参数强制覆盖'.'['.$js_file.']');
            }
        }

        // 检查目录是否存在，不存在则创建
        if (!is_dir($controller_file_dir)) {
            mkdir($controller_file_dir, 0755, true);
        }
        if (!is_dir($model_file_dir)) {
            mkdir($model_file_dir, 0755, true);
        }
        if (!is_dir($service_file_dir)) {
            mkdir($service_file_dir, 0755, true);
        }
        if (!is_dir($search_file_dir)) {
            mkdir($search_file_dir, 0755, true);
        }
        if (!is_dir($html_file_dir)) {
            mkdir($html_file_dir, 0755, true);
        }
        if (!is_dir($js_file_dir)) {
            mkdir($js_file_dir, 0755, true);
        }

        // 生成文件
        $replacement = [
            '__CREATE_TIME__'            => date('Y-m-d H:i:00'),
            '__LIST_NAME__'              => trim($name),
            '__CONTROLLER__'             => $file_name,
            '__CONTROLLER_LOWER__'       => $file_lower_name,
            '__CONTROLLER_UNDER_SCORE__' => $file_under_name,
        ];
        $this->generateFile($temp_controller_file, $controller_file_dir.$controller_file, $replacement, $output);
        $this->generateFile($temp_model_file, $model_file_dir.$model_file, $replacement, $output);
        $this->generateFile($temp_service_file, $service_file_dir.$service_file, $replacement, $output);
        $this->generateFile($temp_search_file, $search_file_dir.$search_file, $replacement, $output);
        $this->generateFile($temp_html_file, $html_file_dir.$html_file, $replacement, $output);
        $this->generateFile($temp_js_file, $js_file_dir.$js_file, $replacement, $output);
    }

    /**
     * 执行模板读取和替换以及生成文件
     * @param string $source 模板文件完整路径
     * @param string $target 生成的文件完整路径
     * @param array $replacement 替换的key-value文本
     * @param Output $output
     */
    protected function generateFile($source, $target, $replacement, Output $output)
    {
        $template = file_get_contents($source);
        $content  = str_replace(array_keys($replacement), array_values($replacement), $template);
        file_put_contents($target, $content);
        $output->writeln("<info>Generate {".pathinfo($target, PATHINFO_BASENAME)."} Success!<info>");
    }
}
