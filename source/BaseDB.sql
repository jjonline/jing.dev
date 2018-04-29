
---基础表结构


-----多部门多角色基础component部门表
CREATE TABLE `com_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `name` varchar(200) NOT NULL COMMENT '部门名称',
  `parent_id` char(36) DEFAULT NULL COMMENT '父级部门ID，为NUll则是顶级部门',
  `level` int(11) NOT NULL COMMENT '部门层级：1->2->3逐次降低，最大层级6',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='多层级部门表';


-----多部门多角色基础component用户表
CREATE TABLE `com_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` varchar(32) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码（密文）',
  `real_name` varchar(32) NOT NULL COMMENT '真实姓名',
  `gender` tinyint(1) NOT NULL DEFAULT -1 COMMENT '性别：-1未知0女1男',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '座机号码',
  `auth_code` char(32) NOT NULL COMMENT '授权code，用于cookie加密(可变)',
  `is_leader` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否本部门的领导：1是0不是 用于直属部门内部审批、数据权限等识别',
  `dept_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属部门ID',
  `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属角色ID',
  `enable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用禁用标记：1启用0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台统一用户表：系统本身的登录授权基础表';

-----开放平台登录账户信息
CREATE TABLE `com_user_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '后台管理系统统一用户ID，为0则表示尚未与用户绑定',
  `open_type` ENUM('qq','pc_weixin','mp_weixin','xiaochengxu','weibo') NOT NULL COMMENT '开放平台登录类型qq-QQ开放登录 pc_weixin-Pc网站版微信扫码登录 mp_weixin-微信公众号版微信登录 xiaochengxu-微信小程序登录 weibo-微博登录当(需要添加新类型时添加该枚举类型的待选值)',
  `open_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) NOT NULL DEFAULT -1 COMMENT '性别：-1未知0女1男',
  `figure` varchar(128) NOT NULL DEFAULT '' COMMENT '头像图src',
  `union_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'UnionID',
  `expire_time` datetime DEFAULT NULL COMMENT 'Token过期时间点',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `open_id` (`open_id`,`open_type`),
  KEY `user_id` (`user_id`),
  KEY `union_id` (`union_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='多平台开放平台登录账户信息（用户和开放平台一对多）';



-----雇员信息表[员工信息表]，
CREATE TABLE `com_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '员工ID',
  `user_id` int(11) NOT NULL DEFAULT '' COMMENT '后台管理系统统一用户ID',
  `is_leader` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否领导：1是0不是 用于部门内部审批、数据权限等识别',
  `dept_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属部门ID',
  `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属角色ID',
  `real_name` varchar(32) NOT NULL COMMENT '真实姓名',
  `gender` tinyint(1) NOT NULL DEFAULT -1 COMMENT '性别：-1未知0女1男',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '座机号码',


  --TODO

  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='职员信息表';

-----权限功能菜单[节点]
CREATE TABLE `com_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tag` varchar(64) NOT NULL COMMENT '菜单名称Tag，唯一的字符串',
  `name` varchar(64) NOT NULL COMMENT '菜单名称',
  `icon` varchar(64) NOT NULL DEFAULT '' COMMENT 'fontawesome、glyphicon或ionicons图标的class',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单Url：无前缀斜线',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父菜单ID',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '标记是否必选，即所有登录用户均可使用，1必选0权限控制，为1时选择角色菜单权限的时候默认勾选且不可取消',
  `is_badge` tinyint(1) NOT NULL DEFAULT 0 COMMENT '菜单所标识的功能中是否需要使用badge统计，显示待办事项等badge',
  `level` int(11) NOT NULL COMMENT '当前层级 1为一级导航2为二级导航3为二级导航页面中的功能按钮',
  `sort` int(11) NOT NULL COMMENT '排序数字越小越靠前',
  `extra_param` varchar(512) NOT NULL DEFAULT '' COMMENT '额外存储的菜单对应操作所需要的限定参数，serialize序列化的字符串格式',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '标记是否系统菜单，1不允许删除0允许',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='功能菜单[节点]';

----角色划分
CREATE TABLE `com_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `sort` bigint(20) DEFAULT NULL COMMENT '排序，仅用于列表排序没有权限级别高低的区分作用，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统角色';

----角色所拥有的菜单权限
CREATE TABLE `com_role_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `menu_id` int(11) NOT NULL COMMENT '角色可使用的菜单ID',
  `permissions` ENUM('super','leader','staff','guest') NOT NULL COMMENT '角色权限级别，super超级管理员，leader部门管理员，staff职员，guest游客',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色所拥有的菜单权限[role表与role_menu表一对多]';


-----用户操作动作的详细日志，每个请求都记录
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
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作动作的详细日志，每个请求都记录';

----附件表-即文件上传所保存的上传文件的信息表
CREATE TABLE `com_attachment` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `file_origin_name` varchar(128) NOT NULL COMMENT '带后缀的上传时的原始文件名',
  `file_name` varchar(128) NOT NULL COMMENT '带后缀的上传完毕保存的文件名',
  `file_path` varchar(512) NOT NULL COMMENT '相对于网站根目录的带文件名的文件路径，斜杠开头，方便切换CDN',
  `file_mime` varchar(64) NOT NULL COMMENT '文件mime类型',
  `file_size` bigint(20)  NOT NULL COMMENT '资源大小，单位：Bytes即B，1024B = 1KB',
  `file_sha1` varchar(40) NOT NULL COMMENT '资源的sha1值',
  `image_width` int(10)  NOT NULL DEFAULT '0' COMMENT '图片类型宽资源的宽度',
  `image_height` int(10) NOT NULL DEFAULT '0' COMMENT '图片类型资源的高度',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`,`file_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件表：用户上传资源数据';


---具有操作流程的操作记录表
CREATE TABLE `com_operation_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `operation_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作流程名称，一般为对应流程的数据表表名称，譬如退货流程记录时值为：pro_returns',
  `business_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应的业务ID，譬如退货记录时该字段记录退货单ID',
  `title` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作流程简要标题',
  `desc` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '操作流程描述',
  `creator` int(11) NOT NULL DEFAULT '0' COMMENT '操作者的用户ID',
  `creator_name` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '操作者姓名，用于直接显示',
  `creator_dept_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作者的部门ID',
  `creator_dept_name` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '操作者的部门名称',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `process_name` (`operation_name`,`business_id`),
  KEY `creator` (`creator`),
  KEY `creator_dept_id` (`creator_dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='具有操作流程的操作记录表';

---异步任务记录
CREATE TABLE `com_async_task` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `dept_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属部门ID',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '异步任务可识读标题:由底层类属性标记',
  `task` varchar(128) NOT NULL DEFAULT '' COMMENT '异步任务:对应底层类名',
  `task_data` text NOT NULL COMMENT '异步任务参数数据，JSON字符串',
  `result` text NOT NULL COMMENT '异步任务执行结果描述，描述文本',
  `task_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '异步任务执行状态：0、未投递未执行，1、已投递正在执行，2、执行成功，3、执行失败',
  `delivery_time` datetime DEFAULT NULL COMMENT '任务投递时间',
  `finish_time` datetime DEFAULT NULL COMMENT '任务结束时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异步任务记录';

---用户可识别日志
CREATE TABLE `com_user_log` (
  `id` char(36) NOT NULL COMMENT 'ID，UUID形式',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `dept_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属部门ID',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '日志标题或描述',
  `os` varchar(128) NOT NULL DEFAULT '' COMMENT '操作系统信息',
  `browser` varchar(128) NOT NULL DEFAULT '' COMMENT '浏览器信息',
  `ip` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `location` varchar(128) NOT NULL DEFAULT '' COMMENT 'IP地址解析出的归属地信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `dept_id` (`dept_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户可识别日志';


-----应用层会员系统主表
CREATE TABLE `com_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` varchar(32) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码（密文）',
  `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `gender` tinyint(1) NOT NULL DEFAULT -1 COMMENT '性别：-1未知0女1男',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '座机号码',
  `auth_code` char(32) NOT NULL COMMENT '授权code，用于cookie加密(可变)',
  `province` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的省份',
  `city` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的地区|市单位',
  `district` varchar(32) NOT NULL DEFAULT '' COMMENT 'distpicker插件的县级',
  `address` varchar(256) NOT NULL DEFAULT '' COMMENT '会员的完整地址',
  `current_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员当前积分',
  `accumulate_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员累加积分，只加(正常消费)不减，惩罚性对应扣除累积积分',
  `enable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用禁用标记：1启用0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='前台会员主表';

-----会员开放平台登录账户信息
CREATE TABLE `com_member_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `member_id` int(11) NOT NULL DEFAULT 0 COMMENT '会员表ID',
  `open_type` ENUM('qq','pc_wx','mp_wx','xcx','wb') NOT NULL COMMENT '开放平台登录类型qq-QQ开放登录 pc_wx-Pc网站版微信扫码登录 mp_wx-微信公众号版微信登录 xcx-微信小程序登录 wb-微博登录当(需要添加新类型时添加该枚举类型的待选值)',
  `open_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) NOT NULL DEFAULT -1 COMMENT '性别：-1未知0女1男',
  `figure` varchar(128) NOT NULL DEFAULT '' COMMENT '头像图src',
  `union_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'UnionID',
  `expire_time` datetime DEFAULT NULL COMMENT 'Token过期时间点',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `open_id` (`open_id`,`open_type`),
  KEY `member_id` (`member_id`),
  KEY `union_id` (`union_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员多平台开放平台登录账户信息';

-----应用层会员积分变动记录表
CREATE TABLE `com_member_point_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `member_id` int(11) NOT NULL DEFAULT 0 COMMENT '客户表的ID，若没有则为0',
  `points_changes` int(11) NOT NULL DEFAULT '0' COMMENT '积分变动数量：增加正数消费负数',
  `current_points` int(11) NOT NULL DEFAULT '0' COMMENT '变动后积分数量，不得为负数',
  `accumulate_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员累加积分，只加(正常消费)不减，惩罚性对应扣除累积积分',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '积分变动后台操作用户ID，0表示无关后台用户',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员积分变动记录表';