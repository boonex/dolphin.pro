DELETE FROM `sys_menu_admin` WHERE `name`='bx_pageac';

DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_pageac';

DELETE FROM `sys_options` WHERE `Name` = 'permalinks_module_pageac';

SET @iHandlerID = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_pageac' LIMIT 1);
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandlerID LIMIT 1;
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandlerID LIMIT 1;

DROP TABLE IF EXISTS `[db_prefix]rules`;
DROP TABLE IF EXISTS `[db_prefix]top_menu_visibility`;
DROP TABLE IF EXISTS `[db_prefix]member_menu_visibility`;
DROP TABLE IF EXISTS `[db_prefix]page_blocks_visibility`;
