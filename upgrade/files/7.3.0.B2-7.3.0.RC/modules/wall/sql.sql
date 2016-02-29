
ALTER TABLE  `bx_wall_events` CHANGE  `date`  `date` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0';

UPDATE `bx_wall_handlers` SET `timeline` = 0 WHERE `alert_unit` = 'comment' AND `alert_action` = 'add';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'wall' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'wall' AND `Column` != 0 AND @iFirstColumn = 0;


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_wall_hide_n_comments','_wall_show_n_comments');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_wall_hide_n_comments','_wall_show_n_comments');

