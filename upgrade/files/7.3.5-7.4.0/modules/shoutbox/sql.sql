

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_shoutbox';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_shoutbox', '_sys_module_shoutbox', 'BxShoutBoxExport', 'modules/boonex/shoutbox/classes/BxShoutBoxExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'shoutbox' AND `version` = '1.3.5';

