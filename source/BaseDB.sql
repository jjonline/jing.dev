
-- ----------------------------
--  Table structure for `com_async_task`
-- ----------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='多层级部门表';


-- ----------------------------
--  Table structure for `com_log`
-- ----------------------------
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
  `extra_param` text DEFAULT NULL COMMENT '额外存储的菜单对应操作所需要的限定参数，json格式',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否系统菜单，1不允许删除0允许',
  `is_permissions` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否有数据范围控制',
  `is_column` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记是否需要控制字段显示，1：是 0:否',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tag` (`tag`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='功能菜单[节点]';

-- ----------------------------
--  Table structure for `com_operation_record`
-- ----------------------------
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
CREATE TABLE `com_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `sort` bigint(20) DEFAULT NULL COMMENT '排序，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='系统角色';

-- ----------------------------
--  Table structure for `com_role_menu`
-- ----------------------------
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
--  Table structure for `com_site_config`
-- ----------------------------
CREATE TABLE `com_site_config` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `flag` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项分组标记[字符串]，统一flag是一个分组',
  `key` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项字符串名称：字符串标记，程序中直接使用该值使用',
  `value` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项内容',
  `default` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项默认值',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项中文名称',
  `description` varchar(1024) NOT NULL DEFAULT '' COMMENT '配置项中文功能说明',
  `type` enum('text','select','textarea') NOT NULL DEFAULT 'text' COMMENT '配置项后台显示的类型:输入框、单选项、大段文本',
  `select_items` text NOT NULL COMMENT 'radio单选框待选值列表',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='站点自定义配置表';

-- ----------------------------
--  Table structure for `com_user`
-- ----------------------------
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
CREATE TABLE `com_user_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '后台管理系统统一用户ID，为0则表示尚未与用户绑定',
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
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `union_id` (`union_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='多平台开放平台登录账户信息（用户和开放平台一对多）';


-- 文章
CREATE TABLE `com_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '标题',
  `sub_title` varchar(32) NOT NULL DEFAULT '' COMMENT '小标题：精简标题',
  `cover_id` char(36) NOT NULL DEFAULT '' COMMENT '封面图ID',
  `cat_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类',
  `tag_ids` text NOT NULL COMMENT 'tag关键词的id构成的json',
  `content_type` tinyint(1) NOT NULL COMMENT '文章类型1-文本 2-图片、3-音乐、4-视频...',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT '导读摘要，最多140字',
  `content` text NOT NULL COMMENT '图文正文富文本',
  `author_id` int(11) NOT NULL DEFAULT '0' COMMENT '作者表的ID',
  `source_url` varchar(255) NOT NULL DEFAULT '' COMMENT '转载情况下的原始来源url',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '点击次数',
  `awesome` int(11) NOT NULL DEFAULT '0' COMMENT '点赞次数：各种awesome总数',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否栏目置顶推荐',
  `is_home` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否首页置顶推荐',
  `allow_comment` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许评论',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属后台用户ID',
  `dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属后台部门ID',
  `is_effected` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用禁用标记：1启用0禁用',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`),
  KEY `author_id` (`author_id`),
  KEY `user` (`user_id`,`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图文表';

-- 图文分类表
CREATE TABLE `com_article_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '分类名称',
  `parent_id` int(11) DEFAULT NULL COMMENT '父分类ID，为NUll则是顶级分类',
  `level` int(11) NOT NULL COMMENT '分类层级：1->2->3逐次降低',
  `sort` bigint(20) NOT NULL DEFAULT '0' COMMENT '排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图文分类表';

-- 关键词列表
CREATE TABLE `com_article_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cover_id` char(36) NOT NULL DEFAULT '' COMMENT '封面图ID',
  `icon_id` char(36) NOT NULL DEFAULT '' COMMENT 'ICON图标ID',
  `tag` varchar(64) NOT NULL DEFAULT '' COMMENT 'tag关键词',
  `reference_times` int(11) NOT NULL DEFAULT '0' COMMENT '大致的引用次数',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT '概要介绍，最多140字',
  `introduction` text NOT NULL COMMENT '详细介绍',
  `sort` bigint(20) NOT NULL DEFAULT '0' COMMENT '排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关键词列表';

-- 图文评论表
CREATE TABLE `com_article_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `parent_id` int(11) DEFAULT NULL COMMENT '父级ID',
  `level` int(11) NOT NULL COMMENT '层级：1->2->3逐次降低',
  `article_id` int(11) NOT NULL DEFAULT '0' COMMENT '被评论的文章ID',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '评论者会员ID',
  `context` varchar(1024) NOT NULL DEFAULT '' COMMENT '评论内容',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图文评论表';

-- 作者投稿者信息表
CREATE TABLE `com_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `figure_id` char(36) NOT NULL DEFAULT '0' COMMENT '头像ID',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '作者名称',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型编码，1-公众号 2-头条 等',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT '概要介绍，最多140字',
  `introduction` text NOT NULL COMMENT '详细介绍',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '可能关联的前台会员ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属后台用户ID',
  `dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属后台部门ID',
  `sort` bigint(20) NOT NULL DEFAULT '0' COMMENT '排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='作者信息表';

