
UPDATE `sys_menu_member` SET `Icon` = 'bell', `Order` = 3 WHERE `Name` = 'Spy';

UPDATE `sys_menu_admin` SET `icon` = 'crosshairs' WHERE `name` = 'Spy';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'spy' AND `version` = '1.1.6';

