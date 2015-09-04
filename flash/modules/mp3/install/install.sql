CREATE TABLE IF NOT EXISTS `[module_db_prefix]Files` (
  `ID` int(11) NOT NULL auto_increment,
  `Categories` text NOT NULL,
  `Title` varchar(255) NOT NULL default '',
  `Uri` varchar(255) NOT NULL default '',
  `Tags` text NOT NULL,
  `Description` text NOT NULL,
  `Time` int(11) NOT NULL default '0',
  `Date` int(20) NOT NULL default '0',
  `Reports` int(11) NOT NULL default '0',
  `Owner` varchar(64) NOT NULL default '',
  `Listens` int(12) default '0',
  `Rate` float NOT NULL,
  `RateCount` int(11) NOT NULL,
  `CommentsCount` int(11) NOT NULL,
  `Featured` tinyint(4) NOT NULL,
  `Status` enum('approved','disapproved','pending','processing','failed') NOT NULL default 'pending',
  PRIMARY KEY  (`ID`),
  KEY (`Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[module_db_prefix]Tokens` (
  `ID` int(11) NOT NULL default '0',
  `Token` varchar(32) NOT NULL default '',
  `Date` int(20) NOT NULL default '0',
  PRIMARY KEY `TokenId` (`ID`,`Token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;