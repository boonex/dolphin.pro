
UPDATE `sys_page_compose_pages` SET `Title` = 'Search Google' WHERE `Name` = 'bx_gsearch';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_gsearch' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_gsearch' AND `Column` != 0 AND @iFirstColumn = 0;

