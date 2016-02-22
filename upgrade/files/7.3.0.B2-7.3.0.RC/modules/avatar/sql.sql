
SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_avatar_main' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_avatar_main' AND `Column` != 0 AND @iFirstColumn = 0;

