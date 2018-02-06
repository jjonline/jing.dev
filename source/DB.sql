


-----多部门多角色基础component部门表
CREATE TABLE `com_department` (
  `id` char(36) NOT NULL COMMENT '部门ID',
  `name` varchar(200) NOT NULL COMMENT '部门名称',
  `parent_id` char(36) DEFAULT NULL COMMENT '父级部门ID，为NUll则是顶级部门',
  `level` int(11) NOT NULL COMMENT '部门层级：1->2->3逐次降低，当前仅用1、2即可',
  `sort` bigint(20) DEFAULT NULL COMMENT '部门排序，数字越小越靠前',
  `remark` varchar(64) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间（不为null则表示软删除）',
  PRIMARY KEY (`id`),
  KEY `PARENTID` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多层级部门表';