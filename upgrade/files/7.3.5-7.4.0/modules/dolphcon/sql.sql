

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_dolphcon';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_dolphcon', '_sys_module_dolphcon', 'BxDolphConExport', 'modules/boonex/dolphin_connect/classes/BxDolphConExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'dolphcon' AND `version` = '1.3.5';

