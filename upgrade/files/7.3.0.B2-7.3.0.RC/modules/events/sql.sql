
UPDATE `sys_page_compose_pages` SET `Title` = 'Events View Event' WHERE `Name` = 'bx_events_view';
UPDATE `sys_page_compose_pages` SET `Title` = 'Events Home' WHERE `Name` = 'bx_events_main';
UPDATE `sys_page_compose_pages` SET `Title` = 'Events User' WHERE `Name` = 'bx_events_my';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_events_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_events_main' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_events_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_events_my' AND `Column` != 0 AND @iFirstColumn = 0;

