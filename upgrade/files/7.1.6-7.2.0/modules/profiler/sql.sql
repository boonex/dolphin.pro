
UPDATE `sys_menu_admin` SET `icon` = 'flask' WHERE `name` = 'bx_profiler';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'profiler' AND `version` = '1.1.6';

