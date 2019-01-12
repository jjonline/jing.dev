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
    'app\console\command\init',// 初始化项目配置
    'app\console\command\tasks',// 基于swoole扩展的TCP异步服务器
    'app\console\command\password',// 修改整站加密auth_key之后加密明文密码
    'app\console\command\generate',// 自动生成后台列表类页面
];
