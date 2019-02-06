
ALTER TABLE `bx_files_main` ADD KEY `Uri` (`Uri`);

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.4' WHERE `uri` = 'files' AND `version` = '1.3.3';

