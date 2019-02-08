

DELETE FROM `sys_objects_exports` WHERE `object` = '[db_prefix]';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_files', 'BxFilesExport', 'modules/boonex/files/classes/BxFilesExport.php', @iMaxOrderExports, 1);


DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_files_i_have_the_right_to_distribute');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_files_i_have_the_right_to_distribute');


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'files' AND `version` = '1.3.5';

