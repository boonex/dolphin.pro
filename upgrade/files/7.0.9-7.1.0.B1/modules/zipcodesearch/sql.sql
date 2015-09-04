
-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'search' WHERE `name` = 'bx_zip';



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_zip_search_block_caption','_bx_zip_search_form_caption');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_zip_search_block_caption','_bx_zip_search_form_caption');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'zipcodesearch' AND `version` = '1.0.9';

