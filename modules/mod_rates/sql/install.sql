CREATE TABLE IF NOT EXISTS `ypfrk_rates_history` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'record id',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `currency_from` varchar(3) NOT NULL,
  `currency_to` varchar(3) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 