
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `bx_shoutbox_messages` ADD `HandlerID` int(11) NOT NULL AFTER `ID`;
ALTER TABLE `bx_shoutbox_messages` ADD INDEX (`HandlerID`);


-- ================ can be safely applied multiple times ================ 

ALTER TABLE `bx_shoutbox_messages` CHANGE `Message`  `Message` BLOB NOT NULL;


CREATE TABLE IF NOT EXISTS `bx_shoutbox_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `code_allow_use` varchar(255) NOT NULL,
  `code_allow_delete` varchar(255) NOT NULL,
  `code_allow_block` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;

TRUNCATE TABLE `bx_shoutbox_objects`;

INSERT INTO `bx_shoutbox_objects` (`name`, `title`, `table`, `code_allow_use`, `code_allow_delete`, `code_allow_block`) VALUES
('bx_shoutbox', '_bx_shoutbox', 'bx_shoutbox_messages', '', '', '');


UPDATE `sys_page_compose` SET `DesignBox` = 11 WHERE `DesignBox` = 1 AND `Page` = 'index' AND `Desc` = 'Shoutbox';


DELETE FROM `sys_options` WHERE `Name` = 'shoutbox_process_smiles';


SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_shoutbox_profile_delete' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('bx_shoutbox_profile_delete', 'BxDolService::call(''shoutbox'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'shoutbox' AND `version` = '1.1.6';

