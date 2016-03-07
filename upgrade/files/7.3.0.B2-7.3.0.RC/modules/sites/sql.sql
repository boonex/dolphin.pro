
UPDATE `sys_page_compose_pages` SET `Title` = 'Sites Home' WHERE `Name` = 'bx_sites_main';
UPDATE `sys_page_compose_pages` SET `Title` = 'Sites User' WHERE `Name` = 'bx_sites_profile';
UPDATE `sys_page_compose_pages` SET `Title` = 'Sites View Page' WHERE `Name` = 'bx_sites_view';
UPDATE `sys_page_compose_pages` SET `Title` = 'Sites Rate' WHERE `Name` = 'bx_sites_hon';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sites_main' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sites_main' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sites_profile' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sites_profile' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sites_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sites_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sites_hon' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sites_hon' AND `Column` != 0 AND @iFirstColumn = 0;

