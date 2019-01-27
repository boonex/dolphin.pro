
CREATE TABLE `bx_forum` (
  `forum_id` int(10) unsigned NOT NULL auto_increment,
  `forum_uri` varchar(255) NOT NULL default '',
  `cat_id` int(11) NOT NULL default '0',
  `forum_title` varchar(255) default NULL,
  `forum_desc` varchar(255) NOT NULL default '',
  `forum_posts` int(11) NOT NULL default '0',
  `forum_topics` int(11) NOT NULL default '0',
  `forum_last` int(11) NOT NULL default '0',
  `forum_type` enum('public','private') NOT NULL default 'public',
  `forum_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_uri` (`forum_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_cat` (
  `cat_id` int(10) unsigned NOT NULL auto_increment,
  `cat_uri` varchar(255) NOT NULL default '',
  `cat_name` varchar(255) default NULL,
  `cat_icon` varchar(32) NOT NULL default '',
  `cat_order` float NOT NULL default '0',
  `cat_expanded` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`cat_id`),
  KEY `cat_order` (`cat_order`),
  KEY `cat_uri` (`cat_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_flag` (
  `user` varchar(32) NOT NULL default '',
  `topic_id` int(11) NOT NULL default '0',
  `when` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_post` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `topic_id` int(11) NOT NULL default '0',
  `forum_id` int(11) NOT NULL default '0',
  `user` varchar(32) NOT NULL default '0',
  `post_text` mediumtext NOT NULL,
  `when` int(11) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `reports` int(11) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `user` (`user`),
  KEY `when` (`when`),
  FULLTEXT KEY `post_text` (`post_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_topic` (
  `topic_id` int(10) unsigned NOT NULL auto_increment,
  `topic_uri` varchar(255) NOT NULL default '',
  `forum_id` int(11) NOT NULL default '0',
  `topic_title` varchar(255) NOT NULL default '',
  `when` int(11) NOT NULL default '0',
  `topic_posts` int(11) NOT NULL default '0',
  `first_post_user` varchar(32) NOT NULL default '0',
  `first_post_when` int(11) NOT NULL default '0',
  `last_post_user` varchar(32) NOT NULL default '',
  `last_post_when` int(11) NOT NULL default '0',
  `topic_sticky` int(11) NOT NULL default '0',
  `topic_locked` tinyint(4) NOT NULL default '0',
  `topic_hidden` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `forum_id_2` (`forum_id`,`when`),
  KEY `topic_uri` (`topic_uri`),
  FULLTEXT KEY `topic_title` (`topic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_user` (
  `user_name` varchar(32) NOT NULL default '',
  `user_pwd` varchar(32) NOT NULL default '',
  `user_email` varchar(128) NOT NULL default '',
  `user_join_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_name`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_user_activity` (
  `user` varchar(32) NOT NULL default '',
  `act_current` int(11) NOT NULL default '0',
  `act_last` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_user_stat` (
  `user` varchar(32) NOT NULL default '',
  `posts` int(11) NOT NULL default '0',
  `user_last_post` int(11) NOT NULL default '0',
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_vote` (
  `user_name` varchar(32) NOT NULL default '',
  `post_id` int(11) NOT NULL default '0',
  `vote_when` int(11) NOT NULL default '0',
  `vote_point` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_name`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_actions_log` (
  `user_name` varchar(32) NOT NULL default '',
  `id` int(11) NOT NULL default '0',
  `action_name` varchar(32) NOT NULL default '',
  `action_when` int(11) NOT NULL default '0',
  KEY `action_when` (`action_when`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bx_forum_attachments` (
  `att_hash` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `att_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `att_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `att_when` int(11) NOT NULL,
  `att_size` int(11) NOT NULL,
  `att_downloads` int(11) NOT NULL,
  PRIMARY KEY (`att_hash`),
  KEY `post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bx_forum_signatures` (
  `user` varchar(32) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `when` int(11) NOT NULL,
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `bx_forum` (`forum_id`, `forum_uri`, `cat_id`, `forum_title`, `forum_desc`, `forum_posts`, `forum_topics`, `forum_last`, `forum_type`, `forum_order`) VALUES
(1, 'General-discussions', 1, 'General discussions', 'General discussions', 0, 0, 0, 'public', 2);

INSERT INTO `bx_forum_cat` (`cat_id`, `cat_uri`, `cat_name`, `cat_icon`, `cat_order`, `cat_expanded`) VALUES
(1, 'General', 'General', '', 2, 0);


-- stat member
INSERT INTO `sys_stat_member` VALUES('mop', 'SELECT COUNT(*) FROM `bx_forum_post` WHERE `user` = ''__member_nick__''');
INSERT INTO `sys_stat_member` VALUES('mot', 'SELECT COUNT(*) FROM `bx_forum_topic` WHERE `first_post_user` = ''__member_nick__''');

-- stat site
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'tps', 'bx_forum_discussions', 'forum/', 'SELECT IF( NOT ISNULL( SUM(`forum_topics`)), SUM(`forum_posts`), 0) AS `Num` FROM `bx_forum`', '', '', 'comments', @iStatSiteOrder);

-- pages
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
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{evalResult}', 'plus', 'javascript:void(0);', 'return f.newTopic(''0'')', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_forums_new_topic'') : '''';', '1', 'bx_forum_title');

-- menu top
SET @iMaxMenuOrder := (SELECT `Order` + 1 FROM `sys_menu_top` WHERE `Parent` = 0 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 0, 'Forums', '_bx_forums', 'forum/', @iMaxMenuOrder, 'non,memb', '', '', '', 1, 1, 1, 'top', 'comments', 'comments', 1, '');
SET @iId = (SELECT LAST_INSERT_ID());

INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, @iId, 'Forums Home', '_bx_forum_menu_home', 'forum/', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Forums Index', '_bx_forum_menu_forum_index', 'forum/?action=goto&index=1', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Flagged Topics', '_bx_forum_menu_flagged_topics', 'forum/?action=goto&my_flags=1', 5, 'memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'My Topics', '_bx_forum_menu_my_topics', 'forum/?action=goto&my_threads=1', 6, 'memb', '', '', '', 1, 1, 1, 'custom', 'comments', 'comments', 1, ''),
(NULL, @iId, 'Spy', '_bx_forum_menu_spy', 'forum/?action=live_tracker', 7, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Forum Search', '_bx_forum_menu_search', 'forum/?action=goto&search=1', 8, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Manage Forum', '_bx_forum_menu_manage_forum', 'forum/?action=goto&manage_forum=1', 20, 'memb', '', '', 'return isAdmin();', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Reported Posts', '_bx_forum_menu_reported_posts', 'forum/?action=goto&reported_posts=1', 22, 'memb', '', '', 'return isAdmin();', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Hidden Posts', '_bx_forum_menu_hidden_posts', 'forum/?action=goto&hidden_posts=1', 24, 'memb', '', '', 'return isAdmin();', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iId, 'Hidden Topics', '_bx_forum_menu_hidden_topics', 'forum/?action=goto&hidden_topics=1', 26, 'memb', '', '', 'return isAdmin();', 1, 1, 1, 'custom', '', '', 0, '');

-- member menu
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_forum', `Eval` = '$oMemberMenu = bx_instance(''BxDolMemberMenu''); $a = array(''item_img_src'' => ''comments'', ''item_link'' => BX_DOL_URL_ROOT . ''forum/#action=goto&new_topic=0'', ''item_title'' => _t(''_bx_forum_forum_topic'')); return $oMemberMenu->getGetExtraMenuLink($a);', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);

-- menu admin
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_forum', '_bx_forum', '{siteUrl}forum/?action=goto&manage_forum=1', 'Forum Administration Panel', 'comments', @iMax+1);

-- stat member
INSERT INTO `sys_account_custom_stat_elements` VALUES (NULL, '_bx_forums', '__mop__ (<a href="__site_url__forum/">__l_add__</a>)');

-- acl
SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum public read', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum public post', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum private read', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum private post', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum search', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum files download', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum edit all', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum delete all', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum make sticky', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum del topics', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum move topics', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum hide topics', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum unhide topics', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum hide posts', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'forum unhide posts', NULL);

-- alerts
INSERT INTO `sys_alerts_handlers` (`name`, `class`, `file`) VALUES
('bx_forum_profile', 'BxForumProfileResponse', 'modules/boonex/forum/profile_response.php');
SET @iHandlerId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'edit', @iHandlerId),
('profile', 'delete', @iHandlerId);

-- email templates
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('bx_forum_notifier', 'New Post In: <TopicTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n    <p>Hello <Recipient>,</p> \r\n    <p><a href="<PosterUrl>"><PosterNickName></a> has posted a new reply in "<TopicTitle>" topic:</p> \r\n    <hr> <ReplyText> <hr> \r\n    \r\n <bx_include_auto:_email_footer.html />', 'Notification about new post in flagged topic', 0);

-- sitemap
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_forum', '_bx_forums_sitemap', '0.8', 'auto', 'BxForumSiteMaps', 'modules/boonex/forum/classes/BxForumSiteMaps.php', @iMaxOrderSiteMaps, 1);

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_forum', '_bx_forum_forum_posts', 'bx_forum_post', 'when', '', '', 1, @iMaxOrderCharts);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_forum', '_sys_module_forum', 'BxForumExport', 'modules/boonex/forum/classes/BxForumExport.php', @iMaxOrderExports, 1);

