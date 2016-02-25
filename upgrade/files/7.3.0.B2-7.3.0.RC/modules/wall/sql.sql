
UPDATE `bx_wall_handlers` SET `timeline` = 0 WHERE `alert_unit` = 'comment' AND `alert_action` = 'add';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'wall' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'wall' AND `Column` != 0 AND @iFirstColumn = 0;

