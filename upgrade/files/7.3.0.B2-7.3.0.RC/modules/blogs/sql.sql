
UPDATE `sys_page_compose_pages` SET `Title` = 'Blogs View Post' WHERE `Name` = 'bx_blogs';
UPDATE `sys_page_compose_pages` SET `Title` = 'Blogs Home' WHERE `Name` = 'bx_blogs_home';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_blogs' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_blogs' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_blogs_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_blogs_home' AND `Column` != 0 AND @iFirstColumn = 0;

