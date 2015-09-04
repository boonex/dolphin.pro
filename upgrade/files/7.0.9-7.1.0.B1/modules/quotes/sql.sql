

-- injections

DELETE FROM `sys_injections` WHERE `name` = 'quotes_injection';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'italic' WHERE `name` = 'Quotes';


-- page blocks

DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Daily Quotes';

SET @iPageColumnOrder := (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'index' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Daily Quotes', '_bx_quotes_bcaption_quotes', 2, @iPageColumnOrder, 'PHP', 'return BxDolService::call(''quotes'', ''get_quote_unit'');', 1, 28.1, 'non,memb', 0);


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'qts';

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'qts', 'bx_quotes_ss', '', 'SELECT COUNT(`ID`) FROM `[db_prefix]units` WHERE 1', '', '', 'italic', @iStatSiteOrder);



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'quotes' AND `version` = '1.0.9';

