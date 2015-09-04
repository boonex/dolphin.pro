

-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'unlock' WHERE `name` = 'bx_pageac';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'pageac' AND `version` = '1.0.9';

