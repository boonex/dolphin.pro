

-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_membership';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'membership' AND `version` = '1.0.9';

