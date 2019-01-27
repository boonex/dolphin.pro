
-- delete tables of the module
DROP TABLE IF EXISTS `[db_prefix]main`;
DROP TABLE IF EXISTS `[db_prefix]units`;
DROP TABLE IF EXISTS `[db_prefix]themes`;
DROP TABLE IF EXISTS `[db_prefix]images`;

-- delete permalinks
DELETE FROM `sys_permalinks` WHERE `check`='bx_profile_customize_permalinks';

-- delete settings
DELETE FROM `sys_options` WHERE `Name` IN ('bx_profile_customize_permalinks', 'bx_profile_customize_enable');

-- delete action
DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Eval` LIKE '%bx_profile_customize%';

-- delete from admin-menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_profile_customize';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_profile_customize';

