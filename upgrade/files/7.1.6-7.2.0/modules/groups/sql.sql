
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `bx_groups_main` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`;

-- ================ can be safely applied multiple times ================ 

CREATE TABLE IF NOT EXISTS `bx_groups_shoutbox` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `HandlerID` int(11) NOT NULL,
  `OwnerID` int(11) NOT NULL,
  `Message` blob NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IP` (`IP`),
  KEY `HandlerID` (`HandlerID`)
) ENGINE=MyISAM;


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_view' AND `Desc` = 'Group''s chat';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_groups_view' AND `Column` = '3');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('bx_groups_view', '1140px', 'Group''s chat', '_Chat', 3, IFNULL(@iMaxOrder, 0), 'PHP', 'return BxDolService::call(''shoutbox'', ''get_shoutbox'', array(''bx_groups'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 11, 28.1, 'non,memb', 0);


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'plus-circle' WHERE `Icon` = 'plus-sign' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'users' WHERE `Icon` = 'group' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'picture-o' WHERE `Icon` = 'picture' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_groups';
UPDATE `sys_objects_actions` SET `Icon` = 'users' WHERE `Icon` = 'group' AND `Type` = 'bx_groups_title';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_groups' AND `Caption` = '{TitleActivate}';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{TitleActivate}', 'check-circle-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''activate/{ID}'';', '14', 'bx_groups');


UPDATE `sys_menu_top` SET `Picture` = 'users' WHERE `Picture` = 'group' AND `Icon` = '' AND `Name` = 'Groups' AND `Parent` = 0;
UPDATE `sys_menu_top` SET `Picture` = 'users', `Icon` = 'users' WHERE `Picture` = 'group' AND `Icon` = 'group' AND `Name` = 'Groups' AND `Parent` = 0;


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_groups';


UPDATE `sys_menu_admin` SET `icon` = 'users' WHERE `name` = 'bx_groups' AND `icon` = 'group';


UPDATE `sys_stat_site` SET `IconName` = 'users' WHERE `Name` = 'bx_groups';


DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'groups' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('groups', 'view_forum', '_bx_groups_privacy_view_forum', 'f');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'groups' AND `version` = '1.1.6';

