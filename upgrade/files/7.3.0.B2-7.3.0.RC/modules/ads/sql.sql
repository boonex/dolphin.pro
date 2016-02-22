
SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'ads' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'ads' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'ads_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'ads_home' AND `Column` != 0 AND @iFirstColumn = 0;

