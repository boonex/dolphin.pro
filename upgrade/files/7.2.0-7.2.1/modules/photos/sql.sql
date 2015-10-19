
UPDATE `sys_options` SET `VALUE` = 'jpg jpeg png gif' WHERE `Name` = 'bx_photos_allowed_exts' AND `VALUE` = 'jpg png gif';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.1' WHERE `uri` = 'photos' AND `version` = '1.2.0';

