

-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'file' WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Articles';
UPDATE `sys_menu_top` SET `Picture` = 'file', `Name` = 'ArticlesView' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = '[db_prefix]_view';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'Articles';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'file' WHERE `name` = 'bx_articles';


-- options

DELETE FROM `sys_options` WHERE `Name` = 'articles_snippet_length';


-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'member') AND `Desc` IN ('Show list of featured articles', 'Show list of latest articles');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Content', 'Comment', 'Action', 'Vote', 'SocialSharing') AND `Page` = 'articles_single';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Featured', 'Latest', 'Calendar', 'Categories', 'Tags') AND `Page` = 'articles_home';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'articles_single' OR `Page` = 'articles_home';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Show list of featured articles', '_articles_bcaption_featured', 0, 0, 'PHP', 'return BxDolService::call(\'articles\', \'featured_block_index\', array(0, 0, false));', 1, 71.9, 'non,memb', 0),
('index', '1140px', 'Show list of latest articles', '_articles_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'articles\', \'archive_block_index\', array(0, 0, false));', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Show list of featured articles', '_articles_bcaption_featured', 0, 0, 'PHP', 'return BxDolService::call(\'articles\', \'featured_block_member\', array(0, 0, false));', 1, 71.9, 'memb', 0),
('member', '1140px', 'Show list of latest articles', '_articles_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'articles\', \'archive_block_member\', array(0, 0, false));', 1, 71.9, 'memb', 0),
('articles_single', '1140px', 'Articles main content', '_articles_bcaption_view_main', 1, 0, 'Content', '', 1, 71.9, 'non,memb', 0),
('articles_single', '1140px', 'Articles comments', '_articles_bcaption_view_comment', 1, 1, 'Comment', '', 1, 71.9, 'non,memb', 0),
('articles_single', '1140px', 'Articles actions', '_articles_bcaption_view_action', 2, 0, 'Action', '', 1, 28.1, 'non,memb', 0),
('articles_single', '1140px', 'Articles rating', '_articles_bcaption_view_vote', 2, 1, 'Vote', '', 1, 28.1, 'non,memb', 0),
('articles_single', '1140px', 'Social sharing', '_sys_block_title_social_sharing', 2, 2, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('articles_home', '1140px', 'Articles featured', '_articles_bcaption_featured', 0, 0, 'Featured', '', 1, 71.9, 'non,memb', 0),
('articles_home', '1140px', 'Articles latest', '_articles_bcaption_latest', 1, 1, 'Latest', '', 1, 71.9, 'non,memb', 0),
('articles_home', '1140px', 'Articles calendar', '_articles_bcaption_calendar', 0, 0, 'Calendar', '', 1, 28.1, 'non,memb', 0),
('articles_home', '1140px', 'Articles categories', '_articles_bcaption_categories', 2, 1, 'Categories', '', 1, 28.1, 'non,memb', 0),
('articles_home', '1140px', 'Articles tags', '_articles_bcaption_tags', 2, 2, 'Tags', '', 1, 28.1, 'non,memb', 0);


-- objects: actions 

UPDATE `sys_objects_actions` SET `Icon` = 'paper-clip' WHERE `Caption` = '{sbs_articles_title}' AND `Type` = 'bx_articles';
UPDATE `sys_objects_actions` SET `Icon` = 'remove' WHERE `Caption` = '{del_articles_title}' AND `Type` = 'bx_articles';


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_articles' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_articles' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsArticlesComments', 't_sbsArticlesRates');
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsArticlesComments', 'New Comments To An Article', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">article you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to article', 0);


-- menu mobile
DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_articles';
SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_articles', 'homepage', '_articles_bcaption_all', '{site_url}modules/boonex/articles/templates/base/images/icons/mobile_icon.png', 100, '{site_url}modules/?r=articles/mobile_latest_entries/', '', '', @iMaxOrderHomepage, 1);


-- site stats
DELETE FROM `sys_stat_site` WHERE `Name` = 'arl';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'arl', 'articles_ss', 'modules/?r=articles/archive/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''0''', 'modules/?r=articles/admin/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''1''', 'file', @iStatSiteOrder);


-- objects: sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_articles';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_articles', '_articles_sitemap', '0.8', 'auto', 'BxArlSiteMaps', 'modules/boonex/articles/classes/BxArlSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_articles';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_articles', '_articles_chart', 'bx_arl_entries', 'when', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_articles_action_error_access_denied','_articles_bcaption_all_categories','_articles_ext_menu_item','_articles_hot_not_top_menu_sitem','_articles_latest_top_menu_sitem','_articles_pcaption_all','_articles_pcaption_view','_articles_sbs_rate','_articles_site_top_menu_sitem','_articles_txt_categories','_articles_txt_comments','_articles_txt_tags');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_articles_action_error_access_denied','_articles_bcaption_all_categories','_articles_ext_menu_item','_articles_hot_not_top_menu_sitem','_articles_latest_top_menu_sitem','_articles_pcaption_all','_articles_pcaption_view','_articles_sbs_rate','_articles_site_top_menu_sitem','_articles_txt_categories','_articles_txt_comments','_articles_txt_tags');
        


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'articles' AND `version` = '1.0.9';

