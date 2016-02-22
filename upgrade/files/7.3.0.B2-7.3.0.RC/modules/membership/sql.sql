
UPDATE `sys_page_compose_pages` SET `Title` = 'Membership My' WHERE `Name` = 'bx_mbp_my_membership';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_mbp_join' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_mbp_join' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_mbp_my_membership' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_mbp_my_membership' AND `Column` != 0 AND @iFirstColumn = 0;

