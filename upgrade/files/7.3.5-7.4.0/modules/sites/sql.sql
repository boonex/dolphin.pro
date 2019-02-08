

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_sites';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_sites', '_sys_module_sites', 'BxSitesExport', 'modules/boonex/sites/classes/BxSitesExport.php', @iMaxOrderExports, 1);


DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_wall_reposted_bx_sites_commentPost','_wall_reposted_title_bx_sites_commentPost');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_wall_reposted_bx_sites_commentPost','_wall_reposted_title_bx_sites_commentPost');


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'sites' AND `version` = '1.3.5';

