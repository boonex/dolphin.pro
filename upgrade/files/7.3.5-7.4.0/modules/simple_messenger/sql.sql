

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_simple_messenger';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_simple_messenger', '_sys_module_simple_messenger', 'BxSimpleMessengerExport', 'modules/boonex/simple_messenger/classes/BxSimpleMessengerExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'simple_messenger' AND `version` = '1.3.5';

