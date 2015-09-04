

UPDATE `sys_objects_actions` SET `Caption` = '{shareCpt}' WHERE `Caption` = '_bx_photos_action_share' AND `Type` = 'bx_photos';

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_photos_action_share');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_photos_action_share');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.2' WHERE `uri` = 'photos' AND `version` = '1.1.1';

