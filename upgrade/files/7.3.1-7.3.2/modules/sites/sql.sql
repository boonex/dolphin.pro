
ALTER TABLE `bx_sites_main` CHANGE  `url`  `url` VARCHAR(255) NOT NULL;

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'sites' AND `version` = '1.3.1';

