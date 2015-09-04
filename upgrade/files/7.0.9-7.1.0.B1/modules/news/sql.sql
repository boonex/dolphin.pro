

-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'bullhorn' WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'News';
UPDATE `sys_menu_top` SET `Picture` = 'bullhorn', `Name` = 'NewsView' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = '[db_prefix]_view';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'News';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'bullhorn' WHERE `name` = 'bx_news';


-- options

DELETE FROM `sys_options` WHERE `Name` = 'news_snippet_length';


-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'member') AND `Desc` IN ('Show list of featured news', 'Show list of latest news');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Content', 'Comment', 'Action', 'Vote', 'SocialSharing') AND `Page` = 'news_single';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Featured', 'Latest', 'Calendar', 'Categories', 'Tags') AND `Page` = 'news_home';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'news_single' OR `Page` = 'news_home';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Show list of featured news', '_news_bcaption_featured', 0, 0, 'PHP', 'return BxDolService::call(\'news\', \'featured_block_index\', array(0, 0, false));', 1, 28.1, 'non,memb', 0),
('index', '1140px', 'Show list of latest news', '_news_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'news\', \'archive_block_index\', array(0, 0, false));', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Show list of featured news', '_news_bcaption_featured', 0, 0, 'PHP', 'return BxDolService::call(\'news\', \'featured_block_member\', array(0, 0, false));', 1, 71.9, 'memb', 0),
('member', '1140px', 'Show list of latest news', '_news_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'news\', \'archive_block_member\', array(0, 0, false));', 1, 71.9, 'memb', 0),
('news_single', '1140px', 'News main content', '_news_bcaption_view_main', 1, 0, 'Content', '', 1, 71.9, 'non,memb', 0),
('news_single', '1140px', 'News comments', '_news_bcaption_view_comment', 1, 1, 'Comment', '', 1, 71.9, 'non,memb', 0),
('news_single', '1140px', 'News actions', '_news_bcaption_view_action', 2, 0, 'Action', '', 1, 28.1, 'non,memb', 0),
('news_single', '1140px', 'News rating', '_news_bcaption_view_vote', 2, 1, 'Vote', '', 1, 28.1, 'non,memb', 0),
('news_single', '1140px', 'Social sharing', '_sys_block_title_social_sharing', 2, 2, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('news_home', '1140px', 'News latest', '_news_bcaption_latest', 1, 1, 'Latest', '', 1, 71.9, 'non,memb', 0),
('news_home', '1140px', 'News categories', '_news_bcaption_categories', 0, 0, 'Categories', '', 1, 28.1, 'non,memb', 0),
('news_home', '1140px', 'News tags', '_news_bcaption_tags', 0, 0, 'Tags', '', 1, 28.1, 'non,memb', 0),
('news_home', '1140px', 'News calendar', '_news_bcaption_calendar', 2, 1, 'Calendar', '', 1, 28.1, 'non,memb', 0),
('news_home', '1140px', 'News featured', '_news_bcaption_featured', 2, 2, 'Featured', '', 1, 28.1, 'non,memb', 0);


-- objects: actions 

UPDATE `sys_objects_actions` SET `Icon` = 'paper-clip' WHERE `Caption` = '{sbs_news_title}' AND `Type` = 'bx_news';
UPDATE `sys_objects_actions` SET `Icon` = 'remove' WHERE `Caption` = '{del_news_title}' AND `Type` = 'bx_news';


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_news' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_news' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsNewsComments', 't_sbsNewsRates');
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsNewsComments', 'New Comments To A News Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<br /><p>The <a href="<ViewLink>">news post you subscribed to got new comments!</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to file', 0);


-- menu mobile

UPDATE `sys_menu_mobile` SET `action_data` = '{site_url}modules/?r=news/mobile_latest_entries/' WHERE `type` = 'bx_news';


-- site stats

DELETE FROM `sys_stat_site` WHERE `Name` = 'news';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'news', 'news_ss', 'modules/?r=news/archive/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''0''', 'modules/?r=news/admin/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''1''', 'bullhorn', @iStatSiteOrder);


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_news';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_news', '_news_sitemap', '0.8', 'auto', 'BxNewsSiteMaps', 'modules/boonex/news/classes/BxNewsSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_news';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_news', '_news_chart', 'bx_news_entries', 'when', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_news_action_error_access_denied','_news_bcaption_all_categories','_news_ext_menu_item','_news_hot_not_top_menu_sitem','_news_latest_top_menu_sitem','_news_pcaption_all','_news_pcaption_view','_news_sbs_rate','_news_site_top_menu_sitem','_news_txt_categories','_news_txt_comments','_news_txt_tags');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_news_action_error_access_denied','_news_bcaption_all_categories','_news_ext_menu_item','_news_hot_not_top_menu_sitem','_news_latest_top_menu_sitem','_news_pcaption_all','_news_pcaption_view','_news_sbs_rate','_news_site_top_menu_sitem','_news_txt_categories','_news_txt_comments','_news_txt_tags');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'news' AND `version` = '1.0.9';

