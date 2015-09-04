

UPDATE `sys_objects_actions` SET `Caption` = '{shareCpt}' WHERE `Type` = 'bx_files' AND `Caption` = '_bx_files_action_share';


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_files_action_share');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_files_action_share');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.2' WHERE `uri` = 'files' AND `version` = '1.1.1';

