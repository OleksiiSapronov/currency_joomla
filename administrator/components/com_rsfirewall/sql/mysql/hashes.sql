CREATE TABLE IF NOT EXISTS `#__rsfirewall_hashes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` text NOT NULL,
  `hash` varchar(32) NOT NULL,
  `type` varchar(64) NOT NULL,
  `flag` varchar(1) NULL,
  `date` varchar(255) NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;