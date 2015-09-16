
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `bx_events_main` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`;

-- ================ can be safely applied multiple times ================ 

CREATE TABLE IF NOT EXISTS `bx_events_shoutbox` (
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

DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Desc` = 'Event''s Chat';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Column` = '3');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('bx_events_view', '1140px', 'Event''s Chat', '_Chat', '3', IFNULL(@iMaxOrder, 0), 'PHP', 'return BxDolService::call(''shoutbox'', ''get_shoutbox'', array(''bx_events'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 11, 28.1, 'non,memb', 0);

UPDATE `sys_objects_actions` SET `Icon` = 'plus-circle' WHERE `Icon` = 'plus-sign' AND `Type` = 'bx_events';
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_events';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_events';
UPDATE `sys_objects_actions` SET `Icon` = 'users' WHERE `Icon` = 'group' AND `Type` = 'bx_events';
UPDATE `sys_objects_actions` SET `Icon` = 'picture-o' WHERE `Icon` = 'picture' AND `Type` = 'bx_events';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_events';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_events' AND `Caption` = '{TitleActivate}';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{TitleActivate}', 'check-circle-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''activate/{ID}'';', '13', 'bx_events');

UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_events';

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'events' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('events', 'view_forum', '_bx_events_privacy_view_forum', 'p');

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'events' AND `version` = '1.1.6';

