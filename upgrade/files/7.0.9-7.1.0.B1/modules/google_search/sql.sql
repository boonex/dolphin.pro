

-- menu admin 

UPDATE `sys_menu_admin` SET `icon` = 'google-plus' WHERE `name` = 'bx_gsearch';


-- menu top 

UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Name` = 'Google Search';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'google_search' AND `version` = '1.0.9';

