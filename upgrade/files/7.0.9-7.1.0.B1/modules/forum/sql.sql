

-- alter tables

ALTER TABLE `bx_forum_post` ADD FULLTEXT KEY `post_text` (`post_text`);

ALTER TABLE `bx_forum_topic` ADD FULLTEXT KEY `topic_title` (`topic_title`);


-- stat site

DELETE FROM `sys_stat_site` WHERE `Name` = 'tps';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'tps', 'bx_forum_discussions', 'forum/', 'SELECT IF( NOT ISNULL( SUM(`forum_topics`)), SUM(`forum_posts`), 0) AS `Num` FROM `bx_forum`', '', '', 'comments', @iStatSiteOrder);


-- page builder 

DELETE FROM `sys_page_compose_pages` WHERE `Name` IN ('forums_index', 'forums_home');

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'member', 'profile') AND `Desc` IN ('Forum Posts', 'Last posts of a member in the forum');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('FullIndex') AND `Page` = 'forums_index';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Search', 'ShortIndex', 'RecentTopics') AND `Page` = 'forums_home';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'forums_index' OR `Page` = 'forums_home';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Forum Posts', '_bx_forum_forum_posts', 0, 0, 'RSS', '{SiteUrl}forum/?action=rss_all#4', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Forum Posts', '_bx_forum_forum_posts', 0, 0, 'RSS', '{SiteUrl}forum/?action=rss_user&user={NickName}#4', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Last posts of a member in the forum', '_bx_forum_forum_posts', 0, 0, 'RSS', '{SiteUrl}forum/?action=rss_user&user={NickName}#4', 1, 71.9, 'non,memb', 0);

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('forums_index', 'Forums Index', @iMaxOrder);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('forums_index', '1140px', 'Full Index', '_bx_forums_index', 1, 0, 'FullIndex', '', 0, 100, 'non,memb', 0);

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('forums_home', 'Forums Home', @iMaxOrder);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('forums_home', '1140px', 'Quick Search', '_bx_forums_quick_search', 2, 0, 'Search', '', 1, 28.1, 'non,memb', 0),
('forums_home', '1140px', 'Short Index', '_bx_forums_index', 2, 1, 'ShortIndex', '', 1, 28.1, 'non,memb', 0),
('forums_home', '1140px', 'Recent Topics', '_bx_forums_recent_topics', 1, 0, 'RecentTopics', '', 0, 71.9, 'non,memb', 0);


-- actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_forum_title';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{evalResult}', 'plus', 'javascript:void(0);', 'return f.newTopic(''0'')', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_forums_new_topic'') : '''';', '1', 'bx_forum_title');


-- menu top

SET @iMenuEventsTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Forums');
UPDATE `sys_menu_top` SET `Picture` = 'comments', `Icon` = 'comments' WHERE `ID` = @iMenuEventsTop;
UPDATE `sys_menu_top` SET `Picture` = '', `BQuickLink` = 0 WHERE `Parent` = @iMenuEventsTop;
UPDATE `sys_menu_top` SET `Picture` = 'comments', `Icon` = 'comments', `BQuickLink` = 1 WHERE `Parent` = @iMenuEventsTop AND `Name` = 'My Topics';


-- menu member 

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_forum';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_forum', `Eval` = '$oMemberMenu = bx_instance(''BxDolMemberMenu''); $a = array(''item_img_src'' => ''comments'', ''item_link'' => BX_DOL_URL_ROOT . ''forum/#action=goto&new_topic=0'', ''item_title'' => _t(''_bx_forum_forum_topic'')); return $oMemberMenu->getGetExtraMenuLink($a);', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- menu admin

DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_forum';
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_forum', '_bx_forum', '{siteUrl}forum/?action=goto&manage_forum=1', 'Forum Administration Panel', 'comments', @iMax+1);


-- acl

SET @iLevelNonMember := 1;
SET @iAction := (SELECT `ID` FROM `sys_acl_actions` WHERE `Name` = 'forum search');
DELETE FROM `sys_acl_matrix` WHERE `IDLevel` = @iLevelNonMember AND `IDAction` = @iAction;
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES (@iLevelNonMember, @iAction);


-- alerts

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_forum_profile' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` (`name`, `class`, `file`) VALUES
('bx_forum_profile', 'BxForumProfileResponse', 'modules/boonex/forum/profile_response.php');
SET @iHandlerId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'edit', @iHandlerId),
('profile', 'delete', @iHandlerId);


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` = 'bx_forum_notifier';
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('bx_forum_notifier', 'New Post In: <TopicTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n    <p>Hello <Recipient>,</p> \r\n    <p><a href="<PosterUrl>"><PosterNickName></a> has posted a new reply in "<TopicTitle>" topic:</p> \r\n    <hr> <ReplyText> <hr> \r\n    \r\n <bx_include_auto:_email_footer.html />', 'Notification about new post in flagged topic', 0);


-- sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_forum';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_forum', '_bx_forums_sitemap', '0.8', 'auto', 'BxForumSiteMaps', 'modules/boonex/forum/classes/BxForumSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_forum';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_forum', '_bx_forum_forum_posts', 'bx_forum_post', 'when', '', '', 1, @iMaxOrderCharts);


-- language category

SET @iLangKeysCountInCat = (SELECT COUNT(*) FROM `sys_localization_keys` AS `k` INNER JOIN `sys_localization_categories` AS `c` ON (`c`.`ID` = `k`.`IDCategory`) WHERE `c`.`Name` = 'BoonEx Forum');
DELETE FROM `sys_localization_categories` WHERE `Name` = 'BoonEx Forum' AND 0 = @iLangKeysCountInCat;

UPDATE IGNORE `sys_localization_categories` SET `Name` = 'BoonEx Forum' WHERE `Name` = 'Boonex Orca Forums';



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_forum_menu_browse_forums','_bx_forum_menu_featured_topics','_bx_forum_menu_latest_posts');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_forum_menu_browse_forums','_bx_forum_menu_featured_topics','_bx_forum_menu_latest_posts');
        


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'forum' AND `version` = '1.0.9';

