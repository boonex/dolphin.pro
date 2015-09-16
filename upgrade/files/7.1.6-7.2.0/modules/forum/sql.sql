
UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_forum';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'forum' AND `version` = '1.1.6';

