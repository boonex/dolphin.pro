
UPDATE `sys_page_compose_pages` SET `Title` = 'Polls View Poll' WHERE `Name` = 'show_poll_info';
UPDATE `sys_page_compose_pages` SET `Title` = 'Polls Home' WHERE `Name` = 'poll_home';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'poll_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'poll_home' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'show_poll_info' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'show_poll_info' AND `Column` != 0 AND @iFirstColumn = 0;

