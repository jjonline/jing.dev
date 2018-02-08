


-----多部门多角色基础component部门表
CREATE TABLE `com_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `name` varchar(200) NOT NULL COMMENT '部门名称',
  `parent_id` char(36) DEFAULT NULL COMMENT '父级部门ID，为NUll则是顶级部门',
  `level` int(11) NOT NULL COMMENT '部门层级：1->2->3逐次降低，最大层级5',
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
  `is_leader` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否领导：1是0不是 用于部门内部审批、数据权限等识别',
  `dept_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属部门ID',
  `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '所属角色ID',
  `enable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用禁用标记：1启用0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`,`mobile`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户表：与职员表对应(staff中存在user中可不存在，user中存在staff中绝对要存在)';


-----雇员信息表[员工信息表]，
CREATE TABLE `com_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '员工ID',
  `user_id` int(11) NOT NULL DEFAULT '' COMMENT '后台hsz 管理系统ID',
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
CREATE TABLE `yk_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(64) NOT NULL COMMENT '菜单名称Tag',
  `icon` varchar(64) NOT NULL DEFAULT '' COMMENT 'fontawesome、glyphicon或ionicons图标的class',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单Url：无前缀斜线',
  `parent_id` int(11) NOT NULL DEFAULT '' COMMENT '父菜单ID',
  `level` int(11) NOT NULL COMMENT '当前层级',
  `sort` int(11) NOT NULL COMMENT '排序数字越小越靠前',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='功能菜单[节点]';

----角色划分
CREATE TABLE `yk_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `level` int(11) NOT NULL COMMENT '角色层级：1->2->3',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `sort` bigint(20) DEFAULT NULL COMMENT '排序，数字越小越靠前',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统角色';
