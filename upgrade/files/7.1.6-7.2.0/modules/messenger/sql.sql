
UPDATE `sys_objects_actions` SET `Icon` = 'comments-o' WHERE `Icon` = 'comments-alt' AND `Type` = 'Profile';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'messenger' AND `version` = '1.1.6';

