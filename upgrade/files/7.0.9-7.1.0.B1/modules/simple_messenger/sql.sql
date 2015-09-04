
-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_simple_messenger';


-- page builder

DELETE FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Desc` = 'Messenger Settings';

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('pedit', '1140px', 'Messenger Settings', '_simple_messenger_bcaption_settings', 2, @iMaxOrder, 'PHP', 'return BxDolService::call(''simple_messenger'', ''get_settings'');', 1, 28.1, 'memb', 0);


-- options

UPDATE `sys_options_cats` SET `name` = 'Simple Messenger' WHERE `name` = 'Simple messenger';


-- menu admin

DELETE FROM `sys_menu_admin` WHERE `name` = 'Simple messenger';

SET @iKategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Simple Messenger');
SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
INSERT INTO 
    `sys_menu_admin` 
SET
    `name`           = 'Simple messenger',
    `title`          = '_simple_messenger_title', 
    `url`            = CONCAT('{siteAdminUrl}settings.php?cat=', @iKategId), 
    `description`    = 'Managing the simple messenger settings', 
    `icon`           = 'comments-alt',
    `parent_id`      = 2,
    `order`         = @iOrder+1;


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_simple_messenger';

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_simple_messenger', '_simple_messenger_chart', 'bx_simple_messenger_messages', '', 'Date', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_simple_messenger_privacy_page');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_simple_messenger_privacy_page');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'simple_messenger' AND `version` = '1.0.9';

