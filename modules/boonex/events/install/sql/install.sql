-- create tables
CREATE TABLE IF NOT EXISTS `[db_prefix]main` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Title` varchar(100) NOT NULL default '',
  `EntryUri` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('approved','pending') NOT NULL default 'approved',
  `Country` varchar(2) NOT NULL default 'US',
  `City` varchar(50) NOT NULL default '',
  `Place` varchar(100) NOT NULL default '',
  `PrimPhoto` int(11) NOT NULL,
  `Date` int(11) NOT NULL,
  `EventStart` int(11) NOT NULL default '0',
  `EventEnd` int(11) NOT NULL default '0',
  `ResponsibleID` int(10) unsigned NOT NULL default '0',
  `EventMembershipFilter` varchar(100) NOT NULL default '',  
  `Tags` varchar(255) NOT NULL default '',
  `Categories` text NOT NULL,
  `Views` int(11) NOT NULL,
  `Rate` float NOT NULL,
  `RateCount` int(11) NOT NULL,
  `CommentsCount` int(11) NOT NULL,
  `FansCount` int(11) NOT NULL,
  `Featured` tinyint(4) NOT NULL,
  `allow_view_event_to` int(11) NOT NULL,
  `allow_view_participants_to` varchar(16) NOT NULL,
  `allow_comment_to` varchar(16) NOT NULL,
  `allow_rate_to` varchar(16) NOT NULL,
  `allow_join_to` int(11) NOT NULL,
  `allow_post_in_forum_to` varchar(16) NOT NULL,
  `allow_view_forum_to` varchar(16) NOT NULL,
  `JoinConfirmation` tinyint(4) NOT NULL default '0',
  `allow_upload_photos_to` varchar(16) NOT NULL,
  `allow_upload_videos_to` varchar(16) NOT NULL,
  `allow_upload_sounds_to` varchar(16) NOT NULL,
  `allow_upload_files_to` varchar(16) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EntryUri` (`EntryUri`),
  KEY `ResponsibleID` (`ResponsibleID`),
  KEY `EventStart` (`EventStart`),
  KEY `Date` (`Date`),
  FULLTEXT KEY `Title` (`Title`,`Description`,`City`,`Place`,`Tags`,`Categories`),
  FULLTEXT KEY `Tags` (`Tags`),
  FULLTEXT KEY `Categories` (`Categories`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]images` (
  `entry_id` int(10) unsigned NOT NULL,
  `media_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `entry_id` (`entry_id`,`media_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]videos` (
  `entry_id` int(10) unsigned NOT NULL,
  `media_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `entry_id` (`entry_id`,`media_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]sounds` (
  `entry_id` int(10) unsigned NOT NULL,
  `media_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `entry_id` (`entry_id`,`media_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]files` (
  `entry_id` int(10) unsigned NOT NULL,
  `media_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `entry_id` (`entry_id`,`media_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]participants` (
  `id_entry` int(10) unsigned NOT NULL,
  `id_profile` int(10) unsigned NOT NULL,
  `when` int(10) unsigned NOT NULL,
  `confirmed` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id_entry`,`id_profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]admins` (
  `id_entry` int(10) unsigned NOT NULL,
  `id_profile` int(10) unsigned NOT NULL,
  `when` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_entry`, `id_profile`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]rating` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_rating_count` int( 11 ) NOT NULL default '0',
  `gal_rating_sum` int( 11 ) NOT NULL default '0',
  UNIQUE KEY `gal_id` (`gal_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `[db_prefix]rating_track` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_ip` varchar( 20 ) default NULL,
  `gal_date` datetime default NULL,
  KEY `gal_ip` (`gal_ip`, `gal_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `[db_prefix]cmts` (
  `cmt_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
  `cmt_parent_id` int( 11 ) NOT NULL default '0',
  `cmt_object_id` int( 12 ) NOT NULL default '0',
  `cmt_author_id` int( 10 ) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL ,
  `cmt_mood` tinyint( 4 ) NOT NULL default '0',
  `cmt_rate` int( 11 ) NOT NULL default '0',
  `cmt_rate_count` int( 11 ) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int( 11 ) NOT NULL default '0',
  PRIMARY KEY ( `cmt_id` ),
  KEY `cmt_object_id` (`cmt_object_id` , `cmt_parent_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `[db_prefix]cmts_track` (
  `cmt_system_id` int( 11 ) NOT NULL default '0',
  `cmt_id` int( 11 ) NOT NULL default '0',
  `cmt_rate` tinyint( 4 ) NOT NULL default '0',
  `cmt_rate_author_id` int( 10 ) unsigned NOT NULL default '0',
  `cmt_rate_author_nip` int( 11 ) unsigned NOT NULL default '0',
  `cmt_rate_ts` int( 11 ) NOT NULL default '0',
  PRIMARY KEY (`cmt_system_id` , `cmt_id` , `cmt_rate_author_nip`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `[db_prefix]views_track` (
  `id` int(10) unsigned NOT NULL,
  `viewer` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  KEY `id` (`id`,`viewer`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]shoutbox` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `HandlerID` int(11) NOT NULL,
  `OwnerID` int(11) NOT NULL,
  `Message` blob NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IP` (`IP`),
  KEY `HandlerID` (`HandlerID`)
) ENGINE=MyISAM;

-- create forum tables

CREATE TABLE `[db_prefix]forum` (
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
  `entry_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`forum_id`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_uri` (`forum_uri`),
  KEY `entry_id` (`entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_cat` (
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

INSERT INTO `[db_prefix]forum_cat` (`cat_id`, `cat_uri`, `cat_name`, `cat_icon`, `cat_order`) VALUES 
(1, 'Events', 'Events', '', 64);

CREATE TABLE `[db_prefix]forum_flag` (
  `user` varchar(32) NOT NULL default '',
  `topic_id` int(11) NOT NULL default '0',
  `when` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_post` (
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
  KEY `when` (`when`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_topic` (
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
  KEY `topic_uri` (`topic_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_user` (
  `user_name` varchar(32) NOT NULL default '',
  `user_pwd` varchar(32) NOT NULL default '',
  `user_email` varchar(128) NOT NULL default '',
  `user_join_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_name`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_user_activity` (
  `user` varchar(32) NOT NULL default '',
  `act_current` int(11) NOT NULL default '0',
  `act_last` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_user_stat` (
  `user` varchar(32) NOT NULL default '',
  `posts` int(11) NOT NULL default '0',
  `user_last_post` int(11) NOT NULL default '0',
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_vote` (
  `user_name` varchar(32) NOT NULL default '',
  `post_id` int(11) NOT NULL default '0',
  `vote_when` int(11) NOT NULL default '0',
  `vote_point` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_name`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_actions_log` (
  `user_name` varchar(32) NOT NULL default '',
  `id` int(11) NOT NULL default '0',
  `action_name` varchar(32) NOT NULL default '',
  `action_when` int(11) NOT NULL default '0',
  KEY `action_when` (`action_when`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]forum_attachments` (
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

CREATE TABLE IF NOT EXISTS `[db_prefix]forum_signatures` (
  `user` varchar(32) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `when` int(11) NOT NULL,
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- page compose pages
SET @iMaxOrder = (SELECT `Order` FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_events_view', 'Events View Event', @iMaxOrder+1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_events_celendar', 'Events Calendar', @iMaxOrder+2);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_events_main', 'Events Home', @iMaxOrder+3);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_events_my', 'Events User', @iMaxOrder+4);

-- page compose blocks
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_events_view', '1140px', 'Event''s info block', '_bx_events_block_info', '2', '0', 'Info', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s actions block', '_bx_events_block_actions', '2', '1', 'Actions', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s rate block', '_bx_events_block_rate', '2', '2', 'Rate', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s social sharing block', '_sys_block_title_social_sharing', '2', '3', 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
    ('bx_events_view', '1140px', 'Event''s files block', '_bx_events_block_files', '2', '4', 'Files', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s participants block', '_bx_events_block_participants', '2', '5', 'Participants', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s unconfirmed participants block', '_bx_events_block_participants_unconfirmed', '2', '6', 'ParticipantsUnconfirmed', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s Location', '_Location', '2', '7', 'PHP', 'return BxDolService::call(''wmap'', ''location_block'', array(''events'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 1, 28.1, 'non,memb', 0),
    ('bx_events_view', '1140px', 'Event''s Chat', '_Chat', '2', '8', 'PHP', 'return BxDolService::call(''shoutbox'', ''get_shoutbox'', array(''bx_events'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 11, 28.1, 'non,memb', 0),
    ('bx_events_view', '1140px', 'Event''s description block', '_bx_events_block_desc', '1', '0', 'Desc', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s photos block', '_bx_events_block_photos', '1', '1', 'Photos', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s videos block', '_bx_events_block_videos', '1', '2', 'Videos', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s sounds block', '_bx_events_block_sounds', '1', '3', 'Sounds', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s comments block', '_bx_events_block_comments', '1', '4', 'Comments', '', '1', '71.9', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s forum feed', '_sys_block_title_forum_feed', '1', '5', 'ForumFeed', '', '1', 71.9, 'non,memb', '0'),

    ('bx_events_main', '1140px', 'Upcoming Events Photo', '_bx_events_block_upcoming_photo', '1', '0', 'UpcomingPhoto', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Upcoming Events List', '_bx_events_block_upcoming_list', '1', '1', 'UpcomingList', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Map', '_Map', '1', '2', 'PHP', 'return BxDolService::call(''wmap'', ''homepage_part_block'', array (''events''));', 1, 71.9, 'non,memb', 0),
    ('bx_events_main', '1140px', 'Calendar', '_bx_events_block_calendar', '2', '0', 'Calendar', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Past Events', '_bx_events_block_past_list', '0', '0', 'PastList', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Recently Added Events', '_bx_events_block_recently_added_list', '0', '0', 'RecentlyAddedList', '', '1', '71.9', 'non,memb', '0'),

    ('bx_events_my', '1140px', 'Administration', '_bx_events_block_administration', '1', '0', 'Owner', '', '1', '100', 'non,memb', '0'),
    ('bx_events_my', '1140px', 'User''s events', '_bx_events_block_user_events', '1', '1', 'Browse', '', '0', '100', 'non,memb', '0'),

    ('index', '1140px', 'Events', '_bx_events_block_home', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''homepage_block'');', 1, 71.9, 'non,memb', 0),
    ('profile', '1140px', 'User Events', '_bx_events_block_my_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
	('profile', '1140px', 'Joined Events', '_bx_events_block_joined_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
    ('member', '1140px', 'Joined Events', '_bx_events_block_joined_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0);

-- permalink
INSERT INTO `sys_permalinks` VALUES (NULL, 'modules/?r=events/', 'm/events/', 'bx_events_permalinks');

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Events', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('category_auto_app_bx_events', 'on', @iCategId, 'Activate all categories after creation automatically', 'checkbox', '', '', '0', ''),
('bx_events_permalinks', 'on', 26, 'Enable friendly permalinks in events', 'checkbox', '', '', '0', ''),
('bx_events_autoapproval', 'on', @iCategId, 'Activate all events after creation automatically', 'checkbox', '', '', '0', ''),
('bx_events_main_upcoming_event_from_featured_only', '', @iCategId, 'Main upcoming event from featured events only', 'checkbox', '', '', '0', ''),
('bx_events_only_upcoming_events_on_map', '', @iCategId, 'Display only upcoming and current events on the map', 'checkbox', '', '', '0', ''),
('bx_events_max_email_invitations', '10', @iCategId, 'Max number of email invitation to send per one invite', 'digit', '', '', '0', ''),
('bx_events_perpage_main_upcoming', '10', @iCategId, 'Number of upcoming events to show on main page', 'digit', '', '', '0', ''),
('bx_events_perpage_main_recent', '4', @iCategId, 'Number of recently added events to show on main page', 'digit', '', '', '0', ''),
('bx_events_perpage_main_past', '6', @iCategId, 'Number of past events to show on main page', 'digit', '', '', '0', ''),
('bx_events_perpage_participants', '9', @iCategId, 'Number of participants to show on event view page', 'digit', '', '', '0', ''),
('bx_events_perpage_browse_participants', '30', @iCategId, 'Number of items to show on browse participants page', 'digit', '', '', '0', ''),
('bx_events_perpage_browse', '14', @iCategId, 'Number of events to show on browse pages', 'digit', '', '', '0', ''),
('bx_events_perpage_homepage', '5', @iCategId, 'Number of events to show on homepage', 'digit', '', '', '0', ''),
('bx_events_homepage_default_tab', 'upcoming', @iCategId, 'Default block tab on homepage', 'select', '', '', '0', 'upcoming,featured,recent,top,popular'),
('bx_events_perpage_profile', '5', @iCategId, 'Number of events to show on profile page', 'digit', '', '', '0', ''),
('bx_events_max_rss_num', '10', @iCategId, 'Max number of rss items to provide', 'digit', '', '', '0', '');

-- search objects
INSERT INTO `sys_objects_search` VALUES(NULL, 'bx_events', '_bx_events', 'BxEventsSearchResult', 'modules/boonex/events/classes/BxEventsSearchResult.php');

-- vote objects
INSERT INTO `sys_objects_vote` VALUES (NULL, 'bx_events', 'bx_events_rating', 'bx_events_rating_track', 'gal_', '5', 'vote_send_result', 'BX_PERIOD_PER_VOTE', '1', '', '', 'bx_events_main', 'Rate', 'RateCount', 'ID', 'BxEventsVoting', 'modules/boonex/events/classes/BxEventsVoting.php');

-- comments objects
INSERT INTO `sys_objects_cmts` VALUES (NULL, 'bx_events', 'bx_events_cmts', 'bx_events_cmts_track', '0', '1', '90', '5', '1', '-3', 'none', '0', '1', '0', 'cmt', 'bx_events_main', 'ID', 'CommentsCount', 'BxEventsCmts', 'modules/boonex/events/classes/BxEventsCmts.php');

-- views objects
INSERT INTO `sys_objects_views` VALUES(NULL, 'bx_events', 'bx_events_views_track', 86400, 'bx_events_main', 'ID', 'Views', 1);

-- tag objects
INSERT INTO `sys_objects_tag` VALUES (NULL, 'bx_events', 'SELECT `Tags` FROM `[db_prefix]main` WHERE `ID` = {iID} AND `Status` = ''approved''', 'bx_events_permalinks', 'm/events/browse/tag/{tag}', 'modules/?r=events/browse/tag/{tag}', '_bx_events');

-- category objects
INSERT INTO `sys_objects_categories` VALUES (NULL, 'bx_events', 'SELECT `Categories` FROM `[db_prefix]main` WHERE `ID` = {iID} AND `Status` = ''approved''', 'bx_events_permalinks', 'm/events/browse/category/{tag}', 'modules/?r=events/browse/category/{tag}', '_bx_events');

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES 
('Events', '0', 'bx_photos', '0', 'active'),
('Party', '0', 'bx_events', '0', 'active'),
('Expedition', '0', 'bx_events', '0', 'active'),
('Presentation', '0', 'bx_events', '0', 'active'),
('Last Friday', '0', 'bx_events', '0', 'active'),
('Birthday', '0', 'bx_events', '0', 'active'),
('Exhibition', '0', 'bx_events', '0', 'active'),
('Bushwalking', '0', 'bx_events', '0', 'active');

-- users actions
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''edit/{ID}'';', '0', 'bx_events'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', '1', 'bx_events'),
    ('{TitleJoin}', '{IconJoin}', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''join/{ID}/{iViewer}'';', '2', 'bx_events'),
    ('{TitleInvite}', 'plus-circle', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''invite/{ID}'';', '3', 'bx_events'),
    ('{TitleShare}', 'share-square-o', '', 'showPopupAnyHtml (''{BaseUri}share_popup/{ID}'');', '', '4', 'bx_events'),
    ('{TitleBroadcast}', 'envelope', '{BaseUri}broadcast/{ID}', '', '', '5', 'bx_events'),
    ('{AddToFeatured}', 'star-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''mark_featured/{ID}'';', 6, 'bx_events'),

    ('{TitleManageFans}', 'users', '', 'showPopupAnyHtml (''{BaseUri}manage_fans_popup/{ID}'');', '', '7', 'bx_events'),
    ('{TitleUploadPhotos}', 'picture-o', '{BaseUri}upload_photos/{URI}', '', '', '8', 'bx_events'),
    ('{TitleUploadVideos}', 'film', '{BaseUri}upload_videos/{URI}', '', '', '9', 'bx_events'),
    ('{TitleUploadSounds}', 'music', '{BaseUri}upload_sounds/{URI}', '', '', '10', 'bx_events'),
    ('{TitleUploadFiles}', 'save', '{BaseUri}upload_files/{URI}', '', '', '11', 'bx_events'),    
    ('{TitleSubscribe}', 'paperclip', '', '{ScriptSubscribe}', '', 12, 'bx_events'),
    ('{TitleActivate}', 'check-circle-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''activate/{ID}'';', '13', 'bx_events'),
    ('{repostCpt}', 'repeat', '', '{repostScript}', '', 14, 'bx_events'),

    ('{evalResult}', 'plus', '{BaseUri}browse/my&bx_events_filter=add_event', '', 'return ($GLOBALS[''logged''][''member''] && BxDolModule::getInstance(''BxEventsModule'')->isAllowedAdd()) || $GLOBALS[''logged''][''admin''] ? _t(''_bx_events_action_create_event'') : '''';', 1, 'bx_events_title'),
    ('{evalResult}', 'calendar', '{BaseUri}browse/my', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_events_action_my_events'') : '''';', '2', 'bx_events_title');
    
-- top menu
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 0, 'Events', '_bx_events_menu_root', 'modules/?r=events/view/|modules/?r=events/broadcast/|modules/?r=events/invite/|modules/?r=events/edit/|modules/?r=events/upload_photos/|modules/?r=events/upload_videos/|modules/?r=events/upload_sounds/|modules/?r=events/upload_files/', '', 'non,memb', '', '', '', 1, 1, 1, 'system', 'calendar', '', '0', '');
SET @iCatRoot := LAST_INSERT_ID();
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, @iCatRoot, 'Event View', '_bx_events_menu_view', 'modules/?r=events/view/{bx_events_view_uri}', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Event View Forum', '_bx_events_menu_view_forum', 'forum/events/forum/{bx_events_view_uri}-0.htm|forum/events/', 1, 'non,memb', '', '', '$oModuleDb = new BxDolModuleDb(); return $oModuleDb->getModuleByUri(''forum'') ? true : false;', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Event View Comments', '_bx_events_menu_view_comments', 'modules/?r=events/comments/{bx_events_view_uri}', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Event View Participants', '_bx_events_menu_view_participants', 'modules/?r=events/browse_participants/{bx_events_view_uri}', 3, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');


SET @iMaxMenuOrder := (SELECT `Order` + 1 FROM `sys_menu_top` WHERE `Parent` = 0 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 0, 'Events', '_bx_events_menu_root', 'modules/?r=events/home/|modules/?r=events/', @iMaxMenuOrder, 'non,memb', '', '', '', 1, 1, 1, 'top', 'calendar', 'calendar', 1, '');
SET @iCatRoot := LAST_INSERT_ID();
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, @iCatRoot, 'Events Main Page', '_bx_events_menu_main', 'modules/?r=events/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Upcoming Events', '_bx_events_menu_upcoming_events', 'modules/?r=events/browse/upcoming', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Past Events', '_bx_events_menu_past_events', 'modules/?r=events/browse/past', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Recently Added Events', '_bx_events_menu_recently_added', 'modules/?r=events/browse/recent', 3, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Top Rated Events', '_bx_events_menu_top_rated', 'modules/?r=events/browse/top', 4, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Popular Events', '_bx_events_menu_popular', 'modules/?r=events/browse/popular', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Featured Events', '_bx_events_menu_featured', 'modules/?r=events/browse/featured', 6, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Events Tags', '_bx_events_menu_tags', 'modules/?r=events/tags', 8, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, 'bx_events'),
(NULL, @iCatRoot, 'Events Categories', '_bx_events_menu_categories', 'modules/?r=events/categories', 9, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, 'bx_events'),
(NULL, @iCatRoot, 'Calendar', '_bx_events_menu_calendar', 'modules/?r=events/calendar', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(NULL, @iCatRoot, 'Search', '_bx_events_menu_search', 'modules/?r=events/search', 11, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 9, 'Events', '_bx_events_menu_my_events_profile', 'modules/?r=events/browse/user/{profileUsername}|modules/?r=events/browse/joined/{profileUsername}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(NULL, 4, 'Events', '_bx_events_menu_my_events_profile', 'modules/?r=events/browse/my', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');

-- member menu
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_events', `Eval` = 'return BxDolService::call(''events'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_events', '_bx_events', '{siteUrl}modules/?r=events/administration/', 'Events module by BoonEx', 'calendar', @iMax+1);

-- email templates
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('bx_events_invitation', 'Invitation To Event: <EventName>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<InviterUrl>"><InviterNickName></a> invited you to this event:</p>\r\n\r\n<hr>\r\n<pre>\r\n<InvitationText>\r\n</pre>\r\n<hr>  \r\n\r\n<p>\r\nEvent Information:<br /> \r\nName: <EventName><br /> \r\nLocation: <EventLocation><br /> \r\nTime: <EventStart><br /> <br />\r\n\r\n<a href="<EventUrl>">More details</a>\r\n</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event invitation', 0),

('bx_events_broadcast', '<BroadcastTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<EntryUrl>"><EntryTitle></a> event admin message:</p> <hr><BroadcastMessage><hr> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Broadcast', 0),

('bx_events_sbs', 'Subscription: Event Details Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<ViewLink>"><EntryTitle></a> event details changed: <br /> <ActionName> </p> \r\n<hr>\r\n<p>Cancel this subscription: <a href="<UnsubscribeLink>"><UnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Subscription', 0),

('bx_events_join_request', 'New Request To Join Your Event', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>New request to join your event: <a href="<EntryUrl>"><EntryTitle></a>.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New join request to an event', 0),

('bx_events_join_reject', 'Your Request To Join Event Was Rejected', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> \r\n\r\n<p>Your request to join <a href="<EntryUrl>"><EntryTitle></a> event was rejected by event admin.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Request To Join Event Was Rejected', 0),

('bx_events_join_confirm', 'Your Request To Join Event Was Approved', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>Congratulations! Your request to join <a href="<EntryUrl>"><EntryTitle></a> event was approved by the event admin.</p>\r\n \r\n<bx_include_auto:_email_footer.html />', 'Request To Join Event Approved', 0),

('bx_events_fan_remove', 'You Were Removed From Event Participants', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> <p>You were removed from participants of <a href="<EntryUrl>"><EntryTitle></a> event by the event admin.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Removed From Event Participants', 0),

('bx_events_fan_become_admin', 'You Are The Event Admin Now', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> \r\n<p>You are an admin of <a href="<EntryUrl>"><EntryTitle></a> event now.\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Admin Status Granted', 0),

('bx_events_admin_become_fan', 'You Are No Longer The Event Admin', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> <p>Your admin status was revoked from <a href="<EntryUrl>"><EntryTitle></a> event by the event creator.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event admin status revoked.', 0);


-- site stats
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'evs', 'bx_events', 'modules/?r=events/browse/recent', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `Status`=''approved''', 'modules/?r=events/administration', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `Status`=''pending''', 'calendar', @iStatSiteOrder);

-- PQ statistics
INSERT INTO `sys_stat_member` VALUES ('bx_events', 'SELECT COUNT(*) FROM `[db_prefix]main` WHERE `ResponsibleID` = ''__member_id__'' AND `Status`=''approved''');
INSERT INTO `sys_stat_member` VALUES ('bx_eventsp', 'SELECT COUNT(*) FROM `[db_prefix]main` WHERE `ResponsibleID` = ''__member_id__'' AND `Status`!=''approved''');
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_events', '__bx_events__ (<a href="modules/?r=events/browse/my&bx_events_filter=add_event">__l_add__</a>)');

-- membership actions
SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` VALUES (NULL, 'events view', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'events browse', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'events search', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'events add', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'events comments delete and edit', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'events edit any event', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'events delete any event', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'events mark as featured', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'events approve', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'events broadcast message', NULL);

-- alert handlers
INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_profile_delete', '', '', 'BxDolService::call(''events'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'profile', 'delete', @iHandler);

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_media_delete', '', '', 'BxDolService::call(''events'', ''response_media_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_photos', 'delete', @iHandler);
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_videos', 'delete', @iHandler);
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_sounds', 'delete', @iHandler);
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_files', 'delete', @iHandler);

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_map_install', '', '', 'if (''wmap'' == $this->aExtras[''uri''] && $this->aExtras[''res''][''result'']) BxDolService::call(''events'', ''map_install'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'module', 'install', @iHandler);

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_set_param', '', '', 'if (''bx_events_only_upcoming_events_on_map'' == $this->aExtras[''name'']) BxDolService::call(''events'', ''set_upcoming_events_on_map'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'system', 'set_param', @iHandler);

-- privacy
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('events', 'view_event', '_bx_events_privacy_view_event', '3'),
('events', 'join', '_bx_events_privacy_join', '3'),
('events', 'comment', '_bx_events_privacy_comment', '3'),
('events', 'rate', '_bx_events_privacy_rate', '3'),
('events', 'view_participants', '_bx_events_privacy_view_participants', '3'),
('events', 'post_in_forum', '_bx_events_privacy_post_in_forum', 'p'),
('events', 'view_forum', '_bx_events_privacy_view_forum', 'p'),
('events', 'upload_photos', '_bx_events_privacy_upload_photos', 'a'),
('events', 'upload_videos', '_bx_events_privacy_upload_videos', 'a'),
('events', 'upload_sounds', '_bx_events_privacy_upload_sounds', 'a'),
('events', 'upload_files', '_bx_events_privacy_upload_files', 'a');

-- subscriptions
INSERT INTO `sys_sbs_types` (`unit`, `action`, `template`, `params`) VALUES
('bx_events', '', '', 'return BxDolService::call(''events'', ''get_subscription_params'', array($arg2, $arg3));'),
('bx_events', 'change', 'bx_events_sbs', 'return BxDolService::call(''events'', ''get_subscription_params'', array($arg2, $arg3));'),
('bx_events', 'commentPost', 'bx_events_sbs', 'return BxDolService::call(''events'', ''get_subscription_params'', array($arg2, $arg3));'),
('bx_events', 'join', 'bx_events_sbs', 'return BxDolService::call(''events'', ''get_subscription_params'', array($arg2, $arg3));');

-- sitemap
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_events', '_bx_events', '0.8', 'auto', 'BxEventsSiteMaps', 'modules/boonex/events/classes/BxEventsSiteMaps.php', @iMaxOrderSiteMaps, 1);

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_events', '_bx_events', 'bx_events_main', 'Date', '', '', 1, @iMaxOrderCharts);

