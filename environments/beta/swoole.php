<?php
/**
 * Swoole TCP Server Config
 * ---
 * 仅Linux平台可用，基于CentOS7开发，其他平台未测试
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-25 11:03:51
 * @file local.php
 */

return [


    // [Server端配置：Cli模式的Server端配置]


    // Swoole异步任务unix socket文件地址
    'socket'                 => '/tmp/swoole.sock',

    // Swoole异步任务绑定的ip地址，默认留空使用本机socket
    'ip'                     => '',

    // Swoole Tcp Server使用的端口号
    'port'                   => 9501,

    /**
     * Swoole Task进程启动后是否使用守护进程模式
     * ----
     * 1、直接使用php think tasks执行且需要维持在后台时设置成守护进程
     * 2、启用systemd特性时，由systemd维护进程，无需设置成守护进程
     * ----
     */
    'daemonize'              => false,

    // Swoole 守护进程的Pid文件位置
    'pid_file'               => '/www/wwwroot/test.laike188.com/runtime/swoole.pid',

    // worker进程的开启数量，使用swoole的task进程执行异步任务，1个worker进程即可(仅负责接收任务塞进队列的数据)
    'worker_num'             => 1,

    // Swoole异步执行任务的task进程数，依据任务繁忙程度调节
    'task_worker_num'        =>  32,

    // CPU亲和性设置 task线程没有作用，无需设置
    //'open_cpu_affinity'      => true,

    // 设置worker/task子进程的所属用户，避免子进程使用root权限 默认本机无需设置，留空不启用
    'user'                   => 'www',




    // [Client端配置：Web端的推送任务的配置]

    // connect链接超时
    'timeout'                => 0.5,

];
