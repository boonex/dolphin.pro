
ALTER TABLE `bx_ads_main` CHANGE `Subject` `Subject` VARCHAR(100) NOT NULL;

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.4' WHERE `uri` = 'ads' AND `version` = '1.3.3';

