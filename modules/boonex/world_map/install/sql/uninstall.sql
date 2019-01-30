
-- drop tables
DROP TABLE IF EXISTS `[db_prefix]parts`;
DROP TABLE IF EXISTS `[db_prefix]locations`;

-- page compose pages
DELETE FROM `sys_page_compose_pages` WHERE `Name` IN('bx_wmap', 'bx_wmap_edit');
DELETE FROM `sys_page_compose` WHERE `Page` IN('bx_wmap', 'bx_wmap_edit');
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Map';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Location';

-- permalinks
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=wmap/';

-- settings
DELETE FROM `sys_options` WHERE `Name` = 'bx_wmap_permalinks';

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'World Map General' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'World Map Hidden' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'World Map Homepage' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'World Map Separate' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;


DELETE FROM `sys_options_cats` WHERE `name` LIKE 'World Map Home: %';
DELETE FROM `sys_options_cats` WHERE `name` LIKE 'World Map Entry: %';
DELETE FROM `sys_options_cats` WHERE `name` LIKE 'World Map Edit Location: %';
DELETE FROM `sys_options` WHERE `Name` LIKE 'bx_wmap_%';


-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_wmap';

-- top menu
DELETE FROM `sys_menu_top` WHERE `Parent` = 138 AND `Name` = 'World Map';

-- mobile
DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_wmap';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_wmap';

