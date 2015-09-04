CREATE TABLE IF NOT EXISTS `[module_db_prefix]Files` (
  `ID` int(11) NOT NULL auto_increment,
  `Categories` TEXT NOT NULL default '',
  `Title` varchar(255) NOT NULL default '',
  `Uri` varchar(255) NOT NULL default '',
  `Tags` TEXT NOT NULL default '',
  `Description` TEXT NOT NULL default '',
  `Time` int(11) NOT NULL default '0',
  `Date` int(20) NOT NULL default '0',
  `Owner` varchar(64) NOT NULL default '',
  `Views` int(12) NOT NULL default '0',
  `Status` enum('approved','disapproved','pending','processing','failed') NOT NULL default 'pending',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[module_db_prefix]Tokens` (
  `ID` int(11) NOT NULL default '0',
  `Token` varchar(32) NOT NULL default '',
  `Date` int(20) NOT NULL default '0',
  PRIMARY KEY `TokenId` (`ID`,`Token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;