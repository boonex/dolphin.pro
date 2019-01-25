-- create tables
CREATE TABLE `[db_prefix]_posts` (
  `PostID` int(11) unsigned NOT NULL auto_increment,
  `PostCaption` varchar(255) NOT NULL default '',
  `PostUri` varchar(255) NOT NULL default '',
  `PostText` text NOT NULL,
  `PostDate` int(11) unsigned default NULL,
  `PostStatus` enum('approval','disapproval') NOT NULL default 'disapproval',
  `PostPhoto` varchar(64) default NULL,
  `Tags` varchar(255) NOT NULL default '',
  `Featured` tinyint(1) NOT NULL default '0',
  `Views` int(11) unsigned NOT NULL,
  `Rate` float NOT NULL,
  `RateCount` int(11) unsigned NOT NULL,
  `CommentsCount` int(11) unsigned NOT NULL,
  `OwnerID` int(11) unsigned NOT NULL,
  `Categories` varchar(255) NOT NULL default '',
  `allowView` int(11) NOT NULL,
  `allowRate` int(11) NOT NULL,
  `allowComment` int(11) NOT NULL,
  PRIMARY KEY  (`PostID`),
  UNIQUE KEY `PostUri` (`PostUri`),
  KEY `OwnerID` (`OwnerID`),
  FULLTEXT KEY `PostCaption` (`PostCaption`,`PostText`,`Tags`),
  FULLTEXT KEY `ftTags` (`Tags`),
  FULLTEXT KEY `ftCategories` (`Categories`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]_rating` (
  `blogp_id` int(11) unsigned NOT NULL default '0',
  `blogp_rating_count` int(11) unsigned NOT NULL default '0',
  `blogp_rating_sum` int(11) unsigned NOT NULL default '0',
  UNIQUE KEY `med_id` (`blogp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]_voting_track` (
  `blogp_id` int(11) unsigned NOT NULL default '0',
  `blogp_ip` varchar(20) default NULL,
  `blogp_date` datetime default NULL,
  KEY `med_ip` (`blogp_ip`,`blogp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]_main` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `OwnerID` int(11) unsigned NOT NULL default '0',
  `Description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `OwnerID` (`OwnerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]_cmts` (
  `cmt_id` int(11) unsigned NOT NULL auto_increment,
  `cmt_parent_id` int(11) unsigned NOT NULL default '0',
  `cmt_object_id` int(11) unsigned NOT NULL default '0',
  `cmt_author_id` int(11) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL,
  `cmt_mood` tinyint NOT NULL default '0',
  `cmt_rate` int(11) NOT NULL default '0',
  `cmt_rate_count` int(11) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cmt_id`),
  KEY `cmt_object_id` (`cmt_object_id`,`cmt_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]_views_track` (
  `id` int(11) unsigned NOT NULL,
  `viewer` int(11) unsigned NOT NULL,
  `ip` int(11) unsigned NOT NULL,
  `ts` int(11) unsigned NOT NULL,
  KEY `id` (`id`,`viewer`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- PQ statistics
INSERT INTO `sys_account_custom_stat_elements` (`Label`, `Value`) VALUES
('_bx_blog_Blog', '__mbp__ (<a href="__site_url__modules/boonex/blogs/blogs.php?action=my_page&mode=add">__l_add__</a>)');

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` VALUES(NULL, 'Blogs', @iMaxOrder);
SET @iGlCategID = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES
('blogAutoApproval', 'on', @iGlCategID, 'Enable AutoApproval of Blogs', 'checkbox', '', '', 1),
('blog_step', '10', @iGlCategID, 'Number of blog posts on a page', 'digit', '', '', 2),
('max_blogs_on_home', '10', @iGlCategID, 'Number of blog posts on Blogs homepage', 'digit', '', '', 3),
('max_blogs_on_profile', '10', @iGlCategID, 'Number of blog posts on profile homepage', 'digit', '', '', 4),
('max_blogs_on_index', '3', @iGlCategID, 'Number of blog posts on site''s homepage', 'digit', '', '', 5),
('max_blog_preview', '256', @iGlCategID, 'Maximum length of Blog preview', 'digit', '', '', 6),
('bx_blogs_iconsize', '45', @iGlCategID, 'Size of post icons', 'digit', '', '', 7),
('bx_blogs_thumbsize', '110', @iGlCategID, 'Size of post thumbs', 'digit', '', '', 8),
('bx_blogs_bigthumbsize', '240', @iGlCategID, 'Size of post bit thumbs', 'digit', '', '', 9),
('bx_blogs_imagesize', '800', @iGlCategID, 'Size of post full images', 'digit', '', '', 10),
('category_auto_app_bx_blogs', 'on', @iGlCategID, 'Auto-activation for categories after blog posts creation', 'checkbox', '', '', 11),
('permalinks_blogs', 'on', 26, 'Enable friendly blogs permalinks', 'checkbox', '', '', NULL);

-- admin menu
SET @iExtOrd = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT INTO `sys_menu_admin` (`id`, `parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES (NULL, 2, 'Blogs', '_sys_module_blogs', '{siteUrl}modules/boonex/blogs/post_mod_blog.php', 'Site administrators can check the content written in the users'' blog to avoid unwanted or prohibited expressions', 'book', '', '', @iExtOrd+1);

-- categories
INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES
('Baby Blogs', 0, '[db_prefix]', 0, 'active'),
('Blogging for Money', 0, '[db_prefix]', 0, 'active'),
('Books', 0, '[db_prefix]', 0, 'active'),
('City Blogs', 0, '[db_prefix]', 0, 'active'),
('Dating and Personals', 0, '[db_prefix]', 0, 'active'),
('Entertainment Blogs', 0, '[db_prefix]', 0, 'active'),
('Food Blogs', 0, '[db_prefix]', 0, 'active'),
('Games', 0, '[db_prefix]', 0, 'active'),
('Health', 0, '[db_prefix]', 0, 'active'),
('Holidays', 0, '[db_prefix]', 0, 'active'),
('Lifestyle', 0, '[db_prefix]', 0, 'active'),
('Movies', 0, '[db_prefix]', 0, 'active'),
('Music', 0, '[db_prefix]', 0, 'active'),
('Politics', 0, '[db_prefix]', 0, 'active'),
('Tech News', 0, '[db_prefix]', 0, 'active'),
('Videos', 0, '[db_prefix]', 0, 'active');

-- category objects
INSERT INTO `sys_objects_categories` (`ID`, `ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) VALUES
(NULL, 'bx_blogs', 'SELECT `Categories` FROM `bx_blogs_posts` WHERE `PostID` = {iID} AND `PostStatus` = ''approval''', 'permalinks_blogs', 'blogs/category/{tag}', 'modules/boonex/blogs/blogs.php?action=category&uri={tag}', '_bx_blog_Blogs');

-- page compose pages
INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'index', '1140px', 'Recently posted blogs', '_bx_blog_Blogs', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_index_page'');', 1, 71.9, 'non,memb', 0),
(NULL, 'index', '1140px', 'Blogs calendar', '_bx_blog_Calendar', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_calendar_index_page'', array($iBlockID));', 0, 28.1, 'non,memb', 0),
(NULL, 'profile', '1140px', 'Member blog block', '_bx_blog_Blog', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_profile_page'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0);

-- page compose pages 2
SET @iPCPOrder = (SELECT `Order` FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES
('bx_blogs', 'Blogs View Post', @iPCPOrder+1),
('bx_blogs_home', 'Blogs Home', @iPCPOrder+2);

INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'bx_blogs', '1140px', '', '_Title', 1, 0, 'PostView', '', 0, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Comments', 1, 1, 'PostComments', '', 1, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_post_info', 2, 0, 'PostOverview', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Rate', 2, 1, 'PostRate', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Actions', 2, 2, 'PostActions', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_sys_block_title_social_sharing', 2, 3, 'PostSocialSharing', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_Categories', 2, 4, 'PostCategories', '', 0, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Tags', 2, 5, 'PostTags', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_Featured_Posts', 0, 0, 'PostFeature', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Latest_posts', 1, 1, 'Latest', '', 1, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Top_blog', 2, 2, 'Top', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Calendar', 2, 1, 'Calendar', '', 0, 28.1, 'non,memb', 0);

-- PQ statistics
INSERT INTO `sys_stat_member` (`Type`, `SQL`) VALUES
('mbpc', 'SELECT COUNT(*) FROM `[db_prefix]_cmts` INNER JOIN `[db_prefix]_posts` ON `[db_prefix]_posts`.`PostID` = `cmt_object_id` WHERE `[db_prefix]_posts`.`OwnerId` = ''__member_id__'''),
('mbp', 'SELECT COUNT(*) FROM `[db_prefix]_posts` WHERE `[db_prefix]_posts`.`OwnerId` = ''__member_id__''');

-- site stats
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('blg', 'bx_blog_stat', 'modules/boonex/blogs/blogs.php?action=all_posts', 'SELECT COUNT(*) FROM `[db_prefix]_posts` WHERE `PostStatus`=''approval''', 'modules/boonex/blogs/post_mod_blog.php', 'SELECT COUNT(*) FROM `[db_prefix]_posts` WHERE `PostStatus`=''disapproval''', 'book', @iStatSiteOrder);

-- search objects
INSERT INTO `sys_objects_search` (`ID`, `ObjectName`, `Title`, `ClassName`, `ClassPath`) VALUES(NULL, 'blog', '_bx_blog_Blogs', 'BxBlogsSearchUnit', 'modules/boonex/blogs/classes/BxBlogsSearchUnit.php');

-- tag objects
INSERT INTO `sys_objects_tag` (`ID`, `ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) VALUES(NULL, 'blog', 'SELECT `Tags` FROM `[db_prefix]_posts` WHERE `PostID` = {iID} AND `PostStatus` = ''approval''', 'permalinks_blogs', 'blogs/tag/{tag}', 'modules/boonex/blogs/blogs.php?action=search_by_tag&tagKey={tag}', '_bx_blog_Blogs');

-- top menu
SET @iTopMenuLastOrder := (SELECT `Order` + 1 FROM `sys_menu_top` WHERE `Parent` = 0 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 0, 'Blogs', '_bx_blog_Blogs', 'modules/boonex/blogs/blogs.php?action=home|modules/boonex/blogs/blogs.php?action=search_by_tag&tagKey=|blogs/tag/|modules/boonex/blogs/blogs.php?action=show_calendar_day&date=|modules/boonex/blogs/blogs.php', @iTopMenuLastOrder, 'non,memb', '', '', '', 1, 1, 1, 'top', 'book', 'book', 1, '');
SET @menu_id = (SELECT LAST_INSERT_ID());

INSERT INTO `sys_menu_top` (`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(NULL, 9, 'Profile Blog', '_bx_blog_Blog', 'modules/boonex/blogs/blogs.php?action=show_member_blog&blogOwnerName={profileUsername}|blogs/posts/', 4, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, 4, 'Profile Blog', '_bx_blog_Blog', 'modules/boonex/blogs/blogs.php?action=my_page&mode=main|modules/boonex/blogs/blogs.php?action=show_member_blog&ownerID={memberID}', 4, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'All Blogs', '_bx_blog_All_Blogs', 'modules/boonex/blogs/blogs.php?action=all', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Blog Calendar', '_sys_calendar', 'modules/boonex/blogs/blogs.php?action=show_calendar', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Top Posts', '_bx_blog_Top_Posts', 'modules/boonex/blogs/blogs.php?action=top_posts', 4, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Blogs Tags', '_Tags', 'modules/boonex/blogs/blogs.php?action=tags', 7, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Blog Search', '_Search', 'searchKeyword.php?type=blog', 11, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Popular Posts', '_bx_blog_Popular_Posts', 'modules/boonex/blogs/blogs.php?action=popular_posts', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'All Posts', '_bx_blog_All_Posts', 'modules/boonex/blogs/blogs.php?action=all_posts', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, @menu_id, 'Blogs Home', '_bx_blog_Blogs_Home', 'modules/boonex/blogs/blogs.php?action=home', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(NULL, 0, 'Blog Post', '_bx_blog_post_view', 'modules/boonex/blogs/blogs.php?action=show_member_post|modules/boonex/blogs/blogs.php?action=edit_post|blogs/entry/', 0, 'non,memb', '', '', '', 1, 1, 1, 'system', '', 0, '');

-- member menu
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_blogs', `Eval` = 'return BxDolService::call(''blogs'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);

-- comments objects
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) 
VALUES('bx_blogs', '[db_prefix]_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', '[db_prefix]_posts', 'PostID', 'CommentsCount', 'BxBlogsCmts', 'modules/boonex/blogs/classes/BxBlogsCmts.php');

-- vote objects
INSERT INTO `sys_objects_vote` (`ID`, `ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`, `OverrideClassName`, `OverrideClassFile`) VALUES(NULL, 'bx_blogs', '[db_prefix]_rating', '[db_prefix]_voting_track', 'blogp_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, '', '', '[db_prefix]_posts', 'Rate', 'RateCount', 'PostID', '', '');

-- permalinks
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES
('modules/boonex/blogs/blogs.php', 'blogs/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=all', 'blogs/all/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=top_blogs', 'blogs/top/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=top_posts', 'blogs/top_posts/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=home', 'blogs/home/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=all_posts', 'blogs/all_posts/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=popular_posts', 'blogs/popular_posts/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=tags', 'blogs/tags/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=show_calendar', 'blogs/show_calendar/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=my_page&mode=main', 'blogs/my_page/', 'permalinks_blogs'),
('modules/boonex/blogs/blogs.php?action=show_member_blog&blogOwnerName=', 'blogs/posts/', 'permalinks_blogs');


-- views objects
INSERT INTO `sys_objects_views` VALUES(NULL, 'bx_blogs', '[db_prefix]_views_track', 86400, '[db_prefix]_posts', 'PostID', 'Views', 1);

-- Alerts Handler and Events
INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_blogs_profile_delete', '', '', 'BxDolService::call(''blogs'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'profile', 'delete', @iHandler);

-- INSERT INTO `sys_alerts_handlers`(`name`, `class`, `file`, `eval`) VALUES ('[db_prefix]', '', '', 'BxDolService::call(\'blogs\', \'response\', array($this), \'Response\');');
-- SET @iHandlerId = (SELECT LAST_INSERT_ID());

-- INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES('[db_prefix]', 'commentPost', 4);
-- INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES('[db_prefix]', 'create', @iHandlerId);
-- INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES('[db_prefix]', 'edit_post', @iHandlerId);
-- INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES('[db_prefix]', 'delete_post', @iHandlerId);
-- INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES('[db_prefix]', 'view_post', @iHandlerId);

-- Membership
SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog view', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog post view', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blogs browse', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blogs posts browse', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog post search', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog post add', NULL);
SET @iAction = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog posts edit any post', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog posts delete any post', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog posts approving', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'blog posts comments delete and edit', NULL);

-- privacy
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('blogs', 'comment', '_bx_blog_privacy_comment', '3'),
('blogs', 'rate', '_bx_blog_privacy_rate', '3'),
('blogs', 'view', '_bx_blog_privacy_view', '3');

-- actions
INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '_Add Post', 'plus', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/add/'' : ''modules/boonex/blogs/blogs.php?action=my_page&mode=add'';\r\nelse\r\n    return null;', 1, 'bx_blogs', 1),
(NULL, '{evalResult}', 'book', '{blog_owner_link}', '', 'if ({only_menu} == 1)\r\nreturn _t(''_bx_blog_My_blog'');\r\nelse\r\nreturn null;', 2, 'bx_blogs', 1),
(NULL, '_bx_blog_Blogs_Home', 'book', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/home/'' : ''modules/boonex/blogs/blogs.php?action=home'';\r\nelse\r\n    return null;', 3, 'bx_blogs', 0),
(NULL, '{evalResult}', 'star-o', '{post_entry_url}&do=cfs&id={post_id}', '', '$iPostFeature = (int)''{post_featured}'';\r\nif (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true) {\r\nreturn ($iPostFeature==1) ? _t(''_De-Feature it'') : _t(''_Feature it'');\r\n}\r\nelse\r\nreturn null;', 4, 'bx_blogs', 0),
(NULL, '_Edit', 'edit', '{evalResult}', '', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true || {edit_allowed}) {\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/edit/{post_id}'' : ''modules/boonex/blogs/blogs.php?action=edit_post&EditPostID={post_id}'';\r\n}\r\nelse\r\n    return null;', 5, 'bx_blogs', 0),
(NULL, '{evalResult}', 'remove', '', 'iDelPostID = {post_id}; sWorkUrl = ''{work_url}''; if (confirm(''{sure_label}'')) { window.open (sWorkUrl+''?action=delete_post&DeletePostID=''+iDelPostID,''_self''); }', '$oModule = BxDolModule::getInstance(''BxBlogsModule'');\r\n if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true || $oModule->isAllowedPostDelete({owner_id})) {\r\nreturn _t(''_Delete'');\r\n}\r\nelse\r\nreturn null;', 6, 'bx_blogs', 0),
(NULL, '{evalResult}', 'check-circle-o', '{post_inside_entry_url}&sa={sSAAction}', '', '$sButAct = ''{sSACaption}'';\r\nif ({admin_mode} == true || {allow_approve}) {\r\nreturn $sButAct;\r\n}\r\nelse\r\nreturn null;', 7, 'bx_blogs', 0),
(NULL, '{sbs_blogs_title}', 'paperclip', '', '{sbs_blogs_script}', '', 8, 'bx_blogs', 0),
(NULL, '{TitleShare}', 'share-square-o', '', 'showPopupAnyHtml(''{base_url}blogs.php?action=share_post&post_id={post_id}'');', '', 9, 'bx_blogs', 0),
(NULL, '_bx_blog_Back_to_Blog', 'book', '{evalResult}', '', 'return ''{blog_owner_link}'';\r\n', 10, 'bx_blogs', 0),
(NULL, '{repostCpt}', 'repeat', '', '{repostScript}', '', 11, 'bx_blogs', 0);

INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '{evalResult}', 'rss', '{site_url}rss_factory.php?action=blogs&pid={owner_id}', '', 'return _t(''_bx_blog_RSS'');', 1, 'bx_blogs_m', 0),
(NULL, '_bx_blog_Back_to_Blog', 'book', '{blog_owner_link}', '', '', 2, 'bx_blogs_m', 0),
(NULL, '_Add Post', 'plus', '{evalResult}', '', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/add/'' : ''modules/boonex/blogs/blogs.php?action=my_page&mode=add'';\r\nelse\r\nreturn null;', 3, 'bx_blogs_m', 1),
(NULL, '{evalResult}', 'edit', '', 'PushEditAtBlogOverview(''{blog_id}'', ''{blog_description_js}'', ''{owner_id}'');', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn _t(''_bx_blog_Edit_blog'');\r\nelse\r\nreturn null;', 4, 'bx_blogs_m', 1),
(NULL, '{evalResult}', 'remove', '', 'if (confirm(''{sure_label}'')) window.open (''{work_url}?action=delete_blog&DeleteBlogID={blog_id}'',''_self'');', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn _t(''_bx_blog_Delete_blog'');\r\nelse\r\nreturn null;', 5, 'bx_blogs_m', 0);

-- subscriptions
INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('bx_blogs', '', '', 'return BxDolService::call(''blogs'', ''get_subscription_params'', array($arg2, $arg3));'),
('bx_blogs', 'commentPost', 't_sbsBlogpostsComments', 'return BxDolService::call(''blogs'', ''get_subscription_params'', array($arg2, $arg3));');

-- email templates
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsBlogpostsComments', 'New Comments To A Blog Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">blog post you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to blog post', 0);

-- mobile

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_blogs', 'homepage', '_bx_blog_Blogs', '{site_url}modules/boonex/blogs/templates/base/images/icons/mobile_icon.png', 100, '{xmlrpc_url}r.php?url=modules%2Fboonex%2Fblogs%2Fblogs.php%3Faction%3Dmobile%26mode%3Dlast&user={member_username}&pwd={member_password}', '', '', @iMaxOrderHomepage, 1),
('bx_blogs', 'profile', '_bx_blog_Blog', '', 100, '{xmlrpc_url}r.php?url=modules%2Fboonex%2Fblogs%2Fblogs.php%3Faction%3Dmobile%26mode%3Duser%26id%3D{profile_id}&user={member_username}&pwd={member_password}', 'return BxDolService::call(''blogs'', ''get_posts_count_for_member'', array(''{profile_id}''));', '', @iMaxOrderProfile, 1);

-- sitemap

SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_blogs', '_bx_blog_blog_posts', '0.8', 'auto', 'BxBlogsSiteMapsPosts', 'modules/boonex/blogs/classes/BxBlogsSiteMapsPosts.php', @iMaxOrderSiteMaps, 1);

-- chart

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_blogs', '_bx_blog_chart', 'bx_blogs_posts', 'PostDate', '', '', 1, @iMaxOrderCharts);

-- export

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_blogs', '_bx_blog_Blogs', 'BxBlogsExport', 'modules/boonex/blogs/classes/BxBlogsExport.php', @iMaxOrderExports, 1);

