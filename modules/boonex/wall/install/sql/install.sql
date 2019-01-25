--
-- Table structure for table `[db_prefix]events`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]events` (
  `id` bigint(8) NOT NULL auto_increment,
  `owner_id` int(10) unsigned NOT NULL default '0',
  `object_id` text collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `action` varchar(255) collate utf8_unicode_ci NOT NULL,
  `content` text collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `reposts` int(11) unsigned NOT NULL default '0',
  `date` int(11) unsigned NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  `hidden` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `[db_prefix]comments`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]comments` (
  `cmt_id` int(11) NOT NULL auto_increment,
  `cmt_parent_id` int(11) NOT NULL default '0',
  `cmt_object_id` int(11) NOT NULL default '0',
  `cmt_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL,
  `cmt_mood` tinyint(4) NOT NULL default '0',
  `cmt_rate` int(11) NOT NULL default '0',
  `cmt_rate_count` int(11) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_id`),
  KEY `cmt_object_id` (`cmt_object_id`,`cmt_parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `[db_prefix]comments_track`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]comments_track` (
  `cmt_system_id` int(11) NOT NULL default '0',
  `cmt_id` int(11) NOT NULL default '0',
  `cmt_rate` tinyint(4) NOT NULL default '0',
  `cmt_rate_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_rate_author_nip` int(11) unsigned NOT NULL default '0',
  `cmt_rate_ts` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_system_id`,`cmt_id`,`cmt_rate_author_nip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `[db_prefix]repost_track`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]repost_track` (
  `event_id` int(11) NOT NULL default '0',
  `author_id` int(11) NOT NULL default '0',
  `author_nip` int(11) unsigned NOT NULL default '0',
  `reposted_id` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  UNIQUE KEY `event_id` (`event_id`),
  KEY `repost` (`reposted_id`, `author_nip`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `[db_prefix]voting`
--
CREATE TABLE `[db_prefix]voting` (
  `wall_id` bigint(8) NOT NULL default '0',
  `wall_rating_count` int(11) NOT NULL default '0',
  `wall_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `wall_id` (`wall_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `[db_prefix]voting_track`
--
CREATE TABLE `[db_prefix]voting_track` (
  `wall_id` bigint(8) NOT NULL default '0',
  `wall_ip` varchar(20) default NULL,
  `wall_date` datetime default NULL,
  KEY `wall_ip` (`wall_ip`,`wall_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `[db_prefix]handlers`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]handlers` (
  `id` int(11) NOT NULL auto_increment,
  `alert_unit` varchar(64) NOT NULL default '',
  `alert_action` varchar(64) NOT NULL default '',
  `module_uri` varchar(64) NOT NULL default '',
  `module_class` varchar(64) NOT NULL default '',
  `module_method` varchar(64) NOT NULL default '',
  `groupable` tinyint(1) NOT NULL default '0',
  `group_by` varchar(64) NOT NULL default '',
  `timeline` tinyint(1) NOT NULL default '1',
  `outline` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE `handler` (`alert_unit`, `alert_action`, `module_uri`, `module_class`, `module_method`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `[db_prefix]handlers`(`alert_unit`, `alert_action`, `module_uri`, `module_class`, `module_method`, `groupable`, `group_by`, `timeline`, `outline`) VALUES
('wall_common_text', '', '', '', '', 0, '', 1, 0),
('wall_common_link', '', '', '', '', 0, '', 1, 0),
('wall_common_photos', '', '', '', '', 0, '', 1, 0),
('wall_common_sounds', '', '', '', '', 0, '', 1, 0),
('wall_common_videos', '', '', '', '', 0, '', 1, 0),
('wall_common_repost', '', '', '', '', 0, '', 1, 0),
('profile', 'edit', '', '', '', 0, '', 1, 0),
('profile', 'edit_status_message', '', '', '', 0, '', 1, 0),
('profile', 'comment_add', '', '', '', 0, '', 1, 0),
('profile', 'commentPost', '', '', '', 0, '', 1, 0),
('profile', 'delete', '', '', '', 0, '', 1, 0),
('friend', 'accept', '', '', '', 0, '', 1, 0),
('comment', 'add', '', '', '', 0, '', 0, 0);


SELECT @iPCPOrder:=MAX(`Order`) FROM `sys_page_compose_pages`;
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES ('wall', 'Wall', @iPCPOrder+1);

SET @iPCOrderIndex = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='index' AND `Column`='1');
SET @iPCOrderProfile = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='profile' AND `Column`='3');
SET @iPCOrderMember = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='member' AND `Column`='1');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Events Outline', '_wall_pc_view_index_ol', 1, IFNULL(@iPCOrderIndex, 0), 'PHP', 'return BxDolService::call(\'wall\', \'view_block_index_outline\');', 1, 71.9, 'non,memb', 0),
('index', '1140px', 'Home Timeline: Post', '_wall_pc_post_index_tl', 1, IFNULL(@iPCOrderIndex, 0)+1, 'PHP', 'return BxDolService::call(\'wall\', \'post_block_index_timeline\');', 1, 71.9, 'memb', 0),
('index', '1140px', 'Home Timeline: View', '_wall_pc_view_index_tl', 1, IFNULL(@iPCOrderIndex, 0)+2, 'PHP', 'return BxDolService::call(\'wall\', \'view_block_index_timeline\');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Profile Timeline: Post', '_wall_pc_post_profile_tl', 3, IFNULL(@iPCOrderProfile, 0), 'PHP', 'return BxDolService::call(\'wall\', \'post_block_profile_timeline\', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'memb', 0),
('profile', '1140px', 'Profile Timeline: View', '_wall_pc_view_profile_tl', 3, IFNULL(@iPCOrderProfile, 0)+1, 'PHP', 'return BxDolService::call(\'wall\', \'view_block_profile_timeline\', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Friends Timeline: View', '_wall_pc_view_account_tl', 1, IFNULL(@iPCOrderMember, 0), 'PHP', 'return BxDolService::call(\'wall\', \'view_block_account_timeline\', array($this->iMember));', 1, 71.9, 'memb', 0),
('wall', '1140px', 'Post event to a Timeline', '_wall_pc_post', 1, 0, 'Post', '', 1, 100, 'memb', 0),
('wall', '1140px', 'View events on a Timeline', '_wall_pc_view', 1, 1, 'View', '', 1, 100, 'non,memb', 0);


SELECT @iTMOrderOwner:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='4';
SELECT @iTMOrderViewer:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='9';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(4, 'TimelineOwner', '_wall_top_smenu_item_my_wall', 'modules/?r=wall/', @iTMOrderOwner+1, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(9, 'TimelineViewer', '_wall_top_smenu_item_wall', 'modules/?r=wall/index/{profileUsername}', @iTMOrderViewer+1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');


SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(2, 'bx_wall', '_wall_admin_menu_sitem', '{siteUrl}modules/?r=wall/admin/', 'For managing Timeline', 'clock-o', '', '', @iOrder+1);


INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`)
VALUES('bx_wall', '[db_prefix]comments', '[db_prefix]comments_track', 0, 1, 90, 9999, 1, -3, 'none', 0, 1, 0, 'wcmt', '', '', '', 'BxWallCmts', 'modules/boonex/wall/classes/BxWallCmts.php');


INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`, `OverrideClassName`, `OverrideClassFile`) 
VALUES ('bx_wall', '[db_prefix]voting', '[db_prefix]voting_track', 'wall_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, '', '', '', '', '', '', 'BxWallVoting', 'modules/boonex/wall/classes/BxWallVoting.php');


SET @iCategoryOrder = (SELECT MAX(`menu_order`) FROM `sys_options_cats`) + 1;
INSERT INTO `sys_options_cats` (`name` , `menu_order` ) VALUES ('Timeline', @iCategoryOrder);
SET @iCategoryId = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('permalinks_module_wall', 'on', 26, 'Enable friendly Timeline permalink', 'checkbox', '', '', 0, ''),
('wall_enable_guest_comments', '', @iCategoryId, 'Allow non-members to post in Timeline', 'checkbox', '', '', 1, ''),
('wall_enable_delete', 'on', @iCategoryId, 'Allow Timeline owner to remove events', 'checkbox', '', '', 2, ''),
('wall_events_per_page_index_ol', '20', @iCategoryId, 'Number of events are displayed in Outline on Home page', 'digit', '', '', 3, ''),
('wall_events_per_page_index_tl', '10', @iCategoryId, 'Number of events are displayed in Timeline on Home page', 'digit', '', '', 4, ''),
('wall_events_per_page_profile_tl', '10', @iCategoryId, 'Number of events are displayed in Timeline on Profile page', 'digit', '', '', 5, ''),
('wall_events_per_page_account_tl', '20', @iCategoryId, 'Number of events are displayed in Timeline on Account page', 'digit', '', '', 6, ''),
('wall_events_chars_display_max', '500', @iCategoryId, 'Max number of displayed character in text post', 'digit', '', '', 7, ''),
('wall_rss_length', '5', @iCategoryId, 'The length of RSS feed', 'digit', '', '', 8, ''),
('wall_events_hide_timeline', '', @iCategoryId, 'Hide events from Timeline', 'select_multiple', '', '', 9, 'PHP:return BxDolService::call(\'wall\', \'get_actions_checklist\', array(\'timeline\'));'),
('wall_events_hide_outline', '', @iCategoryId, 'Hide events from Outline', 'select_multiple', '', '', 10, 'PHP:return BxDolService::call(\'wall\', \'get_actions_checklist\', array(\'outline\'));'),
('wall_uploaders_hide_timeline', '', @iCategoryId, 'Hide uploaders from Post to Timeline block', 'select_multiple', '', '', 11, 'PHP:return BxDolService::call(\'wall\', \'get_uploaders_checklist\', array(\'timeline\'));');


SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions`(`Name`, `AdditionalParamName`) VALUES ('timeline repost', '');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
(@iLevelStandard, @iAction), 
(@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions`(`Name`, `AdditionalParamName`) VALUES ('timeline post comment', '');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
(@iLevelStandard, @iAction), 
(@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions`(`Name`, `AdditionalParamName`) VALUES ('timeline delete comment', '');


INSERT INTO `sys_categories`(`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES
('wall', 0, 'bx_photos', 0, 'active'),
('wall', 0, 'bx_sounds', 0, 'active'),
('wall', 0, 'bx_videos', 0, 'active');


INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=wall/', 'm/wall/', 'permalinks_module_wall');


INSERT INTO `sys_alerts_handlers`(`name`, `class`, `file`, `eval`) VALUES ('bx_wall', '', '', 'BxDolService::call(\'wall\', \'response\', array($this));');
SET @iHandlerId = LAST_INSERT_ID();

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'edit', @iHandlerId),
('profile', 'edit_status_message', @iHandlerId),
('profile', 'delete', @iHandlerId),
('friend', 'accept', @iHandlerId),
('comment', 'add', @iHandlerId);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('bx_wall', '', '', 'return BxDolService::call(\'wall\', \'get_subscription_params\', array($arg1, $arg2, $arg3));'),
('bx_wall', 'update', 't_sbsWallUpdates', 'return BxDolService::call(\'wall\', \'get_subscription_params\', array($arg1, $arg2, $arg3));');

INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsWallUpdates', 'Subscription: New Timeline Event', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>\r\nThere was a new update of the Timeline you subscribed to!\r\n</p>\r\n\r\n<p>\r\n<a href="<ViewLink>">View it now</a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: New Timeline Event', 0);

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_wall', '_bx_wall_chart', 'bx_wall_events', 'date', '', '', 1, @iMaxOrderCharts);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_wall', '_wall', 'BxWallExport', 'modules/boonex/wall/classes/BxWallExport.php', @iMaxOrderExports, 1);
