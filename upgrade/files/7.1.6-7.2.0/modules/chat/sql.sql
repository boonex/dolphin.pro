
UPDATE `sys_menu_top` SET `Picture` = 'comments-o' WHERE `Picture` = 'comments-alt' AND `Name` = 'Chat';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'chat' AND `version` = '1.1.6';

