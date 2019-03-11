<?php
/**
 * 菜单生成的seed数组
 * ---
 * 该文件seed的时候没有用到，文件里的seed数据为该框架的基本seed数据
 * 若需还原最基本的组件菜单数据，将本文件复制一份并重命名为menu_seed.php
 * 然后执行seed，即命令行下：php think seed:run
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-02-25 23:33:08
 * @file menu_seed_basic.php
 */
$date_time = date('Y-m-d H:i:s');
return [
    [
        'id'             => '1',
        'tag'            => 'Dashboard',
        'name'           => '工作台',
        'icon'           => 'fa fa-dashboard',
        'url'            => 'manage/index/index',
        'parent_id'      => '0',
        'is_required'    => '1',
        'is_badge'       => '1',
        'level'          => '1',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '后台默认首页',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '2',
        'tag'            => 'Mine',
        'name'           => '个人中心',
        'icon'           => 'fa fa-h-square',
        'url'            => '',
        'parent_id'      => '0',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '1',
        'sort'           => '198',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '后台管理员个人中心',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '3',
        'tag'            => 'Mine_Profile',
        'name'           => '个人资料概要',
        'icon'           => 'fa fa-user-o',
        'url'            => 'manage/mine/profile',
        'parent_id'      => '2',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '后台管理员个人信息页面',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '4',
        'tag'            => 'Mine_Edit',
        'name'           => '修改个人资料',
        'icon'           => '',
        'url'            => 'manage/mine/edit',
        'parent_id'      => '3',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '后台管理员修改个人资料、账号、密码等信息',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '5',
        'tag'            => 'Common_UploadFile',
        'name'           => '上传文件',
        'icon'           => '',
        'url'            => 'manage/upload/upload',
        'parent_id'      => '3',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '公共上传文件权限',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '6',
        'tag'            => 'Common_Operation_Record',
        'name'           => '查看操作记录',
        'icon'           => '',
        'url'            => 'manage/operation/record',
        'parent_id'      => '3',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '通用的查看各种数据的操作记录接口，非敏感接口通用授权',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '7',
        'tag'            => 'Common_Badge',
        'name'           => 'Badge统计',
        'icon'           => '',
        'url'            => 'manage/statistics/badge',
        'parent_id'      => '3',
        'is_required'    => '1',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => 'Badge统计：即左侧菜单导航栏上的badge统计小标统一更新方法',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '8',
        'tag'            => 'Async_Task_List',
        'name'           => '异步任务管理',
        'icon'           => 'fa fa-bookmark-o',
        'url'            => 'manage/async_task/list',
        'parent_id'      => '2',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '查看异步任务列表和状态',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '9',
        'tag'            => 'Async_Task_Detail',
        'name'           => '查看异步任务详情',
        'icon'           => '',
        'url'            => 'manage/async_task/detail',
        'parent_id'      => '8',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '查看单条异步任务详情',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '10',
        'tag'            => 'System_Setting',
        'name'           => '系统设置',
        'icon'           => 'fa fa-sun-o',
        'url'            => '',
        'parent_id'      => '0',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '1',
        'sort'           => '999',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统的各项设置功能：部门管理、角色管理和系统参数管理',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '11',
        'tag'            => 'Dept_Manage',
        'name'           => '部门管理',
        'icon'           => 'fa fa-address-card',
        'url'            => 'manage/department/list',
        'parent_id'      => '10',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '部门数据管理',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '12',
        'tag'            => 'Dept_Create',
        'name'           => '新增部门',
        'icon'           => '',
        'url'            => 'manage/department/create',
        'parent_id'      => '11',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '新增部门',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '13',
        'tag'            => 'Dept_Edit',
        'name'           => '编辑部门',
        'icon'           => '',
        'url'            => 'manage/department/edit',
        'parent_id'      => '11',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '编辑部门',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '14',
        'tag'            => 'Dept_Delete',
        'name'           => '删除部门',
        'icon'           => '',
        'url'            => 'manage/department/delete',
        'parent_id'      => '11',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '删除部门',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '15',
        'tag'            => 'Dept_Sort',
        'name'           => '部门排序',
        'icon'           => '',
        'url'            => 'manage/department/sort',
        'parent_id'      => '11',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '部门快速排序',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '16',
        'tag'            => 'Role_Manage',
        'name'           => '角色管理',
        'icon'           => 'fa fa-child',
        'url'            => 'manage/role/list',
        'parent_id'      => '10',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统菜单权限和数据范围权限的角色数据管理',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '17',
        'tag'            => 'Role_Create',
        'name'           => '新增角色',
        'icon'           => '',
        'url'            => 'manage/role/create',
        'parent_id'      => '16',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '新增角色',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '18',
        'tag'            => 'Role_Edit',
        'name'           => '编辑角色',
        'icon'           => '',
        'url'            => 'manage/role/edit',
        'parent_id'      => '16',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '编辑角色',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '19',
        'tag'            => 'Role_Delete',
        'name'           => '删除角色',
        'icon'           => '',
        'url'            => 'manage/role/delete',
        'parent_id'      => '16',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '删除角色',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '20',
        'tag'            => 'Role_Sort',
        'name'           => '角色排序',
        'icon'           => '',
        'url'            => 'manage/role/sort',
        'parent_id'      => '16',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '快速设置角色排序',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '21',
        'tag'            => 'Config_Setting_List',
        'name'           => '参数设置',
        'icon'           => 'fa fa-check-square-o',
        'url'            => 'manage/config/list',
        'parent_id'      => '10',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '5',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统提供的各项自定义网站配置参数设置',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '22',
        'tag'            => 'Config_Setting_Save',
        'name'           => '系统设置保存',
        'icon'           => '',
        'url'            => 'manage/config/save',
        'parent_id'      => '21',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '提交保存自定义设置的各项配置参数，不给权限则能查看不能修改',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '23',
        'tag'            => 'Developer',
        'name'           => 'Developer',
        'icon'           => 'fa fa-code',
        'url'            => '',
        'parent_id'      => '0',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '1',
        'sort'           => '1000',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统底层设置功能，超级管理员使用',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '24',
        'tag'            => 'Menu_List',
        'name'           => '后台菜单管理',
        'icon'           => 'fa fa-venus-double',
        'url'            => 'manage/menu/list',
        'parent_id'      => '23',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统底层使用的后台菜单设置和管理',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '25',
        'tag'            => 'Menu_Create',
        'name'           => '新增菜单',
        'icon'           => 'fa fa-plus-square-o',
        'url'            => 'manage/menu/create',
        'parent_id'      => '24',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '新增系统底层菜单项',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '26',
        'tag'            => 'Menu_Edit',
        'name'           => '编辑菜单',
        'icon'           => '',
        'url'            => 'manage/menu/edit',
        'parent_id'      => '24',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '编辑修改系统底层菜单',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '27',
        'tag'            => 'Menu_Delete',
        'name'           => '删除菜单',
        'icon'           => '',
        'url'            => 'manage/menu/delete',
        'parent_id'      => '24',
        'is_required'    => '0',
        'is_badge'       => '1',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '删除系统底层菜单',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '28',
        'tag'            => 'Menu_Sort',
        'name'           => '菜单排序',
        'icon'           => '',
        'url'            => 'manage/menu/sort',
        'parent_id'      => '24',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '快速设置系统底层菜单排序',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '29',
        'tag'            => 'Menu_Reorganize',
        'name'           => '重整菜单',
        'icon'           => '',
        'url'            => 'manage/menu/reorganize',
        'parent_id'      => '24',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '5',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '重新按菜单层级、排序整理菜单数据并生成seed数据',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '30',
        'tag'            => 'Admin_User_List',
        'name'           => '后台用户管理',
        'icon'           => 'fa fa-users',
        'url'            => 'manage/user/list',
        'parent_id'      => '23',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '管理后台管理员数据',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '31',
        'tag'            => 'Admin_User_Create',
        'name'           => '新增用户',
        'icon'           => '',
        'url'            => 'manage/user/create',
        'parent_id'      => '30',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '新增后台管理员',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '32',
        'tag'            => 'Admin_User_Edit',
        'name'           => '编辑用户',
        'icon'           => '',
        'url'            => 'manage/user/edit',
        'parent_id'      => '30',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '编辑后台管理员',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '33',
        'tag'            => 'Admin_User_Enable',
        'name'           => '启用和禁用用户',
        'icon'           => '',
        'url'            => 'manage/user/enabletoggle',
        'parent_id'      => '30',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '启用和禁用后台管理员',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '34',
        'tag'            => 'Site_Config',
        'name'           => '站点配置管理',
        'icon'           => 'fa fa-sun-o',
        'url'            => 'manage/site_config/list',
        'parent_id'      => '23',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '5',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '参数设置的系统底层实现，管理网站配置项系统底层参数',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '35',
        'tag'            => 'Site_Config_Create',
        'name'           => '新增配置项目',
        'icon'           => '',
        'url'            => 'manage/site_config/create',
        'parent_id'      => '34',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '新增参数配置项目',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '36',
        'tag'            => 'Site_Config_Edit',
        'name'           => '编辑配置项目',
        'icon'           => '',
        'url'            => 'manage/site_config/edit',
        'parent_id'      => '34',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '编辑参数配置项目',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '37',
        'tag'            => 'Site_Config_Delete',
        'name'           => '删除配置项目',
        'icon'           => '',
        'url'            => 'manage/site_config/delete',
        'parent_id'      => '34',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '3',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '删除参数配置项目',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '38',
        'tag'            => 'Site_Config_Sort',
        'name'           => '配置项目快速排序',
        'icon'           => '',
        'url'            => 'manage/site_config/sort',
        'parent_id'      => '34',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '4',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '参数配置项目快速排序',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '39',
        'tag'            => 'Developer_tools',
        'name'           => '系统辅助工具',
        'icon'           => 'fa fa-wrench',
        'url'            => 'manage/developer/tools',
        'parent_id'      => '23',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '5',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '系统底层管理工具',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '40',
        'tag'            => 'Developer_clear_runtime',
        'name'           => '清理runtime文件',
        'icon'           => '',
        'url'            => 'manage/developer/runtime',
        'parent_id'      => '39',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '1',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '清理系统运行时runtime文件',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '41',
        'tag'            => 'Developer_clear_cache',
        'name'           => '清理整站数据缓存',
        'icon'           => '',
        'url'            => 'manage/developer/cache',
        'parent_id'      => '39',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '3',
        'sort'           => '2',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '清理为了提升系统运行效率的整站数据缓存，清理后系统将自动重建缓存',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
    [
        'id'             => '42',
        'tag'            => 'Developer_sample',
        'name'           => '组件开发样例',
        'icon'           => 'fa fa-laptop',
        'url'            => 'manage/developer/sample',
        'parent_id'      => '23',
        'is_required'    => '0',
        'is_badge'       => '0',
        'level'          => '2',
        'sort'           => '6',
        'all_columns'    => null,
        'is_system'      => '1',
        'is_permissions' => '0',
        'is_column'      => '0',
        'remark'         => '组件系统的一些常用公共能使用样例',
        'create_time'    => $date_time,
        'update_time'    => $date_time,
    ],
];
