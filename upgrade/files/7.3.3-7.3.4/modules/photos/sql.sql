
ALTER TABLE `bx_photos_main` ADD KEY `Uri` (`Uri`);

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.4' WHERE `uri` = 'photos' AND `version` = '1.3.3';

