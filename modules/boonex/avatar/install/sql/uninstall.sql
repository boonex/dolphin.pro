
-- tables
DROP TABLE IF EXISTS `[db_prefix]images`;
UPDATE `Profiles` SET `Avatar` = 0;

-- compose pages
DELETE FROM `sys_page_compose_pages` WHERE `Name` IN('bx_avatar_main');
DELETE FROM `sys_page_compose` WHERE `Page` IN('bx_avatar_main');
DELETE FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Desc` = 'Manage Avatars';

-- system objects
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=avatar/';

-- member menu
DELETE FROM `sys_menu_top` WHERE `Parent` = 118 AND `Name` = 'Avatar' AND `Link` = 'modules/?r=avatar/';

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_avatar';

-- actions menu
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_photos' AND `Caption` = '{TitleAvatar}';

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Avatar' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_avatar_permalinks';

-- membership levels
DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` IN('avatar upload', 'avatar edit any', 'avatar delete any');
DELETE FROM `sys_acl_actions` WHERE `Name` IN('avatar upload', 'avatar edit any', 'avatar delete any');

-- alerts
SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_avatar' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_avatar';

