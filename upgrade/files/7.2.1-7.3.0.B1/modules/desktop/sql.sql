
UPDATE `sys_box_download` SET `icon` = 'desktop' WHERE `icon` = 'modules/boonex/desktop/|desktop.png';

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'desktop' AND `version` = '1.2.1';

