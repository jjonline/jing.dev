<?php
/**
 * 个性化应用配置
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-06 14:39
 * @file local.php
 */

return [
    // 定义应用系统版本号，部分静态js、css引用
    'version'                => '20181226',
    // 自定义站点名称
    'site_name'              => 'InsSave',
    // 后台cookie和用户密码加密的字符串
    'auth_key'               => 'CwfFXeFa23mdH7KbxyQEEYedsQAJ85AQ',
    // 前台用户cooke和用户密码加密的字符串
    'pwd_key'                => 'wyjKiyiFh6KwX46rYEHJpd5X6BQeiAhk',
    // 系统默认每个请求都写日志进log表，可通过该配置忽略某个控制器下的请求都不记录
    'log_except_controller'  => [],
    // 系统默认每个请求都写日志进log表，可通过该配置忽略某个操作名的请求不记录[仅匹配操作名，可能多个控制器下具有相同操作名]
    'log_except_action'      => ['polling'],
];