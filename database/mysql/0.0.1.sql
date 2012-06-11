CREATE TABLE IF NOT EXISTS `slideatlas_item` (
  `slideatlas_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) NOT NULL,
  `item_order` bigint(20) NOT NULL,
  PRIMARY KEY (`slideatlas_id`)
)   DEFAULT CHARSET=utf8;
