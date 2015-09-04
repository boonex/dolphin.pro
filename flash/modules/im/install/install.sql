DROP TABLE IF EXISTS `[module_db_prefix]Profiles`;
DROP TABLE IF EXISTS `[module_db_prefix]Contacts`;
DROP TABLE IF EXISTS `[module_db_prefix]Messages`;
DROP TABLE IF EXISTS `[module_db_prefix]Pendings`;
-- 
-- Table structure for table `IMProfiles`
-- 
CREATE TABLE IF NOT EXISTS `[module_db_prefix]Profiles` (
  `ID` int(11) NOT NULL default '0',
  `Smileset` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
);

TRUNCATE TABLE `[module_db_prefix]Profiles`;

-- 
-- Table structure for table `IMContacts`
-- 
CREATE TABLE IF NOT EXISTS `[module_db_prefix]Contacts` (
  `ID` int(11) NOT NULL auto_increment,
  `SenderID` int(11) NOT NULL default '0',
  `RecipientID` int(11) NOT NULL default '0',
  `Online` varchar(10) NOT NULL default 'online',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

TRUNCATE TABLE `[module_db_prefix]Contacts`;

-- 
-- Table structure for table `IMMessages`
-- 

CREATE TABLE IF NOT EXISTS `[module_db_prefix]Messages` (
  `ID` int(11) NOT NULL auto_increment,
  `ContactID` int(11) NOT NULL default '0',  
  `Message` text NOT NULL default '',
  `Style` text NOT NULL,
  `Type` varchar(10) NOT NULL default 'text', 
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

TRUNCATE TABLE `[module_db_prefix]Messages`;

-- 
-- Table structure for table `IMPendings`
-- 
CREATE TABLE IF NOT EXISTS `[module_db_prefix]Pendings` (
  `ID` int(11) NOT NULL auto_increment,
  `SenderID` int(11) NOT NULL default '0',
  `RecipientID` int(11) NOT NULL default '0',
  `Message` varchar(255) NOT NULL default '',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

ALTER TABLE `[module_db_prefix]Pendings` ADD INDEX ( `RecipientID` );
TRUNCATE TABLE `[module_db_prefix]Pendings`;