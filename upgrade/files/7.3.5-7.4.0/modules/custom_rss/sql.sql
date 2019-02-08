

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_crss';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_crss', '_sys_module_custom_rss', 'BxCRSSExport', 'modules/boonex/custom_rss/classes/BxCRSSExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'custom_rss' AND `version` = '1.3.5';

