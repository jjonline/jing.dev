<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'app\console\command\Init',// 初始化项目配置
    'app\console\command\Tasks',// 基于swoole扩展的TCP异步服务器
    'app\console\command\Password',// 修改整站加密auth_key之后加密明文密码
    'app\console\command\Generate',// 自动生成后台列表类页面
    // Db migration
    'think\migration\command\migrate\Create',
    'think\migration\command\migrate\Run',
    'think\migration\command\migrate\Rollback',
    'think\migration\command\migrate\Status',
    'think\migration\command\seed\Create',
    'think\migration\command\seed\Run',
];
