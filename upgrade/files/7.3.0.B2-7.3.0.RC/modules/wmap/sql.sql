
UPDATE `sys_page_compose_pages` SET `Title` = 'World Map' WHERE `Name` = 'bx_wmap';
UPDATE `sys_page_compose_pages` SET `Title` = 'World Map Edit' WHERE `Name` = 'bx_wmap_edit';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_wmap' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_wmap' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_wmap_edit' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_wmap_edit' AND `Column` != 0 AND @iFirstColumn = 0;

