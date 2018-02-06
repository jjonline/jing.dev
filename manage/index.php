<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// 加载基础文件
$app_root = realpath(__DIR__ . '/../');

require $app_root . '/thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 入口文件绑定manage模块并执行应用和响应
Container::get('app')->bind('manage')->run()->send();
