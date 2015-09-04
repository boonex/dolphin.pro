-- tables
DROP TABLE IF EXISTS `[db_prefix]units`;

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'Quotes';

-- page blocks
DELETE FROM `sys_page_compose` WHERE `Caption` IN ('_bx_quotes_bcaption_quotes');

-- site stats
DELETE FROM `sys_stat_site` WHERE `Name`='qts';