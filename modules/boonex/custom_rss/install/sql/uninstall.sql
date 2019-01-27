-- tables
DROP TABLE IF EXISTS `[db_prefix]_main`;

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'Custom RSS';

-- settings
SET @iCategoryID := (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Custom RSS' LIMIT 1);
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategoryID;
DELETE FROM `sys_options` WHERE `kateg` = @iCategoryID;

-- page compose pages
DELETE FROM `sys_page_compose` WHERE `Caption`='_crss_Custom_Feeds' AND `Func`='PHP';

-- site stats
DELETE FROM `sys_stat_site` WHERE `Name`='crss';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_crss';

