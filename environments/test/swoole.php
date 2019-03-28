<?php
/**
 * Swoole Redis-like Server Config
 * ---
 * 仅Linux平台可用，基于CentOS7开发，其他平台未测试
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-27
 * @file swoole.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Server端配置：利用swoole实现的redis-like投递任务的服务端相关配置
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Swoole异步任务unix socket文件地址，启用此项则不开放外部机器访问，仅本机可用
    |--------------------------------------------------------------------------
    */
    'socket' => '/tmp/swoole.sock',
    /*
    |--------------------------------------------------------------------------
    | Swoole异步任务绑定的ip地址，启用ip协议则socket配置请留空
    |--------------------------------------------------------------------------
    */
    'ip'     => '127.0.0.1',
    /*
    |--------------------------------------------------------------------------
    | Swoole Tcp Server使用的端口号，设置ip和port将启用ip绑定
    |--------------------------------------------------------------------------
    */
    'port'   => 9501,

    /*
    |--------------------------------------------------------------------------
    | Swoole的配置参数项目数组
    |--------------------------------------------------------------------------
    */
    'options' => [
        /*
        |--------------------------------------------------------------------------
        | 设置worker/task子进程的所属用户，避免子进程使用root权限 默认本机无需设置，留空不启用
        |--------------------------------------------------------------------------
        */
        'user'                    => '',
        /*
        |--------------------------------------------------------------------------
        | Swoole Task进程启动后是否使用守护进程模式，supervisor或systemD时可酌情开启
        |--------------------------------------------------------------------------
        */
        'daemonize'               => false,
        /*
        |--------------------------------------------------------------------------
        | Swoole设置Server最大允许维持多少个TCP连接
        |--------------------------------------------------------------------------
        */
        'max_connection'          => 10000,
        /*
        |--------------------------------------------------------------------------
        | Swoole的reactor进程开启数量
        |--------------------------------------------------------------------------
        */
        'reactor_num'             => 1,
        /*
        |--------------------------------------------------------------------------
        | Swoole的worker进程开启数量
        |--------------------------------------------------------------------------
        */
        'worker_num'              => 2,
        /*
        |--------------------------------------------------------------------------
        | Swoole的task-worker进程开启数量，即执行异步任务的task进程
        |--------------------------------------------------------------------------
        */
        'task_worker_num'         => 16,
        /*
        |--------------------------------------------------------------------------
        | 设置Task进程与Worker进程之间通信的方式，不要更改此项除非你知道自己在做什么
        |--------------------------------------------------------------------------
        */
        'task_ipc_mode'           => 1,
        /*
        |--------------------------------------------------------------------------
        | Swoole的ssl证书cert路径位置
        |--------------------------------------------------------------------------
        */
        'ssl_cert_file'           => null,
        /*
        |--------------------------------------------------------------------------
        | Swoole的ssl证书key路径位置
        |--------------------------------------------------------------------------
        */
        'ssl_key_file'            => null,
        /*
        |--------------------------------------------------------------------------
        | Swoole的是否开启websocket协议访问
        |--------------------------------------------------------------------------
        */
        'open_websocket_protocol' => false,
        /*
        |--------------------------------------------------------------------------
        | Swoole的是否开启http协议访问
        |--------------------------------------------------------------------------
        */
        'open_http_protocol'      => false,
        /*
        |--------------------------------------------------------------------------
        | Swoole的日志文件路径
        |--------------------------------------------------------------------------
        */
        'log_file'                => app()->getRootPath() . 'runtime/swoole/swoole.log',
        /*
        |--------------------------------------------------------------------------
        | Swoole的master进程pid文件路径
        |--------------------------------------------------------------------------
        */
        'pid_file'                => app()->getRootPath() . 'runtime/swoole/swoole.pid',
        /*
        |--------------------------------------------------------------------------
        | Swoole的当前工作进程最大协程数量，默认3k，足够用建议不要随意修改
        |--------------------------------------------------------------------------
        */
        'max_coroutine'           => 3000,
        /*
        |--------------------------------------------------------------------------
        | Swoole的task-worker是否支持协程，不要开启，task-worker仅执行同步阻塞耗时任务
        |--------------------------------------------------------------------------
        */
        'task_enable_coroutine'   => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Client端配置：Web端的推送任务的配置--即利用redis协议的客户端的相关配置
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | 投递任务的redis-client连接本redis-like server时建立connect链接的超时时间，单位：秒
    |--------------------------------------------------------------------------
    */
    'client_timeout' => 0.5,
];
