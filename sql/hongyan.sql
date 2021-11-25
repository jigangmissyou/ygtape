CREATE TABLE `dev_hong_yan` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `code` varchar(20) DEFAULT NULL COMMENT '母件编码',
  `item_name` varchar(200) DEFAULT NULL COMMENT '名称',
  `model_no` varchar(200) DEFAULT NULL COMMENT '规格型号',
  `unit` char(50) DEFAULT NULL COMMENT '单位',
  `price_with_tax` double DEFAULT NULL COMMENT '含税价格',
  `currency_type` char(10) DEFAULT NULL COMMENT '币种',
  `local_currency` double DEFAULT NULL COMMENT '本币',
  `price_without_tax` double DEFAULT NULL COMMENT '不含税价格',
  `ctime` int(15) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`) USING BTREE
) DEFAULT CHARSET=gbk COMMENT='洪研价格';
