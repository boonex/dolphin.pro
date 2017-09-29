--
-- Table structure for table `[db_prefix]entries`
--
CREATE TABLE IF NOT EXISTS `[db_prefix]entries` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `author_id` int(11) unsigned NOT NULL default '0',  
  `caption` varchar(64) NOT NULL default '',
  `content` text NOT NULL,
  `uri` varchar(64) NOT NULL default '',
  `tags` varchar(255) NOT NULL default '',
  `allow_comment_to` int(11) NOT NULL default '0',
  `allow_vote_to` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `rate` int(11) NOT NULL default '0',
  `rate_count` int(11) NOT NULL default '0',
  `cmts_count` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`),
  FULLTEXT KEY `search_group` (`caption`, `content`, `tags`),
  FULLTEXT KEY `search_caption` (`caption`),
  FULLTEXT KEY `search_content` (`content`),
  FULLTEXT KEY `search_tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `[db_prefix]voting`
--
CREATE TABLE `[db_prefix]voting` (
  `fdb_id` bigint(8) NOT NULL default '0',
  `fdb_rating_count` int(11) NOT NULL default '0',
  `fdb_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `fdb_id` (`fdb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `[db_prefix]voting_track`
--
CREATE TABLE `[db_prefix]voting_track` (
  `fdb_id` bigint(8) NOT NULL default '0',
  `fdb_ip` varchar(20) default NULL,
  `fdb_date` datetime default NULL,
  KEY `fdb_ip` (`fdb_ip`,`fdb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

SET @iTMOrder = (SELECT MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='120');
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(120, 'Feedback', '_feedback_top_menu_sitem', 'modules/?r=feedback/index/|modules/?r=feedback/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(0, 'FeedbackView', '_feedback_top_menu_sitem', 'modules/?r=feedback/view/', 0, 'non,memb', '', '', '', 1, 1, 1, 'system', 'thumbs-up', 0, '');

SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(2, 'bx_feedback', '_feedback_admin_menu_sitem', '{siteUrl}modules/?r=feedback/admin/', 'For managing member\'s feedback', 'thumbs-up', '', '', @iOrder+1);


INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=feedback/', 'm/feedback/', 'permalinks_module_feedback');


SET @iCategoryOrder = (SELECT MAX(`menu_order`) FROM `sys_options_cats`) + 1;
INSERT INTO `sys_options_cats` (`name` , `menu_order` ) VALUES ('Feedback', @iCategoryOrder);
SET @iCategoryId = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES
('permalinks_module_feedback', 'on', 26, 'Enable friendly feedback permalink', 'checkbox', '', '', 0),
('feedback_autoapprove', 'on', @iCategoryId, 'Enable autoapprove for members feedback', 'checkbox', '', '', 1),
('feedback_comments', 'on', @iCategoryId, 'Allow comments for feedback', 'checkbox', '', '', 2),
('feedback_votes', 'on', @iCategoryId, 'Allow votes for feedback', 'checkbox', '', '', 3),
('feedback_index_number', '3', @iCategoryId, 'The number of feedback on home page', 'digit', '', '', 4),
('feedback_snippet_length', '200', @iCategoryId, 'The length of feedback snippet for home pages', 'digit', '', '', 5),
('feedback_per_page', '3', @iCategoryId, 'The number of items shown on the page', 'digit', '', '', 6),
('feedback_rss_length', '10', @iCategoryId, 'The number of items shown in the RSS feed', 'digit', '', '', 7);

INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('bx_feedback', '[db_prefix]comments', '[db_prefix]comments_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '[db_prefix]entries', 'id', 'cmts_count', 'BxFdbCmts', 'modules/boonex/feedback/classes/BxFdbCmts.php');

INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`, `OverrideClassName`, `OverrideClassFile`) VALUES
('bx_feedback', '[db_prefix]voting', '[db_prefix]voting_track', 'fdb_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, '', '', '[db_prefix]entries', 'rate', 'rate_count', 'id', 'BxFdbVoting', 'modules/boonex/feedback/classes/BxFdbVoting.php');

INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) VALUES
('bx_feedback', 'SELECT `tags` FROM `[db_prefix]entries` WHERE `id`={iID} AND `status`=0', 'permalinks_module_feedback', 'm/feedback/tag/{tag}', 'modules/?r=feedback/tag/{tag}', '_feedback_lcaption_tags_object');

INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`) VALUES
('bx_feedback', '_feedback_lcaption_search_object', 'BxFdbSearchResult', 'modules/boonex/feedback/classes/BxFdbSearchResult.php');


SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES ('feedback', 'Feedback', @iPCPOrder+1);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Feedback from your customers', '_feedback_bcaption_index', 0, 0, 'PHP', 'return BxDolService::call(\'feedback\', \'archive_block_index\', array(0, 0, false));', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback main content', '_feedback_bcaption_view_main', 1, 0, 'Content', '', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback comments', '_feedback_bcaption_view_comment', 1, 1, 'Comment', '', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback actions', '_feedback_bcaption_view_action', 2, 0, 'Action', '', 1, 28.1, 'non,memb', 0),
('feedback', '1140px', 'Feedback rating', '_feedback_bcaption_view_vote', 2, 1, 'Vote', '', 1, 28.1, 'non,memb', 0),
('feedback', '1140px', 'Social sharing', '_sys_block_title_social_sharing', 2, 2, 'SocialSharing', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_privacy_actions`(`module_uri`, `name`, `title`, `default_group`) VALUES 
('feedback', 'comment', '_feedback_psaction_comment', '3'),
('feedback', 'vote', '_feedback_psaction_vote', '3');

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_feedback_title}', 'paperclip', '', '{sbs_feedback_script}', '', 1, 'bx_feedback', 0),
('{del_feedback_title}', 'remove', '', '{del_feedback_script}', '', 2, 'bx_feedback', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('bx_feedback', '', '', 'return BxDolService::call(\'feedback\', \'get_subscription_params\', array($arg1, $arg2, $arg3));'),
('bx_feedback', 'commentPost', 't_sbsFeedbackComments', 'return BxDolService::call(\'feedback\', \'get_subscription_params\', array($arg1, $arg2, $arg3));');

INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsFeedbackComments', 'New Comments To A Feedback Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">feedback post you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to feedback', 0);

INSERT INTO `sys_acl_actions`(`Name`, `AdditionalParamName`) VALUES ('Feedback Delete', '');

-- site stats
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'fdb', 'feedback_ss', 'modules/?r=feedback/index/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''0''', 'modules/?r=feedback/admin/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''1''', 'thumbs-up', @iStatSiteOrder);

-- sitemap
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_feedback', '_feedback_sitemap', '0.8', 'auto', 'BxFdbSiteMaps', 'modules/boonex/feedback/classes/BxFdbSiteMaps.php', @iMaxOrderSiteMaps, 1);

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_feedback', '_feedback_chart', 'bx_fdb_entries', 'date', '', '', 1, @iMaxOrderCharts);

