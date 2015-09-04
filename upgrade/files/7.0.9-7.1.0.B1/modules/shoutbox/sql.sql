
-- menu admin

DELETE FROM `sys_menu_admin` WHERE `name` = 'Shoutbox';

SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
INSERT INTO 
    `sys_menu_admin` 
SET
    `name`          = 'Shoutbox',
    `title`         = '_bx_shoutbox', 
    `url`           = '{siteUrl}modules/?r=shoutbox/administration/',
    `description`   = 'Some shoutbox''s settings',
    `icon`          = 'comment',
    `parent_id`     = 2,
    `order`         = @iOrder+1;


-- chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_shoutbox';

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_shoutbox', '_bx_shoutbox_chart', 'bx_shoutbox_messages', '', 'Date', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_shoutbox_message_blocked','_bx_shoutbox_message_here','_bx_shoutbox_send');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_shoutbox_message_blocked','_bx_shoutbox_message_here','_bx_shoutbox_send');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'shoutbox' AND `version` = '1.0.9';

