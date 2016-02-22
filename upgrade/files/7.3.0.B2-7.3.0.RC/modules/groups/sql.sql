
UPDATE `sys_page_compose_pages` SET `Title` = 'Groups View Group' WHERE `Name` = 'bx_groups_view';
UPDATE `sys_page_compose_pages` SET `Title` = 'Groups User' WHERE `Name` = 'bx_groups_my';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_groups_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_groups_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_groups_main' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_groups_main' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_groups_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_groups_my' AND `Column` != 0 AND @iFirstColumn = 0;

