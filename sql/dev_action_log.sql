CREATE TABLE `dev_action_log` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `ticket_no` char(10) DEFAULT NULL,
  `action_name` char(100) DEFAULT NULL COMMENT '变更项',
  `action_type` tinyint(3) DEFAULT NULL COMMENT '类型',
  `from_data` char(100) DEFAULT NULL COMMENT '原来数据',
  `to_data` char(100) DEFAULT NULL COMMENT '现在数据',
  `uname` varchar(100) DEFAULT NULL COMMENT '用户名',
  `ctime` int(15) DEFAULT NULL COMMENT '创建时间',
  `mtime` int(15) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;