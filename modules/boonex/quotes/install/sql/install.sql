-- create tables
CREATE TABLE IF NOT EXISTS `[db_prefix]units` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Text` mediumtext NOT NULL,
  `Author` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


INSERT INTO `[db_prefix]units` VALUES(1, 'We have all known the long loneliness and we have learned that the only solution is love and that love comes with community.', 'Dorothy Day');
INSERT INTO `[db_prefix]units` VALUES(2, 'For a community to be whole and healthy, it must be based on people''s love and concern for each other.', 'Millard Fuller');
INSERT INTO `[db_prefix]units` VALUES(3, 'We were born to unite with our fellow men, and to join in community with the human race.', 'Cicero');

-- admin menu
SET @iExtOrd = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT INTO `sys_menu_admin` (`id`, `parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(NULL, 2, 'Quotes', '_bx_Quotes', '{siteUrl}modules/?r=quotes/administration/', 'Quotes administration', 'italic', '', '', @iExtOrd+1);

-- page blocks
SET @iPageColumnOrder := (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'index' AND `Column` = 3 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Daily Quotes', '_bx_quotes_bcaption_quotes', 3, IFNULL(@iPageColumnOrder, 0), 'PHP', 'return BxDolService::call(''quotes'', ''get_quote_unit'');', 1, 28.1, 'non,memb', 0);

-- site stats
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'qts', 'bx_quotes_ss', '', 'SELECT COUNT(`ID`) FROM `[db_prefix]units` WHERE 1', '', '', 'italic', @iStatSiteOrder);
