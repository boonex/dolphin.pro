CREATE TABLE IF NOT EXISTS `[module_db_prefix]CurrentUsers` (
  `ID` varchar(20) NOT NULL default '',
  `Nick` varchar(255) NOT NULL,
  `Sex` enum('M','F') NOT NULL default 'M',
  `Age` int(11) NOT NULL default '0',
  `Photo` varchar(255) NOT NULL default '',
  `Profile` varchar(255) NOT NULL default '',
  `Desc` varchar(255) NOT NULL,
  `When` int(11) NOT NULL default '0',
  `Status` enum('new','old','idle') NOT NULL default 'new',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

TRUNCATE TABLE `[module_db_prefix]CurrentUsers`;

CREATE TABLE IF NOT EXISTS `[module_db_prefix]Boards` (
  `ID` int(11) NOT NULL auto_increment,  
  `Name` varchar(255) NOT NULL default '',
  `Password` varchar(255) NOT NULL default '',
  `OwnerID` varchar(20) NOT NULL default '0', 
  `When` int(11) default NULL,
  `Status` enum('new', 'normal','delete') NOT NULL default 'new',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

TRUNCATE TABLE `[module_db_prefix]Boards`;

CREATE TABLE IF NOT EXISTS `[module_db_prefix]Users` (
  `ID` int(11) NOT NULL auto_increment,  
  `Board` int(11) NOT NULL default '0',
  `User` varchar(20) NOT NULL default '',
  `When` int(11) default NULL,
  `Status` enum('normal','delete') NOT NULL default 'normal',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
TRUNCATE TABLE `[module_db_prefix]Users`;