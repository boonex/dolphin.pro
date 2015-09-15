
UPDATE `sys_menu_top` SET `Picture` = 'square-o' WHERE `Picture` = 'check-empty' AND `Name` = 'Board';

UPDATE `sys_objects_actions` SET `Icon` = 'square-o' WHERE `Icon` = 'check-empty' AND `Type` = 'bx_photos';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'board' AND `version` = '1.1.6';

