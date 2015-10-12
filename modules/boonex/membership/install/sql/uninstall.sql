SET @sModuleName = 'Membership';


-- options
SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name`=@sModuleName LIMIT 1);
DELETE FROM `sys_options_cats` WHERE `name`=@sModuleName LIMIT 1;
DELETE FROM `sys_options` WHERE `kateg`=@iCategoryId OR `Name`='permalinks_module_membership';


-- menus
DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_membership';

DELETE FROM `sys_menu_top` WHERE `Name`='My Membership';

DELETE FROM `sys_menu_admin` WHERE `name`=@sModuleName;


-- pages and blocks
DELETE FROM `sys_page_compose_pages` WHERE `Name` IN ('bx_mbp_my_membership', 'bx_mbp_join');
DELETE FROM `sys_page_compose` WHERE `Page` IN ('bx_mbp_my_membership', 'bx_mbp_join');


-- cron
DELETE FROM `sys_cron_jobs` WHERE `name`=@sModuleName;


-- alerts
SELECT @iHandlerId:=`id` FROM `sys_alerts_handlers` WHERE `name`=@sModuleName LIMIT 1;
DELETE FROM `sys_alerts_handlers` WHERE `name`=@sModuleName LIMIT 1;
DELETE FROM `sys_alerts` WHERE `handler_id`=@iHandlerId;