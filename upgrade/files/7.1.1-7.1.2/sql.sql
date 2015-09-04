
-- ================ can be safely applied multiple times ================ 

ALTER TABLE  `sys_alerts` 
CHANGE `unit` `unit` VARCHAR(64) NOT NULL,
CHANGE `action` `action` VARCHAR(64) DEFAULT 'none';


DELETE FROM `sys_options` WHERE `Name` = 'sys_antispam_smart_check';
INSERT INTO `sys_options` VALUES ('sys_antispam_smart_check', 'on', 23, 'Smart antispam check', 'checkbox', '', '', 70, '');

UPDATE `sys_options` SET `AvailableValues` = 'File,Memcache,APC,XCache' WHERE `Name` = 'sys_db_cache_engine' OR `Name` = 'sys_pb_cache_engine' OR `Name` = 'sys_mm_cache_engine';
UPDATE `sys_options` SET `AvailableValues` = 'FileHtml,Memcache,APC,XCache' WHERE `Name` = 'sys_template_cache_engine';

UPDATE `sys_options` SET `VALUE` = 'File' WHERE `VALUE` = 'EAccelerator' AND (`Name` = 'sys_db_cache_engine' OR `Name` = 'sys_pb_cache_engine' OR `Name` = 'sys_mm_cache_engine');
UPDATE `sys_options` SET `VALUE` = 'FileHtml' WHERE `VALUE` = 'EAccelerator' AND `Name` = 'sys_template_cache_engine';


UPDATE `sys_profile_fields` SET `EditOwnBlock` = 0, `EditOwnOrder` = NULL, `EditAdmBlock` = 0, `EditAdmOrder` = NULL WHERE `Name` = 'NickName';


UPDATE `sys_menu_top` SET `Check` = '' WHERE `ID` = 4 OR `ID` = 9;


DELETE FROM `sys_dnsbl_rules` WHERE `chain` = 'spammers' AND `zonedomain` = 'zomgbl.spameatingmonkey.net.';


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.2', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.2';

