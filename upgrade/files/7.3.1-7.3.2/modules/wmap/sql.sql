
UPDATE `sys_options` SET `desc` = 'Google Maps API key' WHERE `Name` = 'bx_wmap_key';

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'wmap' AND `version` = '1.3.1';

