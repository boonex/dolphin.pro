
-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'beaker' WHERE `name` = 'bx_profiler';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'profiler' AND `version` = '1.0.9';

