CREATE TABLE IF NOT EXISTS `#__rsfirewall_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` enum('low','medium','high','critical') NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NULL,
  `page` text NULL,
  `referer` text NULL,
  `code` varchar(255) NOT NULL,
  `debug_variables` text NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) DEFAULT CHARSET=utf8;