

-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'comments-alt' WHERE `Name` = 'Chat';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'chat' AND `version` = '1.0.9';

