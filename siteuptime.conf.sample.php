<?php
$cfg['mysql']['host'] =         'localhost';
$cfg['mysql']['database'] =     'siteuptime-endpoint';
$cfg['mysql']['table'] =        'siteuptime';
$cfg['mysql']['user'] =         'siteuptime';
$cfg['mysql']['pw'] =           'password';
$cfg['siteuptime']['UserId'] =  'xxxxxxxx';
$cfg['siteuptime']['Id'] =      'xxxx';
$cfg['request_key'] =           '';

/*
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `siteuptime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `page_latency` float NOT NULL,
  `loadavg` text NOT NULL,
  `meta` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used for logging siteuptime.com checks' AUTO_INCREMENT=38234 ;
*/

?>
