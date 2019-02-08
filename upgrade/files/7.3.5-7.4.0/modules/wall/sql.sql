

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_wall';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_wall', '_wall', 'BxWallExport', 'modules/boonex/wall/classes/BxWallExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'wall' AND `version` = '1.3.5';

