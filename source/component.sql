/*
 Navicat Premium Data Transfer

 Source Server         : 本机MySQL
 Source Server Type    : MySQL
 Source Server Version : 50723
 Source Host           : localhost
 Source Database       : component

 Target Server Type    : MySQL
 Target Server Version : 50723
 File Encoding         : utf-8

 Date: 01/04/2019 22:25:57 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `com_async_task`
-- ----------------------------
DROP TABLE IF EXISTS `com_async_task`;
CREATE TABLE `com_async_task` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属部门ID',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '异步任务可识读标题:由底层类属性标记',
  `task` varchar(128) NOT NULL DEFAULT '' COMMENT '异步任务:对应底层类名',
  `task_data` text NOT NULL COMMENT '异步任务参数数据，JSON字符串',
  `result` text NOT NULL COMMENT '异步任务执行结果描述，描述文本',
  `task_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '异步任务执行状态：0、未投递未执行，1、已投递正在执行，2、执行成功，3、执行失败',
  `delivery_time` datetime DEFAULT NULL COMMENT '任务投递时间',
  `finish_time` datetime DEFAULT NULL COMMENT '任务结束时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='异步任务记录';

-- ----------------------------
--  Table structure for `com_attachment`
-- ----------------------------
DROP TABLE IF EXISTS `com_attachment`;
CREATE TABLE `com_attachment` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `file_origin_name` varchar(128) NOT NULL COMMENT '带后缀的上传时的原始文件名',
  `file_name` varchar(128) NOT NULL COMMENT '带后缀的上传完毕保存的文件名',
  `file_path` varchar(512) NOT NULL COMMENT '相对于网站根目录的带文件名的文件路径，斜杠开头，方便切换CDN',
  `file_mime` varchar(64) NOT NULL COMMENT '文件mime类型',
  `file_size` bigint(20) NOT NULL COMMENT '资源大小，单位：Bytes即B，1024B = 1KB',
  `file_sha1` varchar(40) NOT NULL COMMENT '资源的sha1值',
  `image_width` int(10) NOT NULL DEFAULT '0' COMMENT '图片类型宽资源的宽度',
  `image_height` int(10) NOT NULL DEFAULT '0' COMMENT '图片类型资源的高度',
  `is_safe` tinyint(1) NOT NULL DEFAULT '0' COMMENT '资源文件是否需要安全存储不暴露公网url，1是0否',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`,`file_sha1`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='附件表：用户上传资源数据';

-- ----------------------------
--  Table structure for `com_department`
-- ----------------------------
DROP TABLE IF EXISTS `com_department`;
CREATE TABLE `com_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `name` varchar(200) NOT NULL COMMENT '部门名称',
  `parent_id` int(11) DEFAULT NULL COMMENT '父级部门ID，为NUll则是顶级部门',
  `level` int(11) NOT NULL COMMENT '部门层级：1->2->3逐次降低，最大层级5',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='多层级部门表';

-- ----------------------------
--  Records of `com_department`
-- ----------------------------
BEGIN;
INSERT INTO `com_department` VALUES ('1', '线上团队-顶级部门', null, '1', '1', '开发测试线上团队', '2018-02-19 19:29:57', '2019-01-04 11:58:09');
COMMIT;

-- ----------------------------
--  Table structure for `com_log`
-- ----------------------------
DROP TABLE IF EXISTS `com_log`;
CREATE TABLE `com_log` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '动作记录的ip地址',
  `user_agent` varchar(512) NOT NULL DEFAULT '' COMMENT '请求头信息，浏览器头信息',
  `action` varchar(100) NOT NULL COMMENT '请求的操作，对应menu表的url字段值',
  `url` text NOT NULL COMMENT '请求的完整Url',
  `method` varchar(8) NOT NULL COMMENT '请求方式 GET、POST、PUT、DELETE等',
  `request_data` text COMMENT '请求体数据',
  `extra_data` text COMMENT '主动保存进日志的数据',
  `memory_usage` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '内存小号（kb）',
  `execute_millisecond` int(11) NOT NULL DEFAULT '0' COMMENT '执行耗时（毫秒）',
  `description` varchar(128) NOT NULL DEFAULT '' COMMENT '日志手动记录的说明文字',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`,`action`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户操作动作的详细日志，每个请求都记录';

-- ----------------------------
--  Table structure for `com_member`
-- ----------------------------
DROP TABLE IF EXISTS `com_member`;
CREATE TABLE `com_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` varchar(32) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码（密文）',
  `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `gender` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '性别：-1未知0女1男',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '座机号码',
  `auth_code` char(32) NOT NULL COMMENT '授权code，用于cookie加密(可变)',
  `province` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的省份',
  `city` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的地区|市单位',
  `district` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的县级',
  `address` varchar(256) NOT NULL DEFAULT '' COMMENT '会员的完整地址',
  `member_level_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员当等级ID，依据累积积分计算',
  `current_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员当前积分',
  `accumulate_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员累加积分，只加(正常消费)不减，惩罚性对应扣除累积积分',
  `enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用禁用标记：1启用0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_name` (`user_name`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `member_level_id` (`member_level_id`) USING BTREE,
  KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='前台会员主表';

-- ----------------------------
--  Table structure for `com_member_level`
-- ----------------------------
DROP TABLE IF EXISTS `com_member_level`;
CREATE TABLE `com_member_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '等级名称',
  `once_obtain_begin` int(11) NOT NULL DEFAULT '0' COMMENT '一次性获取积分起始值',
  `once_obtain_end` int(11) NOT NULL DEFAULT '0' COMMENT '一次性获取积分结束值',
  `accumulate_begin` int(11) NOT NULL DEFAULT '0' COMMENT '累积积分起始值',
  `accumulate_end` int(11) NOT NULL DEFAULT '0' COMMENT '累积积分结束值',
  `remark` varchar(512) NOT NULL DEFAULT '' COMMENT '管理员备注',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT '当前级别，1<2<3',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='会员等级设定表';

-- ----------------------------
--  Table structure for `com_member_log`
-- ----------------------------
DROP TABLE IF EXISTS `com_member_log`;
CREATE TABLE `com_member_log` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `member_id` int(11) NOT NULL COMMENT '用户ID',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '日志标题或描述',
  `os` varchar(128) NOT NULL DEFAULT '' COMMENT '操作系统信息',
  `browser` varchar(128) NOT NULL DEFAULT '' COMMENT '浏览器信息',
  `ip` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `location` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址解析出的归属地信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用层会员可识别日志';

-- ----------------------------
--  Table structure for `com_member_open`
-- ----------------------------
DROP TABLE IF EXISTS `com_member_open`;
CREATE TABLE `com_member_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员表ID',
  `open_type` enum('qq','pc_wx','mp_wx','xcx','wb') NOT NULL COMMENT '开放平台登录类型qq-QQ开放登录 pc_wx-Pc网站版微信扫码登录 mp_wx-微信公众号版微信登录 xcx-微信小程序登录 wb-微博登录当(需要添加新类型时添加该枚举类型的待选值)',
  `open_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '性别：-1未知0女1男',
  `figure` varchar(128) NOT NULL DEFAULT '' COMMENT '头像图src',
  `union_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'UnionID',
  `expire_time` datetime DEFAULT NULL COMMENT 'Token过期时间点',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `open_id` (`open_id`,`open_type`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `union_id` (`union_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='会员多平台开放平台登录账户信息';

-- ----------------------------
--  Table structure for `com_member_point_record`
-- ----------------------------
DROP TABLE IF EXISTS `com_member_point_record`;
CREATE TABLE `com_member_point_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '客户表的ID，若没有则为0',
  `points_changes` int(11) NOT NULL DEFAULT '0' COMMENT '积分变动数量：增加正数消费负数',
  `current_points` int(11) NOT NULL DEFAULT '0' COMMENT '变动后积分数量，不得为负数',
  `accumulate_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员累加积分，只加(正常消费)不减，惩罚性对应扣除累积积分',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '积分变动后台操作用户ID，0表示无关后台用户',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='会员积分变动记录表';

-- ----------------------------
--  Table structure for `com_menu`
-- ----------------------------
DROP TABLE IF EXISTS `com_menu`;
CREATE TABLE `com_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tag` varchar(64) NOT NULL COMMENT '菜单名称Tag，唯一的字符串',
  `name` varchar(64) NOT NULL COMMENT '菜单名称',
  `icon` varchar(64) NOT NULL DEFAULT '' COMMENT 'fontawesome、glyphicon或ionicons图标的class',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单Url：无前缀斜线',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父菜单ID',
  `is_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否必选，即所有登录用户均可使用，1必选0权限控制，为1时选择角色菜单权限的时候默认勾选且不可取消',
  `is_badge` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单所标识的功能中是否需要使用badge统计，显示待办事项等badge',
  `level` int(11) NOT NULL COMMENT '当前层级 1为一级导航2为二级导航3为二级导航页面中的功能按钮',
  `sort` int(11) NOT NULL COMMENT '排序数字越小越靠前',
  `extra_param` json DEFAULT NULL COMMENT '额外存储的菜单对应操作所需要的限定参数，json格式',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否系统菜单，1不允许删除0允许',
  `is_permissions` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否有数据范围控制',
  `is_column` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否需要控制字段显示，1：是 0:否',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tag` (`tag`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='功能菜单[节点]';

-- ----------------------------
--  Records of `com_menu`
-- ----------------------------
BEGIN;
INSERT INTO `com_menu` VALUES ('1', 'Dasheboard', '工作台', 'fa fa-dashboard', 'manage/index/index', '0', '1', '1', '1', '1', 'null', '1', '0', '0', '工作台默认首页', '2018-02-19 19:59:42', '2018-07-24 12:07:16'), ('2', 'Developer_Tools', 'Developer', 'fa fa-code', '', '0', '0', '0', '1', '200', 'null', '1', '0', '0', '开发者使用的菜单', '2018-02-19 20:56:01', '2019-01-04 12:06:32'), ('3', 'Menu_Tools_List', '后台菜单管理', 'fa fa-venus-double', 'manage/menu/list', '2', '0', '0', '2', '1', 'null', '1', '0', '0', '开发者管理权限菜单', '2018-02-19 21:05:00', '2019-01-04 12:06:58'), ('4', 'Menu_Tools_Create', '新增菜单', 'fa fa-plus-square-o', 'manage/menu/create', '3', '0', '0', '3', '1', 'null', '1', '0', '0', '开发者新增管理菜单', '2018-02-19 21:08:08', '2019-01-04 12:07:09'), ('5', 'System_Setting', '系统设置', 'fa fa-sun-o', '', '0', '0', '0', '1', '199', 'null', '1', '0', '0', '系统角色管理、系统参数设置等', '2018-02-20 19:05:46', '2019-01-04 12:04:18'), ('6', 'Role_Create', '新增角色', '', 'manage/role/create', '10', '0', '0', '3', '1', 'null', '1', '0', '0', '新增角色', '2018-02-20 19:07:07', '2019-01-04 12:05:41'), ('7', 'Menu_Tools_Edit', '编辑菜单', '', 'manage/menu/edit', '3', '0', '0', '3', '1', 'null', '1', '0', '0', '编辑修改菜单', '2018-02-20 19:18:09', '2019-01-04 12:07:22'), ('8', 'Menu_Tools_Sort', '菜单排序', '', 'manage/menu/sort', '3', '0', '0', '3', '2', 'null', '1', '0', '0', '快速设置菜单排序', '2018-02-20 19:19:48', '2019-01-04 12:07:31'), ('9', 'Menu_Tools_Delete', '删除菜单', '', 'manage/menu/delete', '3', '0', '1', '3', '3', 'null', '1', '0', '0', '删除菜单', '2018-02-21 10:59:04', '2019-01-04 12:07:42'), ('10', 'Role_Manage', '角色管理', 'fa fa-child', 'manage/role/list', '5', '0', '0', '2', '2', 'null', '1', '0', '0', '系统内所有角色列表', '2018-02-21 17:13:02', '2019-01-04 12:05:31'), ('11', 'Role_Edit', '编辑角色', '', 'manage/role/edit', '10', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑角色', '2018-02-21 17:16:09', '2019-01-04 12:05:52'), ('12', 'Role_Sort', '角色排序', '', 'manage/role/sort', '10', '0', '0', '3', '4', 'null', '1', '0', '0', '快速设置角色排序', '2018-02-21 17:17:32', '2019-01-04 12:06:10'), ('13', 'Dept_Create', '新增部门', '', 'manage/department/create', '14', '0', '0', '3', '1', 'null', '1', '0', '0', '新增部门', '2018-02-21 17:46:53', '2019-01-04 12:04:46'), ('14', 'Dept_Manage', '部门管理', 'fa fa-address-card', 'manage/department/list', '5', '0', '0', '2', '1', 'null', '1', '0', '0', '部门列表管理', '2018-02-21 17:55:44', '2019-01-04 12:04:34'), ('15', 'Mine', '个人中心', 'fa fa-h-square', '', '0', '1', '0', '1', '198', 'null', '1', '0', '0', '个人中心', '2018-02-21 18:04:14', '2019-01-04 12:01:11'), ('16', 'Mine_Profile', '个人资料概要', 'fa fa-user-o', 'manage/mine/profile', '15', '1', '0', '2', '1', 'null', '1', '0', '0', '个人中心概要页面', '2018-02-21 18:14:59', '2018-07-24 12:07:16'), ('17', 'Mine_Edit', '修改个人资料', '', 'manage/mine/edit', '16', '1', '0', '3', '1', 'null', '1', '0', '0', '修改个人资料、账号密码等信息', '2018-02-21 18:19:24', '2018-07-24 12:07:16'), ('18', 'Role_Delete', '删除角色', '', 'manage/role/delete', '10', '0', '0', '3', '3', 'null', '1', '0', '0', '删除角色', '2018-02-23 11:16:06', '2019-01-04 12:06:02'), ('19', 'Dept_Edit', '编辑部门', '', 'manage/department/edit', '14', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑部门', '2018-02-23 11:17:59', '2019-01-04 12:04:55'), ('20', 'Dept_Sort', '部门排序', '', 'manage/department/sort', '14', '0', '0', '3', '4', 'null', '1', '0', '0', '部门快速排序进行调整', '2018-02-23 11:19:01', '2019-01-04 12:05:12'), ('21', 'Dept_Delete', '删除部门', '', 'manage/department/delete', '14', '0', '0', '3', '3', 'null', '1', '0', '0', '删除部门数据', '2018-02-23 11:19:46', '2019-01-04 12:05:04'), ('22', 'Article', '图文管理', 'fa fa-file-text', '', '0', '0', '0', '1', '196', 'null', '1', '0', '0', '网站图文管理', '2018-03-05 16:12:30', '2019-01-04 20:28:13'), ('23', 'Common_UploadFile', '上传文件', '', 'manage/upload/upload', '16', '1', '0', '3', '1', 'null', '1', '0', '0', '上传文件公共权限', '2018-03-05 16:14:46', '2018-07-24 12:07:16'), ('24', 'Admin_User_Create', '新增用户', '', 'manage/user/create', '25', '0', '0', '3', '1', 'null', '1', '0', '0', '新增后台用户', '2018-03-09 10:18:14', '2019-01-04 12:08:21'), ('25', 'Admin_User_List', '后台用户管理', 'fa fa-users', 'manage/user/list', '2', '0', '0', '2', '4', 'null', '1', '0', '0', '后台用户列表管理，非管理权限禁止分配', '2018-03-09 10:19:34', '2019-01-04 12:07:51'), ('26', 'Admin_User_Edit', '编辑用户', '', 'manage/user/edit', '25', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑后台管理员用户信息', '2018-03-09 10:20:28', '2019-01-04 12:08:28'), ('27', 'Admin_User_Enable', '启用和禁用用户', '', 'manage/user/enabletoggle', '25', '0', '0', '3', '3', 'null', '1', '0', '0', '启用和禁用后台管理员', '2018-03-09 10:21:47', '2019-01-04 12:08:33'), ('28', 'Operation_Record', '查看操作记录', '', 'manage/operation/record', '16', '1', '0', '3', '3', 'null', '1', '0', '0', '通用的查看各种数据的操作记录接口，非敏感接口通用授权', '2018-04-28 14:42:22', '2018-07-24 12:07:16'), ('29', 'Common_Badge', 'Badge统计', '', 'manage/statistics/badge', '16', '1', '0', '3', '4', 'null', '1', '0', '0', 'Badge统计：即左侧导航栏后方的badge小标', '2018-04-28 14:43:53', '2018-07-24 12:07:16'), ('30', 'Async_Task_Manage', '异步任务状态', 'fa fa-bookmark-o', 'manage/async_task/list', '15', '0', '0', '2', '2', 'null', '1', '0', '0', '查看当前异步任务列表和状态', '2018-04-28 14:45:03', '2018-07-24 12:07:16'), ('31', 'Frontend_Member_Manage', '会员管理', 'fa fa-users', '', '0', '0', '0', '1', '197', 'null', '1', '0', '0', '前台会员管理', '2018-04-29 14:10:45', '2019-01-04 12:01:21'), ('32', 'Frontend_Member_List', '前台会员管理', 'fa fa-user-circle-o', 'manage/member/list', '31', '0', '0', '2', '1', 'null', '1', '0', '0', '前台会员列表', '2018-04-29 14:12:27', '2019-01-04 14:15:55'), ('33', 'Frontend_Member_Create', '新增前台会员', '', 'manage/member/create', '32', '0', '0', '3', '1', 'null', '1', '0', '0', '新增前台会员', '2018-04-29 15:12:03', '2018-07-24 12:07:16'), ('34', 'Frontend_Member_Edit', '编辑前台会员', '', 'manage/member/edit', '32', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑前台会员', '2018-04-29 15:12:31', '2018-07-24 12:07:16'), ('35', 'Frontend_Member_Enable', '启用禁用前台会员', '', 'manage/member/enabletoggle', '32', '0', '0', '3', '3', 'null', '1', '0', '0', '启用禁用前台会员', '2018-04-29 15:13:09', '2018-07-24 12:07:16'), ('36', 'Frontend_Member_Level', '会员等级设置', 'fa fa-level-up', 'manage/member_level/list', '31', '0', '0', '2', '2', 'null', '1', '0', '0', '会员等级设置和管理', '2018-04-30 14:02:52', '2018-07-24 12:07:16'), ('37', 'Frontend_Member_Level_Create', '新增会员等级', '', 'manage/member_level/create', '36', '0', '0', '3', '1', 'null', '1', '0', '0', '新增会员等级', '2018-04-30 14:03:47', '2018-07-24 12:07:16'), ('38', 'Frontend_Member_Level_Edit', '编辑会员等级', '', 'manage/member_level/edit', '36', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑会员等级', '2018-04-30 14:04:11', '2018-07-24 12:07:16'), ('39', 'Frontend_Member_Level_Delete', '删除会员等级', '', 'manage/member_level/delete', '36', '0', '0', '3', '3', 'null', '1', '0', '0', '删除会员等级', '2018-04-30 14:04:37', '2018-07-24 12:07:16'), ('41', 'Site_Config', '站点配置管理', 'fa fa-sun-o', 'manage/site_config/list', '2', '0', '0', '2', '5', 'null', '1', '0', '0', '站点配置项增删改查，与`参数设置`管理侧重点不一样，前者侧重于配置项的值设置，后者侧重于配置条目本身的新增和修改', '2018-06-16 15:19:41', '2019-01-04 14:21:19'), ('42', 'Site_Config_Create', '新增配置项目', '', 'manage/site_config/create', '41', '0', '0', '3', '1', 'null', '1', '0', '0', '新增站点配置项目', '2018-06-16 15:21:01', '2018-12-30 14:37:21'), ('43', 'Site_Config_Edit', '编辑配置项目', '', 'manage/site_config/edit', '41', '0', '0', '3', '2', 'null', '1', '0', '0', '编辑配置项目', '2018-06-16 15:21:30', '2018-12-30 14:37:25'), ('44', 'Site_Config_Delete', '删除配置项目', '', 'manage/site_config/delete', '41', '0', '0', '3', '3', 'null', '1', '0', '0', '删除配置项目', '2018-06-16 15:21:57', '2018-12-30 14:37:28'), ('45', 'Site_Config_Sort', '配置项目快速排序', '', 'manage/site_config/sort', '41', '0', '0', '3', '4', 'null', '1', '0', '0', '配置项目快速排序', '2018-06-16 15:22:28', '2018-12-30 14:37:30'), ('46', 'Config_Setting_List', '参数设置', 'fa fa-check-square-o', 'manage/config/list', '5', '0', '0', '2', '5', '[]', '1', '0', '0', '站点各个配置项值设置，与`站点配置`管理侧重点不一样，前者侧重于配置条目本身的新增和修改，后者侧重于配置项的值设置', '2018-12-31 21:05:48', '2019-01-04 14:20:40'), ('47', 'Config_Setting_Save', '保存参数设置', '', 'manage/config/save', '46', '0', '0', '3', '1', '[]', '1', '0', '0', '提交保存，不给权限可查看而无法修改', '2018-12-31 21:07:57', '2019-01-04 14:13:33'), ('48', 'Article_List', '图文管理', 'fa fa-file-text-o', 'mange/article/list', '22', '0', '0', '2', '1', '[]', '1', '0', '0', '网站图文列表：文章列表', '2019-01-04 20:17:51', '2019-01-04 20:20:13'), ('49', 'Article_Create', '新增图文', '', 'mange/article/create', '48', '0', '0', '3', '1', '[]', '1', '0', '0', '新增图文', '2019-01-04 20:18:46', '2019-01-04 20:18:46'), ('50', 'Article_Edit', '编辑图文', '', 'mange/article/edit', '48', '0', '0', '3', '2', '[]', '1', '0', '0', '编辑图文', '2019-01-04 20:19:15', '2019-01-04 20:19:30'), ('51', 'Article_Delete', '删除图文', '', 'mange/article/delete', '48', '0', '0', '3', '3', '[]', '1', '0', '0', '删除图文', '2019-01-04 20:19:54', '2019-01-04 20:19:54'), ('52', 'Article_Cat', '图文分类', 'fa fa fa-server', 'mange/article_cat/list', '22', '0', '0', '2', '2', '[]', '1', '0', '0', '图文分类管理', '2019-01-04 20:27:07', '2019-01-04 20:27:07'), ('53', 'Article_Sort', '快速排序', '', 'manage/article/sort', '48', '0', '0', '3', '4', '[]', '1', '0', '0', '图文快速排序', '2019-01-04 20:30:05', '2019-01-04 20:31:02'), ('54', 'Article_Prop', '快速设置属性', '', 'manage/article/property', '48', '0', '0', '3', '5', '[]', '1', '0', '0', '图文快速设置属性', '2019-01-04 20:30:54', '2019-01-04 20:30:54'), ('55', 'Article_Cat_Create', '新增图文分类', '', 'manage/article_cat/create', '52', '0', '0', '3', '1', '[]', '1', '0', '0', '新增图文分类', '2019-01-04 20:31:53', '2019-01-04 20:31:53'), ('56', 'Article_Cat_Edit', '编辑图文分类', '', 'manage/article_cat/edit', '52', '0', '0', '3', '2', '[]', '1', '0', '0', '编辑图文分类', '2019-01-04 20:32:18', '2019-01-04 20:32:18'), ('57', 'Article_Cat_Delete', '删除图文分类', '', 'manage/article_cat/delete', '52', '0', '0', '3', '3', '[]', '1', '0', '0', '删除图文分类', '2019-01-04 20:32:47', '2019-01-04 20:32:47'), ('58', 'Article_Cat_Sort', '图文分类快速排序', '', 'manage/article_cat/sort', '52', '0', '0', '3', '4', '[]', '1', '0', '0', '图文分类快速排序', '2019-01-04 20:33:19', '2019-01-04 20:33:19'), ('59', 'Tag_List', '关键词管理', 'fa fa-tags', 'manage/tag/list', '22', '0', '0', '2', '3', '[]', '1', '0', '0', '图文关键词管理', '2019-01-04 20:52:53', '2019-01-04 20:53:15'), ('60', 'Tag_Create', '新增关键词', '', 'manage/tag/create', '59', '0', '0', '3', '1', '[]', '1', '0', '0', '新增关键词', '2019-01-04 22:11:17', '2019-01-04 22:11:17'), ('61', 'Tag_Edit', '编辑关键词', '', 'manage/tag/edit', '59', '0', '0', '3', '2', '[]', '1', '0', '0', '编辑关键词', '2019-01-04 22:11:42', '2019-01-04 22:11:42'), ('62', 'Tag_Delet', '删除关键词', '', 'manage/tag/delete', '59', '0', '0', '3', '3', '[]', '1', '0', '0', '删除关键词', '2019-01-04 22:12:10', '2019-01-04 22:12:10'), ('63', 'Tag_Sort', '关键词快速排序', '', 'manage/tag/sort', '59', '0', '0', '3', '4', '[]', '1', '0', '0', '关键词快速排序', '2019-01-04 22:12:40', '2019-01-04 22:12:40');
COMMIT;

-- ----------------------------
--  Table structure for `com_operation_record`
-- ----------------------------
DROP TABLE IF EXISTS `com_operation_record`;
CREATE TABLE `com_operation_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `operation_name` varchar(64) NOT NULL DEFAULT '' COMMENT '操作流程名称，一般为对应流程的数据表表名称，譬如退货流程记录时值为：pro_returns',
  `business_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应的业务ID，譬如退货记录时该字段记录退货单ID',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '操作流程简要标题',
  `desc` varchar(512) NOT NULL DEFAULT '' COMMENT '操作流程描述',
  `creator` int(11) NOT NULL DEFAULT '0' COMMENT '操作者的用户ID',
  `creator_name` varchar(32) NOT NULL DEFAULT '' COMMENT '操作者姓名，用于直接显示',
  `creator_dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作者的部门ID',
  `creator_dept_name` varchar(200) NOT NULL DEFAULT '' COMMENT '操作者的部门名称',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `process_name` (`operation_name`,`business_id`) USING BTREE,
  KEY `creator` (`creator`) USING BTREE,
  KEY `creator_dept_id` (`creator_dept_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='具有操作流程的操作记录表';

-- ----------------------------
--  Table structure for `com_role`
-- ----------------------------
DROP TABLE IF EXISTS `com_role`;
CREATE TABLE `com_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `sort` bigint(20) DEFAULT NULL COMMENT '排序，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='系统角色';

-- ----------------------------
--  Records of `com_role`
-- ----------------------------
BEGIN;
INSERT INTO `com_role` VALUES ('1', 'Developer', '线上开发团队角色', '1', '2018-02-19 19:32:24', '2018-03-03 11:14:49');
COMMIT;

-- ----------------------------
--  Table structure for `com_role_menu`
-- ----------------------------
DROP TABLE IF EXISTS `com_role_menu`;
CREATE TABLE `com_role_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `menu_id` int(11) NOT NULL COMMENT '角色可使用的菜单ID',
  `permissions` enum('super','leader','staff','guest') NOT NULL COMMENT '角色权限级别，super超级管理员，leader部门管理员，staff职员，guest游客',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `role_id` (`role_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='角色所拥有的菜单权限[role表与role_menu表一对多]';

-- ----------------------------
--  Records of `com_role_menu`
-- ----------------------------
BEGIN;
INSERT INTO `com_role_menu` VALUES ('1', '1', '1', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('2', '1', '22', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('3', '1', '48', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('4', '1', '49', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('5', '1', '50', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('6', '1', '51', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('7', '1', '52', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('8', '1', '31', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('9', '1', '32', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('10', '1', '33', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('11', '1', '34', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('12', '1', '35', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('13', '1', '36', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('14', '1', '37', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('15', '1', '38', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('16', '1', '39', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('17', '1', '15', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('18', '1', '16', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('19', '1', '17', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('20', '1', '23', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('21', '1', '28', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('22', '1', '29', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('23', '1', '30', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('24', '1', '5', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('25', '1', '14', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('26', '1', '13', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('27', '1', '19', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('28', '1', '21', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('29', '1', '20', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('30', '1', '10', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('31', '1', '6', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('32', '1', '11', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('33', '1', '18', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('34', '1', '12', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('35', '1', '46', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('36', '1', '47', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('37', '1', '2', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('38', '1', '3', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('39', '1', '4', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('40', '1', '7', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('41', '1', '8', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('42', '1', '9', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('43', '1', '25', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('44', '1', '24', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('45', '1', '26', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('46', '1', '27', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('47', '1', '41', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('48', '1', '42', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('49', '1', '43', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('50', '1', '44', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50'), ('51', '1', '45', 'super', '2019-01-04 20:27:50', '2019-01-04 20:27:50');
COMMIT;

-- ----------------------------
--  Table structure for `com_site_config`
-- ----------------------------
DROP TABLE IF EXISTS `com_site_config`;
CREATE TABLE `com_site_config` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `flag` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项分组标记[字符串]，统一flag是一个分组',
  `key` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项字符串名称：字符串标记，程序中直接使用该值使用',
  `value` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项内容',
  `default` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项默认值',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项中文名称',
  `description` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项中文功能说明',
  `type` enum('text','radio','textarea') NOT NULL DEFAULT 'text' COMMENT '配置项后台显示的类型:输入框、单选项、大段文本',
  `var` json NOT NULL COMMENT 'radio单选框待选值列表',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='站点自定义配置表';

-- ----------------------------
--  Table structure for `com_user`
-- ----------------------------
DROP TABLE IF EXISTS `com_user`;
CREATE TABLE `com_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` varchar(32) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码（密文）',
  `real_name` varchar(32) NOT NULL COMMENT '真实姓名',
  `gender` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '性别：-1未知0女1男',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '座机号码',
  `auth_code` char(32) NOT NULL COMMENT '授权code，用于cookie加密(可变)',
  `is_leader` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否本部门的领导：1是0不是 用于直属部门内部审批、数据权限等识别',
  `dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属部门ID',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属角色ID',
  `enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用禁用标记：1启用0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_name` (`user_name`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `dept_id` (`dept_id`) USING BTREE,
  KEY `role_id` (`role_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='后台统一用户表：系统本身的登录授权基础表';

-- ----------------------------
--  Records of `com_user`
-- ----------------------------
BEGIN;
INSERT INTO `com_user` VALUES ('1', 'jing', '$2y$10$F8pbW15nHyKinDY94Y/xu.50yYrx8HamG4MDfzWc6B0jZnrTRSc9W', '杨晶晶', '1', '15872254727', 'jjonline@jjonline.cn', '0717-3320405', 'WKA0HXtQ', '1', '1', '1', '1', '', '2018-02-19 19:35:22', '2019-01-04 20:21:58');
COMMIT;

-- ----------------------------
--  Table structure for `com_user_log`
-- ----------------------------
DROP TABLE IF EXISTS `com_user_log`;
CREATE TABLE `com_user_log` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属部门ID',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '日志标题或描述',
  `os` varchar(128) NOT NULL DEFAULT '' COMMENT '操作系统信息',
  `browser` varchar(128) NOT NULL DEFAULT '' COMMENT '浏览器信息',
  `ip` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `location` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址解析出的归属地信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `dept_id` (`dept_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户可识别日志';

-- ----------------------------
--  Table structure for `com_user_open`
-- ----------------------------
DROP TABLE IF EXISTS `com_user_open`;
CREATE TABLE `com_user_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '后台管理系统统一用户ID，为0则表示尚未与用户绑定',
  `open_type` enum('qq','pc_weixin','mp_weixin','xiaochengxu','weibo') NOT NULL COMMENT '开放平台登录类型qq-QQ开放登录 pc_weixin-Pc网站版微信扫码登录 mp_weixin-微信公众号版微信登录 xiaochengxu-微信小程序登录 weibo-微博登录当(需要添加新类型时添加该枚举类型的待选值)',
  `open_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '性别：-1未知0女1男',
  `figure` varchar(128) NOT NULL DEFAULT '' COMMENT '头像图src',
  `union_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'UnionID',
  `expire_time` datetime DEFAULT NULL COMMENT 'Token过期时间点',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `open_id` (`open_id`,`open_type`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `union_id` (`union_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='多平台开放平台登录账户信息（用户和开放平台一对多）';

SET FOREIGN_KEY_CHECKS = 1;
