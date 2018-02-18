<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型 支持 php think 支持扩展
    'type'         => 'Think',
    // 模板路径
    'view_path'    => '',
    // 模板后缀
    'view_suffix'  => 'html',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板文件中不允许使用原生PHP代码块 ，但可使用模板php标签
    'tpl_deny_php' => false,
    // 开启模板布局功能
    'layout_on'    => true,
    // 模板布局主文件名称
    'layout_name'  => 'layout/layout',
    // 模板替换变量
    'tpl_replace_string'  =>  [
        '__PUBLIC__'  => '/public',
        '__JS__'      => '/public/js',
        '__CSS__'     => '/public/css',
        '__IMG__'     => '/public/images',
    ],
    // 默认错误跳转对应的模板文件，控制器下error方法渲染的模板
    'dispatch_error_tmpl' => '../application/common/view/dispatch_error_tmpl.html',
    // 默认成功跳转对应的模板文件，控制器下success方法渲染的模板
    'dispatch_success_tmpl' => '../application/common/view/dispatch_success_tmpl.html',
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '<%{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}%>',
    // 标签库标签开始标记
    'taglib_begin' => '<%{',
    // 标签库标签结束标记
    'taglib_end'   => '}%>',
];
