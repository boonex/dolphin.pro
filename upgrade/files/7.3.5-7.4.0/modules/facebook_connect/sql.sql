

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_facebook';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_facebook', '_sys_module_facebook_connect', 'BxFaceBookConnectExport', 'modules/boonex/facebook_connect/classes/BxFaceBookConnectExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'facebook_connect' AND `version` = '1.3.5';

