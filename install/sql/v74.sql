--
-- Database: v 7.4
--

-- --------------------------------------------------------

SET NAMES 'utf8';
DROP TABLE IF EXISTS `sys_account_custom_stat_elements`, `sys_admin_ban_list`, `sys_menu_admin`, `sys_menu_admin_top`, `sys_menu_bottom`, `sys_menu_service`, `sys_albums`, `sys_albums_objects`,`sys_banners`, `sys_banners_clicks`, `sys_banners_shows`, `sys_block_list`, `sys_categories`, `sys_objects_categories`, `sys_objects_cmts`, `sys_cmts_profile`, `sys_cmts_track`, `sys_color_base`, `sys_countries`, `sys_email_templates`, `sys_menu_member`, `sys_friend_list`, `sys_options`, `sys_options_cats`, `sys_fave_list`, `sys_ip_list`, `sys_ip_members_visits`, `sys_localization_categories`, `sys_localization_keys`, `sys_localization_languages`, `sys_localization_string_params`, `sys_localization_strings`, `sys_acl_actions`, `sys_acl_actions_track`, `sys_acl_matrix`, `sys_acl_level_prices`, `sys_acl_levels`, `sys_messages`, `sys_page_compose`, `sys_page_compose_pages`, `sys_page_compose_privacy`, `sys_sessions`, `sys_stat_member`, `sys_pre_values`, `sys_profile_fields`, `sys_acl_levels_members`, `Profiles`, `sys_profile_views_track`, `sys_profiles_match`, `sys_profile_rating`, `sys_profile_voting_track`, `RayBoardCurrentUsers`, `RayBoardBoards`, `RayBoardUsers`, `RayChatCurrentUsers`, `RayChatMessages`, `RayChatHistory`, `RayChatProfiles`, `RayChatRooms`, `RayChatRoomsUsers`, `RayChatMembershipsSettings`, `RayChatMemberships`, `RayImContacts`, `RayImMessages`, `RayImPendings`, `RayImProfiles`, `RayMp3Files`, `RayMp3Tokens`, `RayShoutboxMessages`, `RayVideoFiles`, `RayVideoTokens`, `RayVideo_commentsFiles`, `RayVideo_commentsTokens`, `sys_objects_search`, `sys_shared_sites`, `sys_stat_site`, `sys_alerts`, `sys_alerts_handlers`, `sys_injections`, `sys_injections_admin`, `sys_modules`, `sys_modules_file_tracks`, `sys_permalinks`, `sys_privacy_actions`, `sys_privacy_defaults`, `sys_privacy_groups`, `sys_privacy_members`, `sys_tags`, `sys_objects_tag`, `sys_menu_top`, `sys_objects_actions`, `sys_objects_auths`, `sys_greetings`, `sys_objects_vote`, `sys_objects_views`, `sys_box_download`, `sys_cron_jobs`, `sys_sbs_users`, `sys_sbs_entries`, `sys_sbs_types`, `sys_sbs_queue`, `sys_sbs_messages`, `sys_profiles_match_mails`, `sys_dnsbl_rules`, `sys_dnsbl_block_log`, `sys_dnsbluri_zones`, `sys_antispam_block_log`, `sys_menu_mobile`, `sys_menu_mobile_pages`, `sys_objects_social_sharing`, `sys_objects_site_maps`, `sys_objects_charts`, `sys_objects_captcha`, `sys_objects_editor`, `sys_objects_exports`;
ALTER DATABASE DEFAULT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';

-- --------------------------------------------------------

--
-- Table structure for table `sys_account_custom_stat_elements`
--

CREATE TABLE `sys_account_custom_stat_elements` (
  `ID` int(2) NOT NULL auto_increment,
  `Label` varchar(128) NOT NULL,
  `Value` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `sys_admin_ban_list`
--

CREATE TABLE `sys_admin_ban_list` (
  `ProfID` int(10) unsigned NOT NULL default '0',
  `Time` int(10) unsigned NOT NULL default '0',
  `DateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`ProfID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_admin`
--

CREATE TABLE `sys_menu_admin` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(32) NOT NULL default '',
  `title` varchar(64) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',  
  `icon` varchar(128) NOT NULL default '',
  `icon_large` varchar(128) NOT NULL default '',
  `check` varchar(255) NOT NULL default '',
  `order` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_menu_admin`
--

-- Users menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'users', '_adm_mmi_users', '{siteAdminUrl}profiles.php', '', 'users col-green1', 'users', '', 2);
SET @iParentId = LAST_INSERT_ID();

-- Extensions menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'modules', '_adm_mmi_modules', '', '', 'puzzle-piece col-red1', 'puzzle-piece', '', 3);
SET @iParentId = LAST_INSERT_ID();

INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'manage_modules', '_adm_mmi_manage_modules', '{siteAdminUrl}modules.php', 'Manage and configure integration modules for 3d party scripts', 'plus col-red1', '', '', 1),
(@iParentId, 'flash_apps', '_adm_mmi_flash_apps', '{siteAdminUrl}flash.php', 'Flash Apps administration panel is available here', 'bolt col-red1', '', '', 2);

-- Tools menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'tools', '_adm_mmi_tools', '', '', 'wrench col-green3', 'wrench', '', 4);
SET @iParentId = LAST_INSERT_ID();

INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'mass_mailer', '_adm_mmi_mass_mailer', '{siteAdminUrl}notifies.php', 'Using this function you are able to send a newsletter to your site members', 'envelope col-green3', '', '', 1),
(@iParentId, 'banners', '_adm_mmi_banners', '{siteAdminUrl}banners.php', 'Provides you with the ability to manage banners on your web site', 'flag col-green3', '', '', 4),
(@iParentId, 'ip_blacklist', '_adm_mmi_ip_blacklist', '{siteAdminUrl}ip_blacklist.php', 'IP Blacklist system', 'ban col-green3', '', '', 6),
(@iParentId, 'database_backup', '_adm_mmi_database_backup', '{siteAdminUrl}db.php', 'Make a backup of your site database with this utility', 'download col-green3', '', '', 7),
(@iParentId, 'host_tools', '_adm_mmi_host_tools', '{siteAdminUrl}host_tools.php', 'Admin Host Tools', 'hdd-o col-green3', '', '', 8),
(@iParentId, 'antispam', '_adm_mmi_antispam', '{siteAdminUrl}antispam.php', 'Antispam Tools', 'gavel col-green3', '', '', 9),
(@iParentId, 'sitemap', '_adm_mmi_sitemap', '{siteAdminUrl}sitemap.php', 'Sitemap', 'sitemap col-green3', '', '', 10),
(@iParentId, 'cache', '_adm_mmi_cache', '{siteAdminUrl}cache.php', 'Cache', 'bolt col-green3', '', '', 11);

-- Builders menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'builders', '_adm_mmi_builders', '', '', 'magic col-red2', 'magic', '', 5);
SET @iParentId = LAST_INSERT_ID();

INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'navigation_menu', '_adm_mmi_navigation_menu', '{siteAdminUrl}nav_menu_compose.php', 'For top menu items management', 'list col-red2', '', '', 1),
(@iParentId, 'service_menu', '_adm_mmi_service_menu', '{siteAdminUrl}service_menu_compose.php', 'For top service''s menu items management', 'list col-red2', '', '', 2),
(@iParentId, 'bottom_menu', '_adm_mmi_bottom_menu', '{siteAdminUrl}bottom_menu_compose.php', 'For top bottom''s menu items management', 'list col-red2', '', '', 3),
(@iParentId, 'member_menu', '_adm_mmi_member_menu', '{siteAdminUrl}member_menu_compose.php', 'For top member''s menu items management', 'list col-red2', '', '', 4),
(@iParentId, 'admin_menu', '_adm_mmi_admin_menu', '{siteAdminUrl}menu_compose_admin.php', 'For top admin''s menu items management', 'list col-red2', '', '', 5),
(@iParentId, 'profile_fields', '_adm_mmi_profile_fields', '{siteAdminUrl}fields.php', 'For member profile fields management', 'list-alt col-red2', '', '', 10),
(@iParentId, 'pages_blocks', '_adm_mmi_pages_blocks', '{siteAdminUrl}pageBuilder.php', 'Compose blocks for the site pages here', 'th-large col-red2', '', '', 20),
(@iParentId, 'mobile_pages', '_adm_mmi_mobile_pages', '{siteAdminUrl}mobileBuilder.php', 'Mobile pages builder', 'th col-red2', '', '', 21),
(@iParentId, 'predefined_values', '_adm_mmi_predefined_values', '{siteAdminUrl}preValues.php', '', 'list-ol col-red2', '', '', 30);

-- Settings menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'settings', '_adm_mmi_settings', '', '', 'sliders col-blue2', 'sliders', '', 6);
SET @iParentId = LAST_INSERT_ID();

INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'admin_password', '_adm_mmi_admin_password', '{siteAdminUrl}settings.php?cat=ap', 'Change a password for access to administration panel here', 'user-secret col-blue2', '', '', 1),
(@iParentId, 'basic_settings', '_adm_mmi_basic_settings', '{siteAdminUrl}basic_settings.php', 'For managing site system settings', 'cog col-blue2', '', '', 2),
(@iParentId, 'advanced_settings', '_adm_mmi_advanced_settings', '{siteAdminUrl}advanced_settings.php', 'More enhanced settings for your site features', 'cogs col-blue2', '', '', 3),
(@iParentId, 'languages_settings', '_adm_mmi_languages_settings', '{siteAdminUrl}lang_file.php', 'For languages management your website is using and making changes in your website content', 'language col-blue2', '', '', 4),
(@iParentId, 'membership_levels', '_adm_mmi_membership_levels', '{siteAdminUrl}memb_levels.php', 'For setting up different membership levels, different actions for each membership level and action limits', 'star-o col-blue2', '', '', 5),
(@iParentId, 'email_templates', '_adm_mmi_email_templates', '{siteAdminUrl}email_templates.php', 'For setting up email texts which are sent from your website to members automatically', 'clipboard col-blue2', '', '', 6),
(@iParentId, 'templates', '_adm_mmi_templates', '{siteAdminUrl}templates.php', 'Templates management', 'eye col-blue2', '', '', 7),
(@iParentId, 'privacy', '_adm_mmi_privacy', '{siteAdminUrl}privacy.php', 'Privacy settings', 'lock col-blue2', '', '', 8),
(@iParentId, 'categories_settings', '_adm_mmi_categories_settings', '{siteAdminUrl}categories.php', 'Categories settings', 'folder col-blue2', '', '', 15),
(@iParentId, 'watermark', '_adm_mmi_watermark', '{siteAdminUrl}settings.php?cat=16', 'Setting up watermark for media content', 'certificate col-blue2', '', '', 16);

-- Dashboard menu item
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'dashboard', '_adm_mmi_dashboard', '{siteAdminUrl}index.php', '', 'tachometer col-blue3', 'tachometer', '', 1);
SET @iParentId = LAST_INSERT_ID();

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_admin_top`
--

CREATE TABLE `sys_menu_admin_top` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `caption` varchar(64) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `target` varchar(64) NOT NULL default '',
  `icon` varchar(128) NOT NULL default '',
  `order` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_menu_admin_top`
--

INSERT INTO `sys_menu_admin_top`(`name`, `caption`, `url`, `target`, `icon`, `order`) VALUES
('home', '_adm_tmi_home', '{site_url}index.php', '_blank', 'external-link-square', 1),
('extensions', '_adm_tmi_extensions', 'https://www.boonex.com/market', '', 'puzzle-piece', 2),
('info', '_adm_tmi_info', 'https://www.boonex.com/trac/dolphin/wiki', '', 'question-circle', 3),
('logout', '_adm_tmi_logout', '{site_url}logout.php', '', 'sign-out', 4);

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_service`
--
CREATE TABLE `sys_menu_service` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(200) NOT NULL,
  `Caption` varchar(100) NOT NULL,
  `Icon` varchar(100) NOT NULL,
  `Link` varchar(250) NOT NULL,
  `Script` varchar(250) NOT NULL,
  `Target` varchar(200) NOT NULL,
  `Order` int(5) NOT NULL,
  `Visible` set('non','memb') NOT NULL DEFAULT '',
  `Active` tinyint(1) NOT NULL DEFAULT '1',
  `Movable` tinyint(1) NOT NULL DEFAULT '1',
  `Clonable` tinyint(1) NOT NULL DEFAULT '1',
  `Editable` tinyint(1) NOT NULL DEFAULT '1',
  `Deletable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_menu_bottom`
--
INSERT INTO `sys_menu_service` (`Name`, `Caption`, `Icon`, `Link`, `Script`, `Target`, `Order`, `Visible`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`) VALUES
('Join', '_sys_sm_join', 'user', '', 'showPopupJoinForm(); return false;', '', 1, 'non', 1, 3, 1, 1, 1),
('Login', '_sys_sm_login', 'sign-in', '', 'showPopupLoginForm(); return false;', '', 2, 'non', 1, 3, 1, 1, 1),
('LoginOnly', '_sys_sm_login', 'sign-in', '', 'showPopupLoginOnlyForm(); return false;', '', 0, 'non', 0, 3, 1, 1, 1),
('Profile', '_sys_sm_profile', '', '{memberLink}|{memberNick}|change_status.php', '', '', 0, 'memb', 0, 3, 1, 1, 1),
('Account', '_sys_sm_account', 'tachometer', 'member.php', '', '', 1, 'memb', 1, 3, 1, 1, 1),
('ProfileSettings', '_sys_sm_profile_settings', 'cog', 'pedit.php?ID={memberID}', '', '', 2, 'memb', 1, 3, 1, 1, 1),
('Logout', '_sys_sm_logout', 'sign-out', 'logout.php?action=member_logout', '', '', 3, 'memb', 1, 3, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_bottom`
--
CREATE TABLE `sys_menu_bottom` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Caption` varchar(100) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Icon` varchar(100) NOT NULL,
  `Link` varchar(250) NOT NULL,
  `Script` varchar(250) NOT NULL,
  `Order` int(5) NOT NULL,
  `Target` varchar(200) NOT NULL,
  `Visible` set('non','memb') NOT NULL DEFAULT '',
  `Active` tinyint(1) NOT NULL DEFAULT '1',
  `Movable` tinyint(1) NOT NULL DEFAULT '1',
  `Clonable` tinyint(1) NOT NULL DEFAULT '1',
  `Editable` tinyint(1) NOT NULL DEFAULT '1',
  `Deletable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_menu_bottom`
--
INSERT INTO `sys_menu_bottom` (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Order`, `Target`, `Visible`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`) VALUES
('_About Us', 'About Us', '', 'about_us.php', '', 1, '', 'non,memb', 1, 3, 1, 1, 1),
('_Privacy', 'Privacy', '', 'privacy.php', '', 2, '', 'non,memb', 1, 3, 1, 1, 1),
('_Terms_of_use', 'Terms of use', '', 'terms_of_use.php', '', 3, '', 'non,memb', 1, 3, 1, 1, 1),
('_FAQ', 'FAQ', '', 'faq.php', '', 4, '', 'non,memb', 1, 3, 1, 1, 1),
('_Invite a friend', 'Invite a friend', '', 'tellfriend.php', 'return launchTellFriend();', 5, '', 'non,memb', 1, 3, 1, 1, 1),
('_contact_us', 'Contact us', '', 'contact.php', '', 6, '', 'non,memb', 1, 3, 1, 1, 1),
('_Bookmark', 'Bookmark', '', '', 'addBookmark(); return false;', 7, '', 'non,memb', 1, 3, 1, 1, 1);

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `sys_albums`
--

CREATE TABLE `sys_albums` (
    `ID` int(10) NOT NULL auto_increment,
    `Caption` varchar(128) NOT NULL default '',
    `Uri` varchar(255) NOT NULL default '',
    `Location` varchar(128) NOT NULL default '',
    `Description` varchar(255) NOT NULL default '',
    `Type` varchar(20) NOT NULL default '',
    `Owner` int(10) NOT NULL default '0',
    `Status` enum('active', 'passive') NOT NULL default 'active',
    `Date` int(10) NOT NULL default '0',
    `ObjCount` int(10) NOT NULL default '0',
    `LastObjId` int(10) NOT NULL default '0',
    `AllowAlbumView` int(10) NOT NULL default '3',
    PRIMARY KEY (`ID`),
    UNIQUE KEY (`Uri`, `Type`, `Owner`),
    KEY `Owner` (`Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_albums`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_albums_objects`
--

CREATE TABLE `sys_albums_objects` (
    `id_album` int(10) NOT NULL,
    `id_object` int(10) NOT NULL,
    `obj_order` int(10) NOT NULL default '0',
    UNIQUE KEY (`id_album`, `id_object`),
    KEY `id_object` (`id_object`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_albums_objects`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_banners`
--

CREATE TABLE `sys_banners` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `Title` varchar(32) NOT NULL default '',
  `Url` varchar(255) NOT NULL default '',
  `Text` mediumtext NOT NULL,
  `Active` tinyint(4) NOT NULL default '0',
  `Created` date NOT NULL default '0000-00-00',
  `campaign_start` date NOT NULL default '2005-01-01',
  `campaign_end` date NOT NULL default '2007-01-01',
  `Position` varchar(10) NOT NULL default '4',
  `lhshift` int(5) NOT NULL default '-200',
  `lvshift` int(5) NOT NULL default '-750',
  `rhshift` int(5) NOT NULL default '100',
  `rvshift` int(5) NOT NULL default '-750',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dumping data for table `sys_banners`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_banners_clicks`
--

CREATE TABLE `sys_banners_clicks` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Date` int(10) NOT NULL default '0',
  `IP` varchar(16) NOT NULL default '',
  UNIQUE KEY `ID_2` (`ID`,`Date`,`IP`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_banners_clicks`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_banners_shows`
--

CREATE TABLE `sys_banners_shows` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Date` int(10) NOT NULL default '0',
  `IP` varchar(16) NOT NULL default '',
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_banners_shows`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_block_list`
--

CREATE TABLE `sys_block_list` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `When` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `BlockPair` (`ID`,`Profile`),
  KEY `ID` (`ID`),
  KEY `Profile` (`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_block_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_categories`
--

CREATE TABLE `sys_categories` (
  `Category` varchar(32) NOT NULL default '',
  `ID` int(10) unsigned NOT NULL default '0',
  `Type` varchar(20) NOT NULL default 'photo',
  `Owner` int(10) unsigned NOT NULL,
  `Status` enum('active', 'passive') NOT NULL default 'active',
  `Date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`Category`, `ID`, `Type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_categories`
--

-- --------------------------------------------------------


--
-- Table structure for table `sys_objects_auths`
--

CREATE TABLE `sys_objects_auths` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(64) NOT NULL,
  `Title` varchar(128) NOT NULL,
  `Link` varchar(255) NOT NULL,
  `OnClick` varchar(255) NOT NULL,
  `Icon` varchar(64) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Table structure for table `sys_objects_categories`
--

CREATE TABLE `sys_objects_categories` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ObjectName` varchar(50) NOT NULL,
  `Query` text  NOT NULL,
  `PermalinkParam` varchar(50) NOT NULL default '',
  `EnabledPermalink` varchar(100) NOT NULL default '',
  `DisabledPermalink` varchar(100) NOT NULL default '',
  `LangKey` varchar(100) NOT NULL default '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_objects_categories`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_cmts_profile`
--

CREATE TABLE `sys_cmts_profile` (
  `cmt_id` int(11) NOT NULL auto_increment,
  `cmt_parent_id` int(11) NOT NULL default '0',
  `cmt_object_id` int(11) NOT NULL default '0',
  `cmt_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL,
  `cmt_mood` tinyint NOT NULL DEFAULT '0',
  `cmt_rate` int(11) NOT NULL default '0',
  `cmt_rate_count` int(11) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_id`),
  KEY `cmt_object_id` (`cmt_object_id`,`cmt_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_cmts_profile`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_cmts_track`
--

CREATE TABLE `sys_cmts_track` (
  `cmt_system_id` int(11) NOT NULL default '0',
  `cmt_id` int(11) NOT NULL default '0',
  `cmt_rate` tinyint(4) NOT NULL default '0',
  `cmt_rate_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_rate_author_nip` int(11) unsigned NOT NULL default '0',
  `cmt_rate_ts` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_system_id`,`cmt_id`,`cmt_rate_author_nip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_cmts_track`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_objects_cmts`
--

CREATE TABLE `sys_objects_cmts` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ObjectName` varchar(50) NOT NULL,
  `TableCmts` varchar(50) NOT NULL,
  `TableTrack` varchar(50) NOT NULL,
  `AllowTags` smallint(1) NOT NULL,
  `Nl2br` smallint(1) NOT NULL,
  `SecToEdit` smallint(6) NOT NULL,
  `PerView` smallint(6) NOT NULL,
  `IsRatable` smallint(1) NOT NULL,
  `ViewingThreshold` smallint(6) NOT NULL,
  `AnimationEffect` varchar(50) NOT NULL,
  `AnimationSpeed` smallint(6) NOT NULL,
  `IsOn` smallint(1) NOT NULL,
  `IsMood` smallint(1) NOT NULL,
  `RootStylePrefix` varchar(16) NOT NULL default 'cmt',
  `TriggerTable` varchar(32) NOT NULL,
  `TriggerFieldId` varchar(32) NOT NULL,
  `TriggerFieldComments` varchar(32) NOT NULL,
  `ClassName` varchar(32) NOT NULL,
  `ClassFile` varchar(256) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_objects_cmts`
--

INSERT INTO `sys_objects_cmts` VALUES(1, 'profile', 'sys_cmts_profile', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', 'Profiles', 'ID', 'CommentsCount', 'BxDolCmtsProfile', 'inc/classes/BxDolCmtsProfile.php');

--
-- Table structure for table `sys_color_base`
--

CREATE TABLE `sys_color_base` (
  `ColorName` varchar(20) NOT NULL default '',
  `ColorCode` varchar(10) NOT NULL default '',
  UNIQUE KEY `ColorName` (`ColorName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_color_base`
--

INSERT INTO `sys_color_base` VALUES('AliceBlue', '#F0F8FF');
INSERT INTO `sys_color_base` VALUES('AntiqueWhite', '#FAEBD7');
INSERT INTO `sys_color_base` VALUES('Aqua', '#00FFFF');
INSERT INTO `sys_color_base` VALUES('Aquamarine', '#7FFFD4');
INSERT INTO `sys_color_base` VALUES('Azure', '#F0FFFF');
INSERT INTO `sys_color_base` VALUES('Beige', '#F5F5DC');
INSERT INTO `sys_color_base` VALUES('Bisque', '#FFE4C4');
INSERT INTO `sys_color_base` VALUES('Black', '#000000');
INSERT INTO `sys_color_base` VALUES('BlanchedAlmond', '#FFEBCD');
INSERT INTO `sys_color_base` VALUES('Blue', '#0000FF');
INSERT INTO `sys_color_base` VALUES('BlueViolet', '#8A2BE2');
INSERT INTO `sys_color_base` VALUES('Brown', '#A52A2A');
INSERT INTO `sys_color_base` VALUES('BurlyWood', '#DEB887');
INSERT INTO `sys_color_base` VALUES('CadetBlue', '#5F9EA0');
INSERT INTO `sys_color_base` VALUES('Chartreuse', '#7FFF00');
INSERT INTO `sys_color_base` VALUES('Chocolate', '#D2691E');
INSERT INTO `sys_color_base` VALUES('Coral', '#FF7F50');
INSERT INTO `sys_color_base` VALUES('CornflowerBlue', '#6495ED');
INSERT INTO `sys_color_base` VALUES('Cornsilk', '#FFF8DC');
INSERT INTO `sys_color_base` VALUES('Crimson', '#DC143C');
INSERT INTO `sys_color_base` VALUES('Cyan', '#00FFFF');
INSERT INTO `sys_color_base` VALUES('DarkBlue', '#00008B');
INSERT INTO `sys_color_base` VALUES('DarkCyan', '#008B8B');
INSERT INTO `sys_color_base` VALUES('DarkGoldenRod', '#B8860B');
INSERT INTO `sys_color_base` VALUES('DarkGray', '#A9A9A9');
INSERT INTO `sys_color_base` VALUES('DarkGreen', '#006400');
INSERT INTO `sys_color_base` VALUES('DarkKhaki', '#BDB76B');
INSERT INTO `sys_color_base` VALUES('DarkMagenta', '#8B008B');
INSERT INTO `sys_color_base` VALUES('DarkOliveGreen', '#556B2F');
INSERT INTO `sys_color_base` VALUES('Darkorange', '#FF8C00');
INSERT INTO `sys_color_base` VALUES('DarkOrchid', '#9932CC');
INSERT INTO `sys_color_base` VALUES('DarkRed', '#8B0000');
INSERT INTO `sys_color_base` VALUES('DarkSalmon', '#E9967A');
INSERT INTO `sys_color_base` VALUES('DarkSeaGreen', '#8FBC8F');
INSERT INTO `sys_color_base` VALUES('DarkSlateBlue', '#483D8B');
INSERT INTO `sys_color_base` VALUES('DarkSlateGray', '#2F4F4F');
INSERT INTO `sys_color_base` VALUES('DarkTurquoise', '#00CED1');
INSERT INTO `sys_color_base` VALUES('DarkViolet', '#9400D3');
INSERT INTO `sys_color_base` VALUES('DeepPink', '#FF1493');
INSERT INTO `sys_color_base` VALUES('DeepSkyBlue', '#00BFFF');
INSERT INTO `sys_color_base` VALUES('DimGray', '#696969');
INSERT INTO `sys_color_base` VALUES('DodgerBlue', '#1E90FF');
INSERT INTO `sys_color_base` VALUES('Feldspar', '#D19275');
INSERT INTO `sys_color_base` VALUES('FireBrick', '#B22222');
INSERT INTO `sys_color_base` VALUES('FloralWhite', '#FFFAF0');
INSERT INTO `sys_color_base` VALUES('ForestGreen', '#228B22');
INSERT INTO `sys_color_base` VALUES('Fuchsia', '#FF00FF');
INSERT INTO `sys_color_base` VALUES('Gainsboro', '#DCDCDC');
INSERT INTO `sys_color_base` VALUES('GhostWhite', '#F8F8FF');
INSERT INTO `sys_color_base` VALUES('Gold', '#FFD700');
INSERT INTO `sys_color_base` VALUES('GoldenRod', '#DAA520');
INSERT INTO `sys_color_base` VALUES('Gray', '#808080');
INSERT INTO `sys_color_base` VALUES('Green', '#008000');
INSERT INTO `sys_color_base` VALUES('GreenYellow', '#ADFF2F');
INSERT INTO `sys_color_base` VALUES('HoneyDew', '#F0FFF0');
INSERT INTO `sys_color_base` VALUES('HotPink', '#FF69B4');
INSERT INTO `sys_color_base` VALUES('IndianRed', '#CD5C5C');
INSERT INTO `sys_color_base` VALUES('Indigo', '#4B0082');
INSERT INTO `sys_color_base` VALUES('Ivory', '#FFFFF0');
INSERT INTO `sys_color_base` VALUES('Khaki', '#F0E68C');
INSERT INTO `sys_color_base` VALUES('Lavender', '#E6E6FA');
INSERT INTO `sys_color_base` VALUES('LavenderBlush', '#FFF0F5');
INSERT INTO `sys_color_base` VALUES('LawnGreen', '#7CFC00');
INSERT INTO `sys_color_base` VALUES('LemonChiffon', '#FFFACD');
INSERT INTO `sys_color_base` VALUES('LightBlue', '#ADD8E6');
INSERT INTO `sys_color_base` VALUES('LightCoral', '#F08080');
INSERT INTO `sys_color_base` VALUES('LightCyan', '#E0FFFF');
INSERT INTO `sys_color_base` VALUES('LightGoldenRodYellow', '#FAFAD2');
INSERT INTO `sys_color_base` VALUES('LightGrey', '#D3D3D3');
INSERT INTO `sys_color_base` VALUES('LightGreen', '#90EE90');
INSERT INTO `sys_color_base` VALUES('LightPink', '#FFB6C1');
INSERT INTO `sys_color_base` VALUES('LightSalmon', '#FFA07A');
INSERT INTO `sys_color_base` VALUES('LightSeaGreen', '#20B2AA');
INSERT INTO `sys_color_base` VALUES('LightSkyBlue', '#87CEFA');
INSERT INTO `sys_color_base` VALUES('LightSlateBlue', '#8470FF');
INSERT INTO `sys_color_base` VALUES('LightSlateGray', '#778899');
INSERT INTO `sys_color_base` VALUES('LightSteelBlue', '#B0C4DE');
INSERT INTO `sys_color_base` VALUES('LightYellow', '#FFFFE0');
INSERT INTO `sys_color_base` VALUES('Lime', '#00FF00');
INSERT INTO `sys_color_base` VALUES('LimeGreen', '#32CD32');
INSERT INTO `sys_color_base` VALUES('Linen', '#FAF0E6');
INSERT INTO `sys_color_base` VALUES('Magenta', '#FF00FF');
INSERT INTO `sys_color_base` VALUES('Maroon', '#800000');
INSERT INTO `sys_color_base` VALUES('MediumAquaMarine', '#66CDAA');
INSERT INTO `sys_color_base` VALUES('MediumBlue', '#0000CD');
INSERT INTO `sys_color_base` VALUES('MediumOrchid', '#BA55D3');
INSERT INTO `sys_color_base` VALUES('MediumPurple', '#9370D8');
INSERT INTO `sys_color_base` VALUES('MediumSeaGreen', '#3CB371');
INSERT INTO `sys_color_base` VALUES('MediumSlateBlue', '#7B68EE');
INSERT INTO `sys_color_base` VALUES('MediumSpringGreen', '#00FA9A');
INSERT INTO `sys_color_base` VALUES('MediumTurquoise', '#48D1CC');
INSERT INTO `sys_color_base` VALUES('MediumVioletRed', '#C71585');
INSERT INTO `sys_color_base` VALUES('MidnightBlue', '#191970');
INSERT INTO `sys_color_base` VALUES('MintCream', '#F5FFFA');
INSERT INTO `sys_color_base` VALUES('MistyRose', '#FFE4E1');
INSERT INTO `sys_color_base` VALUES('Moccasin', '#FFE4B5');
INSERT INTO `sys_color_base` VALUES('NavajoWhite', '#FFDEAD');
INSERT INTO `sys_color_base` VALUES('Navy', '#000080');
INSERT INTO `sys_color_base` VALUES('OldLace', '#FDF5E6');
INSERT INTO `sys_color_base` VALUES('Olive', '#808000');
INSERT INTO `sys_color_base` VALUES('OliveDrab', '#6B8E23');
INSERT INTO `sys_color_base` VALUES('Orange', '#FFA500');
INSERT INTO `sys_color_base` VALUES('OrangeRed', '#FF4500');
INSERT INTO `sys_color_base` VALUES('Orchid', '#DA70D6');
INSERT INTO `sys_color_base` VALUES('PaleGoldenRod', '#EEE8AA');
INSERT INTO `sys_color_base` VALUES('PaleGreen', '#98FB98');
INSERT INTO `sys_color_base` VALUES('PaleTurquoise', '#AFEEEE');
INSERT INTO `sys_color_base` VALUES('PaleVioletRed', '#D87093');
INSERT INTO `sys_color_base` VALUES('PapayaWhip', '#FFEFD5');
INSERT INTO `sys_color_base` VALUES('PeachPuff', '#FFDAB9');
INSERT INTO `sys_color_base` VALUES('Peru', '#CD853F');
INSERT INTO `sys_color_base` VALUES('Pink', '#FFC0CB');
INSERT INTO `sys_color_base` VALUES('Plum', '#DDA0DD');
INSERT INTO `sys_color_base` VALUES('PowderBlue', '#B0E0E6');
INSERT INTO `sys_color_base` VALUES('Purple', '#800080');
INSERT INTO `sys_color_base` VALUES('Red', '#FF0000');
INSERT INTO `sys_color_base` VALUES('RosyBrown', '#BC8F8F');
INSERT INTO `sys_color_base` VALUES('RoyalBlue', '#4169E1');
INSERT INTO `sys_color_base` VALUES('SaddleBrown', '#8B4513');
INSERT INTO `sys_color_base` VALUES('Salmon', '#FA8072');
INSERT INTO `sys_color_base` VALUES('SandyBrown', '#F4A460');
INSERT INTO `sys_color_base` VALUES('SeaGreen', '#2E8B57');
INSERT INTO `sys_color_base` VALUES('SeaShell', '#FFF5EE');
INSERT INTO `sys_color_base` VALUES('Sienna', '#A0522D');
INSERT INTO `sys_color_base` VALUES('Silver', '#C0C0C0');
INSERT INTO `sys_color_base` VALUES('SkyBlue', '#87CEEB');
INSERT INTO `sys_color_base` VALUES('SlateBlue', '#6A5ACD');
INSERT INTO `sys_color_base` VALUES('SlateGray', '#708090');
INSERT INTO `sys_color_base` VALUES('Snow', '#FFFAFA');
INSERT INTO `sys_color_base` VALUES('SpringGreen', '#00FF7F');
INSERT INTO `sys_color_base` VALUES('SteelBlue', '#4682B4');
INSERT INTO `sys_color_base` VALUES('Tan', '#D2B48C');
INSERT INTO `sys_color_base` VALUES('Teal', '#008080');
INSERT INTO `sys_color_base` VALUES('Thistle', '#D8BFD8');
INSERT INTO `sys_color_base` VALUES('Tomato', '#FF6347');
INSERT INTO `sys_color_base` VALUES('Turquoise', '#40E0D0');
INSERT INTO `sys_color_base` VALUES('Violet', '#EE82EE');
INSERT INTO `sys_color_base` VALUES('VioletRed', '#D02090');
INSERT INTO `sys_color_base` VALUES('Wheat', '#F5DEB3');
INSERT INTO `sys_color_base` VALUES('White', '#FFFFFF');
INSERT INTO `sys_color_base` VALUES('WhiteSmoke', '#F5F5F5');
INSERT INTO `sys_color_base` VALUES('Yellow', '#FFFF00');
INSERT INTO `sys_color_base` VALUES('YellowGreen', '#9ACD32');

-- --------------------------------------------------------

--
-- Table structure for table `sys_countries`
--

CREATE TABLE `sys_countries` (
  `ISO2` varchar(2) NOT NULL default '',
  `ISO3` varchar(3) NOT NULL default '',
  `ISONo` smallint(3) NOT NULL default '0',
  `Country` varchar(100) NOT NULL default '',
  `Region` varchar(100) default NULL,
  `Currency` varchar(100) default NULL,
  `CurrencyCode` varchar(3) default NULL,
  PRIMARY KEY  (`ISO2`),
  KEY `CurrencyCode` (`CurrencyCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_countries`
--

INSERT INTO `sys_countries` VALUES('AD', 'AND', 20, 'Andorra', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('AE', 'ARE', 784, 'United Arab Emirates', 'Middle East', 'UAE Dirham', 'AED');
INSERT INTO `sys_countries` VALUES('AF', 'AFG', 4, 'Afghanistan', 'Asia', 'Afghani', 'AFA');
INSERT INTO `sys_countries` VALUES('AG', 'ATG', 28, 'Antigua and Barbuda', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('AI', 'AIA', 660, 'Anguilla', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('AL', 'ALB', 8, 'Albania', 'Europe', 'Lek', 'ALL');
INSERT INTO `sys_countries` VALUES('AM', 'ARM', 51, 'Armenia', 'Commonwealth of Independent States', 'Armenian Dram', 'AMD');
INSERT INTO `sys_countries` VALUES('AN', 'ANT', 530, 'Netherlands Antilles', 'Central America and the Caribbean', 'Netherlands Antillean guilder', 'ANG');
INSERT INTO `sys_countries` VALUES('AO', 'AGO', 24, 'Angola', 'Africa', 'Kwanza', 'AOA');
INSERT INTO `sys_countries` VALUES('AQ', 'ATA', 10, 'Antarctica', 'Antarctic Region', NULL, NULL);
INSERT INTO `sys_countries` VALUES('AR', 'ARG', 32, 'Argentina', 'South America', 'Argentine Peso', 'ARS');
INSERT INTO `sys_countries` VALUES('AS', 'ASM', 16, 'American Samoa', 'Oceania', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('AT', 'AUT', 40, 'Austria', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('AU', 'AUS', 36, 'Australia', 'Oceania', 'Australian dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('AW', 'ABW', 533, 'Aruba', 'Central America and the Caribbean', 'Aruban Guilder', 'AWG');
INSERT INTO `sys_countries` VALUES('AX', 'ALA', 248, 'Aland Islands', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('AZ', 'AZE', 31, 'Azerbaijan', 'Commonwealth of Independent States', 'Azerbaijani Manat', 'AZM');
INSERT INTO `sys_countries` VALUES('BA', 'BIH', 70, 'Bosnia and Herzegovina', 'Bosnia and Herzegovina, Europe', 'Convertible Marka', 'BAM');
INSERT INTO `sys_countries` VALUES('BB', 'BRB', 52, 'Barbados', 'Central America and the Caribbean', 'Barbados Dollar', 'BBD');
INSERT INTO `sys_countries` VALUES('BD', 'BGD', 50, 'Bangladesh', 'Asia', 'Taka', 'BDT');
INSERT INTO `sys_countries` VALUES('BE', 'BEL', 56, 'Belgium', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('BF', 'BFA', 854, 'Burkina Faso', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('BG', 'BGR', 100, 'Bulgaria', 'Europe', 'Lev', 'BGL');
INSERT INTO `sys_countries` VALUES('BH', 'BHR', 48, 'Bahrain', 'Middle East', 'Bahraini Dinar', 'BHD');
INSERT INTO `sys_countries` VALUES('BI', 'BDI', 108, 'Burundi', 'Africa', 'Burundi Franc', 'BIF');
INSERT INTO `sys_countries` VALUES('BJ', 'BEN', 204, 'Benin', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('BL', 'BLM', 652, 'Saint Barthelemy', 'Central America and the Caribbean', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('BM', 'BMU', 60, 'Bermuda', 'North America', 'Bermudian Dollar', 'BMD');
INSERT INTO `sys_countries` VALUES('BN', 'BRN', 96, 'Brunei Darussalam', 'Southeast Asia', 'Brunei Dollar', 'BND');
INSERT INTO `sys_countries` VALUES('BO', 'BOL', 68, 'Bolivia', 'South America', 'Boliviano', 'BOB');
INSERT INTO `sys_countries` VALUES('BR', 'BRA', 76, 'Brazil', 'South America', 'Brazilian Real', 'BRL');
INSERT INTO `sys_countries` VALUES('BS', 'BHS', 44, 'The Bahamas', 'Central America and the Caribbean', 'Bahamian Dollar', 'BSD');
INSERT INTO `sys_countries` VALUES('BT', 'BTN', 64, 'Bhutan', 'Asia', 'Ngultrum', 'BTN');
INSERT INTO `sys_countries` VALUES('BV', 'BVT', 74, 'Bouvet Island', 'Antarctic Region', 'Norwegian Krone', 'NOK');
INSERT INTO `sys_countries` VALUES('BW', 'BWA', 72, 'Botswana', 'Africa', 'Pula', 'BWP');
INSERT INTO `sys_countries` VALUES('BY', 'BLR', 112, 'Belarus', 'Commonwealth of Independent States', 'Belarussian Ruble', 'BYR');
INSERT INTO `sys_countries` VALUES('BZ', 'BLZ', 84, 'Belize', 'Central America and the Caribbean', 'Belize Dollar', 'BZD');
INSERT INTO `sys_countries` VALUES('CA', 'CAN', 124, 'Canada', 'North America', 'Canadian Dollar', 'CAD');
INSERT INTO `sys_countries` VALUES('CC', 'CCK', 166, 'Cocos (Keeling) Islands', 'Southeast Asia', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('CD', 'COD', 180, 'Congo, Democratic Republic of the', 'Africa', 'Franc Congolais', 'CDF');
INSERT INTO `sys_countries` VALUES('CF', 'CAF', 140, 'Central African Republic', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('CG', 'COG', 178, 'Congo, Republic of the', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('CH', 'CHE', 756, 'Switzerland', 'Europe', 'Swiss Franc', 'CHF');
INSERT INTO `sys_countries` VALUES('CI', 'CIV', 384, 'Cote d''Ivoire', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('CK', 'COK', 184, 'Cook Islands', 'Oceania', 'New Zealand Dollar', 'NZD');
INSERT INTO `sys_countries` VALUES('CL', 'CHL', 152, 'Chile', 'South America', 'Chilean Peso', 'CLP');
INSERT INTO `sys_countries` VALUES('CM', 'CMR', 120, 'Cameroon', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('CN', 'CHN', 156, 'China', 'Asia', 'Yuan Renminbi', 'CNY');
INSERT INTO `sys_countries` VALUES('CO', 'COL', 170, 'Colombia', 'South America, Central America and the Caribbean', 'Colombian Peso', 'COP');
INSERT INTO `sys_countries` VALUES('CR', 'CRI', 188, 'Costa Rica', 'Central America and the Caribbean', 'Costa Rican Colon', 'CRC');
INSERT INTO `sys_countries` VALUES('CU', 'CUB', 192, 'Cuba', 'Central America and the Caribbean', 'Cuban Peso', 'CUP');
INSERT INTO `sys_countries` VALUES('CV', 'CPV', 132, 'Cape Verde', 'World', 'Cape Verdean Escudo', 'CVE');
INSERT INTO `sys_countries` VALUES('CX', 'CXR', 162, 'Christmas Island', 'Southeast Asia', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('CY', 'CYP', 196, 'Cyprus', 'Middle East', 'Cyprus Pound', 'CYP');
INSERT INTO `sys_countries` VALUES('CZ', 'CZE', 203, 'Czech Republic', 'Europe', 'Czech Koruna', 'CZK');
INSERT INTO `sys_countries` VALUES('DE', 'DEU', 276, 'Germany', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('DJ', 'DJI', 262, 'Djibouti', 'Africa', 'Djibouti Franc', 'DJF');
INSERT INTO `sys_countries` VALUES('DK', 'DNK', 208, 'Denmark', 'Europe', 'Danish Krone', 'DKK');
INSERT INTO `sys_countries` VALUES('DM', 'DMA', 212, 'Dominica', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('DO', 'DOM', 214, 'Dominican Republic', 'Central America and the Caribbean', 'Dominican Peso', 'DOP');
INSERT INTO `sys_countries` VALUES('DZ', 'DZA', 12, 'Algeria', 'Africa', 'Algerian Dinar', 'DZD');
INSERT INTO `sys_countries` VALUES('EC', 'ECU', 218, 'Ecuador', 'South America', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('EE', 'EST', 233, 'Estonia', 'Europe', 'Kroon', 'EEK');
INSERT INTO `sys_countries` VALUES('EG', 'EGY', 818, 'Egypt', 'Africa', 'Egyptian Pound', 'EGP');
INSERT INTO `sys_countries` VALUES('EH', 'ESH', 732, 'Western Sahara', 'Africa', 'Moroccan Dirham', 'MAD');
INSERT INTO `sys_countries` VALUES('ER', 'ERI', 232, 'Eritrea', 'Africa', 'Nakfa', 'ERN');
INSERT INTO `sys_countries` VALUES('ES', 'ESP', 724, 'Spain', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('ET', 'ETH', 231, 'Ethiopia', 'Africa', 'Ethiopian Birr', 'ETB');
INSERT INTO `sys_countries` VALUES('FI', 'FIN', 246, 'Finland', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('FJ', 'FJI', 242, 'Fiji', 'Oceania', 'Fijian Dollar', 'FJD');
INSERT INTO `sys_countries` VALUES('FK', 'FLK', 238, 'Falkland Islands (Islas Malvinas)', 'South America', 'Falkland Islands Pound', 'FKP');
INSERT INTO `sys_countries` VALUES('FM', 'FSM', 583, 'Micronesia, Federated States of', 'Oceania', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('FO', 'FRO', 234, 'Faroe Islands', 'Europe', 'Danish Krone', 'DKK');
INSERT INTO `sys_countries` VALUES('FR', 'FRA', 250, 'France', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('GA', 'GAB', 266, 'Gabon', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('GB', 'GBR', 826, 'United Kingdom', 'Europe', 'Pound Sterling', 'GBP');
INSERT INTO `sys_countries` VALUES('GD', 'GRD', 308, 'Grenada', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('GE', 'GEO', 268, 'Georgia', 'Commonwealth of Independent States', 'Lari', 'GEL');
INSERT INTO `sys_countries` VALUES('GF', 'GUF', 254, 'French Guiana', 'South America', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('GG', 'GGY', 831, 'Guernsey', 'Europe', 'Pound sterling', 'GBP');
INSERT INTO `sys_countries` VALUES('GH', 'GHA', 288, 'Ghana', 'Africa', 'Cedi', 'GHC');
INSERT INTO `sys_countries` VALUES('GI', 'GIB', 292, 'Gibraltar', 'Europe', 'Gibraltar Pound', 'GIP');
INSERT INTO `sys_countries` VALUES('GL', 'GRL', 304, 'Greenland', 'Arctic Region', 'Danish Krone', 'DKK');
INSERT INTO `sys_countries` VALUES('GM', 'GMB', 270, 'The Gambia', 'Africa', 'Dalasi', 'GMD');
INSERT INTO `sys_countries` VALUES('GN', 'GIN', 324, 'Guinea', 'Africa', 'Guinean Franc', 'GNF');
INSERT INTO `sys_countries` VALUES('GP', 'GLP', 312, 'Guadeloupe', 'Central America and the Caribbean', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('GQ', 'GNQ', 226, 'Equatorial Guinea', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('GR', 'GRC', 300, 'Greece', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('GS', 'SGS', 239, 'South Georgia and the South Sandwich Islands', 'Antarctic Region', 'Pound Sterling', 'GBP');
INSERT INTO `sys_countries` VALUES('GT', 'GTM', 320, 'Guatemala', 'Central America and the Caribbean', 'Quetzal', 'GTQ');
INSERT INTO `sys_countries` VALUES('GU', 'GUM', 316, 'Guam', 'Oceania', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('GW', 'GNB', 624, 'Guinea-Bissau', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('GY', 'GUY', 328, 'Guyana', 'South America', 'Guyana Dollar', 'GYD');
INSERT INTO `sys_countries` VALUES('HK', 'HKG', 344, 'Hong Kong (SAR)', 'Southeast Asia', 'Hong Kong Dollar', 'HKD');
INSERT INTO `sys_countries` VALUES('HM', 'HMD', 334, 'Heard Island and McDonald Islands', 'Antarctic Region', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('HN', 'HND', 340, 'Honduras', 'Central America and the Caribbean', 'Lempira', 'HNL');
INSERT INTO `sys_countries` VALUES('HR', 'HRV', 191, 'Croatia', 'Europe', 'Kuna', 'HRK');
INSERT INTO `sys_countries` VALUES('HT', 'HTI', 332, 'Haiti', 'Central America and the Caribbean', 'Gourde', 'HTG');
INSERT INTO `sys_countries` VALUES('HU', 'HUN', 348, 'Hungary', 'Europe', 'Forint', 'HUF');
INSERT INTO `sys_countries` VALUES('ID', 'IDN', 360, 'Indonesia', 'Southeast Asia', 'Rupiah', 'IDR');
INSERT INTO `sys_countries` VALUES('IE', 'IRL', 372, 'Ireland', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('IL', 'ISR', 376, 'Israel', 'Middle East', 'New Israeli Sheqel', 'ILS');
INSERT INTO `sys_countries` VALUES('IM', 'IMN', 833, 'Isle of Man', 'Europe', 'Pound sterling', 'GBP');
INSERT INTO `sys_countries` VALUES('IN', 'IND', 356, 'India', 'Asia', 'Indian Rupee', 'INR');
INSERT INTO `sys_countries` VALUES('IO', 'IOT', 86, 'British Indian Ocean Territory', 'World', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('IQ', 'IRQ', 368, 'Iraq', 'Middle East', 'Iraqi Dinar', 'IQD');
INSERT INTO `sys_countries` VALUES('IR', 'IRN', 364, 'Iran', 'Middle East', 'Iranian Rial', 'IRR');
INSERT INTO `sys_countries` VALUES('IS', 'ISL', 352, 'Iceland', 'Arctic Region', 'Iceland Krona', 'ISK');
INSERT INTO `sys_countries` VALUES('IT', 'ITA', 380, 'Italy', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('JE', 'JEY', 832, 'Jersey', 'Europe', 'Pound sterling', 'GBP');
INSERT INTO `sys_countries` VALUES('JM', 'JAM', 388, 'Jamaica', 'Central America and the Caribbean', 'Jamaican dollar', 'JMD');
INSERT INTO `sys_countries` VALUES('JO', 'JOR', 400, 'Jordan', 'Middle East', 'Jordanian Dinar', 'JOD');
INSERT INTO `sys_countries` VALUES('JP', 'JPN', 392, 'Japan', 'Asia', 'Yen', 'JPY');
INSERT INTO `sys_countries` VALUES('KE', 'KEN', 404, 'Kenya', 'Africa', 'Kenyan shilling', 'KES');
INSERT INTO `sys_countries` VALUES('KG', 'KGZ', 417, 'Kyrgyzstan', 'Commonwealth of Independent States', 'Som', 'KGS');
INSERT INTO `sys_countries` VALUES('KH', 'KHM', 116, 'Cambodia', 'Southeast Asia', 'Riel', 'KHR');
INSERT INTO `sys_countries` VALUES('KI', 'KIR', 296, 'Kiribati', 'Oceania', 'Australian dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('KM', 'COM', 174, 'Comoros', 'Africa', 'Comoro Franc', 'KMF');
INSERT INTO `sys_countries` VALUES('KN', 'KNA', 659, 'Saint Kitts and Nevis', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('KP', 'PRK', 408, 'Korea, North', 'Asia', 'North Korean Won', 'KPW');
INSERT INTO `sys_countries` VALUES('KR', 'KOR', 410, 'Korea, South', 'Asia', 'Won', 'KRW');
INSERT INTO `sys_countries` VALUES('KW', 'KWT', 414, 'Kuwait', 'Middle East', 'Kuwaiti Dinar', 'KWD');
INSERT INTO `sys_countries` VALUES('KY', 'CYM', 136, 'Cayman Islands', 'Central America and the Caribbean', 'Cayman Islands Dollar', 'KYD');
INSERT INTO `sys_countries` VALUES('KZ', 'KAZ', 398, 'Kazakhstan', 'Commonwealth of Independent States', 'Tenge', 'KZT');
INSERT INTO `sys_countries` VALUES('LA', 'LAO', 418, 'Laos', 'Southeast Asia', 'Kip', 'LAK');
INSERT INTO `sys_countries` VALUES('LB', 'LBN', 422, 'Lebanon', 'Middle East', 'Lebanese Pound', 'LBP');
INSERT INTO `sys_countries` VALUES('LC', 'LCA', 662, 'Saint Lucia', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('LI', 'LIE', 438, 'Liechtenstein', 'Europe', 'Swiss Franc', 'CHF');
INSERT INTO `sys_countries` VALUES('LK', 'LKA', 144, 'Sri Lanka', 'Asia', 'Sri Lanka Rupee', 'LKR');
INSERT INTO `sys_countries` VALUES('LR', 'LBR', 430, 'Liberia', 'Africa', 'Liberian Dollar', 'LRD');
INSERT INTO `sys_countries` VALUES('LS', 'LSO', 426, 'Lesotho', 'Africa', 'Loti', 'LSL');
INSERT INTO `sys_countries` VALUES('LT', 'LTU', 440, 'Lithuania', 'Europe', 'Lithuanian Litas', 'LTL');
INSERT INTO `sys_countries` VALUES('LU', 'LUX', 442, 'Luxembourg', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('LV', 'LVA', 428, 'Latvia', 'Europe', 'Latvian Lats', 'LVL');
INSERT INTO `sys_countries` VALUES('LY', 'LBY', 434, 'Libya', 'Africa', 'Libyan Dinar', 'LYD');
INSERT INTO `sys_countries` VALUES('MA', 'MAR', 504, 'Morocco', 'Africa', 'Moroccan Dirham', 'MAD');
INSERT INTO `sys_countries` VALUES('MC', 'MCO', 492, 'Monaco', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('MD', 'MDA', 498, 'Moldova', 'Commonwealth of Independent States', 'Moldovan Leu', 'MDL');
INSERT INTO `sys_countries` VALUES('ME', 'MNE', 499, 'Montenegro', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('MF', 'MAF', 663, 'Saint Martin (French part)', 'Central America and the Caribbean', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('MG', 'MDG', 450, 'Madagascar', 'Africa', 'Malagasy Franc', 'MGF');
INSERT INTO `sys_countries` VALUES('MH', 'MHL', 584, 'Marshall Islands', 'Oceania', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('MK', 'MKD', 807, 'Macedonia, The Former Yugoslav Republic of', 'Europe', 'Denar', 'MKD');
INSERT INTO `sys_countries` VALUES('ML', 'MLI', 466, 'Mali', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('MM', 'MMR', 104, 'Myanmar', 'Southeast Asia', 'kyat', 'MMK');
INSERT INTO `sys_countries` VALUES('MN', 'MNG', 496, 'Mongolia', 'Asia', 'Tugrik', 'MNT');
INSERT INTO `sys_countries` VALUES('MO', 'MAC', 446, 'Macao', 'Southeast Asia', 'Pataca', 'MOP');
INSERT INTO `sys_countries` VALUES('MP', 'MNP', 580, 'Northern Mariana Islands', 'Oceania', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('MQ', 'MTQ', 474, 'Martinique', 'Central America and the Caribbean', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('MR', 'MRT', 478, 'Mauritania', 'Africa', 'Ouguiya', 'MRO');
INSERT INTO `sys_countries` VALUES('MS', 'MSR', 500, 'Montserrat', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('MT', 'MLT', 470, 'Malta', 'Europe', 'Maltese Lira', 'MTL');
INSERT INTO `sys_countries` VALUES('MU', 'MUS', 480, 'Mauritius', 'World', 'Mauritius Rupee', 'MUR');
INSERT INTO `sys_countries` VALUES('MV', 'MDV', 462, 'Maldives', 'Asia', 'Rufiyaa', 'MVR');
INSERT INTO `sys_countries` VALUES('MW', 'MWI', 454, 'Malawi', 'Africa', 'Kwacha', 'MWK');
INSERT INTO `sys_countries` VALUES('MX', 'MEX', 484, 'Mexico', 'North America', 'Mexican Peso', 'MXN');
INSERT INTO `sys_countries` VALUES('MY', 'MYS', 458, 'Malaysia', 'Southeast Asia', 'Malaysian Ringgit', 'MYR');
INSERT INTO `sys_countries` VALUES('MZ', 'MOZ', 508, 'Mozambique', 'Africa', 'Metical', 'MZM');
INSERT INTO `sys_countries` VALUES('NA', 'NAM', 516, 'Namibia', 'Africa', 'Namibian Dollar', 'NAD');
INSERT INTO `sys_countries` VALUES('NC', 'NCL', 540, 'New Caledonia', 'Oceania', 'CFP Franc', 'XPF');
INSERT INTO `sys_countries` VALUES('NE', 'NER', 562, 'Niger', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('NF', 'NFK', 574, 'Norfolk Island', 'Oceania', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('NG', 'NGA', 566, 'Nigeria', 'Africa', 'Naira', 'NGN');
INSERT INTO `sys_countries` VALUES('NI', 'NIC', 558, 'Nicaragua', 'Central America and the Caribbean', 'Cordoba Oro', 'NIO');
INSERT INTO `sys_countries` VALUES('NL', 'NLD', 528, 'Netherlands', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('NO', 'NOR', 578, 'Norway', 'Europe', 'Norwegian Krone', 'NOK');
INSERT INTO `sys_countries` VALUES('NP', 'NPL', 524, 'Nepal', 'Asia', 'Nepalese Rupee', 'NPR');
INSERT INTO `sys_countries` VALUES('NR', 'NRU', 520, 'Nauru', 'Oceania', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('NU', 'NIU', 570, 'Niue', 'Oceania', 'New Zealand Dollar', 'NZD');
INSERT INTO `sys_countries` VALUES('NZ', 'NZL', 554, 'New Zealand', 'Oceania', 'New Zealand Dollar', 'NZD');
INSERT INTO `sys_countries` VALUES('OM', 'OMN', 512, 'Oman', 'Middle East', 'Rial Omani', 'OMR');
INSERT INTO `sys_countries` VALUES('PA', 'PAN', 591, 'Panama', 'Central America and the Caribbean', 'balboa', 'PAB');
INSERT INTO `sys_countries` VALUES('PE', 'PER', 604, 'Peru', 'South America', 'Nuevo Sol', 'PEN');
INSERT INTO `sys_countries` VALUES('PF', 'PYF', 258, 'French Polynesia', 'Oceania', 'CFP Franc', 'XPF');
INSERT INTO `sys_countries` VALUES('PG', 'PNG', 598, 'Papua New Guinea', 'Oceania', 'Kina', 'PGK');
INSERT INTO `sys_countries` VALUES('PH', 'PHL', 608, 'Philippines', 'Southeast Asia', 'Philippine Peso', 'PHP');
INSERT INTO `sys_countries` VALUES('PK', 'PAK', 586, 'Pakistan', 'Asia', 'Pakistan Rupee', 'PKR');
INSERT INTO `sys_countries` VALUES('PL', 'POL', 616, 'Poland', 'Europe', 'Zloty', 'PLN');
INSERT INTO `sys_countries` VALUES('PM', 'SPM', 666, 'Saint Pierre and Miquelon', 'North America', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('PN', 'PCN', 612, 'Pitcairn Islands', 'Oceania', 'New Zealand Dollar', 'NZD');
INSERT INTO `sys_countries` VALUES('PR', 'PRI', 630, 'Puerto Rico', 'Central America and the Caribbean', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('PS', 'PSE', 275, 'Palestinian Territory, Occupied', NULL, NULL, NULL);
INSERT INTO `sys_countries` VALUES('PT', 'PRT', 620, 'Portugal', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('PW', 'PLW', 585, 'Palau', 'Oceania', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('PY', 'PRY', 600, 'Paraguay', 'South America', 'Guarani', 'PYG');
INSERT INTO `sys_countries` VALUES('QA', 'QAT', 634, 'Qatar', 'Middle East', 'Qatari Rial', 'QAR');
INSERT INTO `sys_countries` VALUES('RE', 'REU', 638, 'Reunion', 'World', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('RO', 'ROU', 642, 'Romania', 'Europe', 'Leu', 'ROL');
INSERT INTO `sys_countries` VALUES('RS', 'SRB', 688, 'Serbia', 'Europe', 'Serbian Dinar', 'RSD');
INSERT INTO `sys_countries` VALUES('RU', 'RUS', 643, 'Russia', 'Asia', 'Russian Ruble', 'RUB');
INSERT INTO `sys_countries` VALUES('RW', 'RWA', 646, 'Rwanda', 'Africa', 'Rwanda Franc', 'RWF');
INSERT INTO `sys_countries` VALUES('SA', 'SAU', 682, 'Saudi Arabia', 'Middle East', 'Saudi Riyal', 'SAR');
INSERT INTO `sys_countries` VALUES('SB', 'SLB', 90, 'Solomon Islands', 'Oceania', 'Solomon Islands Dollar', 'SBD');
INSERT INTO `sys_countries` VALUES('SC', 'SYC', 690, 'Seychelles', 'Africa', 'Seychelles Rupee', 'SCR');
INSERT INTO `sys_countries` VALUES('SD', 'SDN', 736, 'Sudan', 'Africa', 'Sudanese Dinar', 'SDD');
INSERT INTO `sys_countries` VALUES('SE', 'SWE', 752, 'Sweden', 'Europe', 'Swedish Krona', 'SEK');
INSERT INTO `sys_countries` VALUES('SG', 'SGP', 702, 'Singapore', 'Southeast Asia', 'Singapore Dollar', 'SGD');
INSERT INTO `sys_countries` VALUES('SH', 'SHN', 654, 'Saint Helena', 'Africa', 'Saint Helenian Pound', 'SHP');
INSERT INTO `sys_countries` VALUES('SI', 'SVN', 705, 'Slovenia', 'Europe', 'Tolar', 'SIT');
INSERT INTO `sys_countries` VALUES('SJ', 'SJM', 744, 'Svalbard', 'Arctic Region', 'Norwegian Krone', 'NOK');
INSERT INTO `sys_countries` VALUES('SK', 'SVK', 703, 'Slovakia', 'Europe', 'Slovak Koruna', 'SKK');
INSERT INTO `sys_countries` VALUES('SL', 'SLE', 694, 'Sierra Leone', 'Africa', 'Leone', 'SLL');
INSERT INTO `sys_countries` VALUES('SM', 'SMR', 674, 'San Marino', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('SN', 'SEN', 686, 'Senegal', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('SO', 'SOM', 706, 'Somalia', 'Africa', 'Somali Shilling', 'SOS');
INSERT INTO `sys_countries` VALUES('SR', 'SUR', 740, 'Suriname', 'South America', 'Suriname Guilder', 'SRG');
INSERT INTO `sys_countries` VALUES('ST', 'STP', 678, 'Sao Tome and Principe', 'Africa', 'Dobra', 'STD');
INSERT INTO `sys_countries` VALUES('SV', 'SLV', 222, 'El Salvador', 'Central America and the Caribbean', 'El Salvador Colon', 'SVC');
INSERT INTO `sys_countries` VALUES('SY', 'SYR', 760, 'Syria', 'Middle East', 'Syrian Pound', 'SYP');
INSERT INTO `sys_countries` VALUES('SZ', 'SWZ', 748, 'Swaziland', 'Africa', 'Lilangeni', 'SZL');
INSERT INTO `sys_countries` VALUES('TC', 'TCA', 796, 'Turks and Caicos Islands', 'Central America and the Caribbean', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('TD', 'TCD', 148, 'Chad', 'Africa', 'CFA Franc BEAC', 'XAF');
INSERT INTO `sys_countries` VALUES('TF', 'ATF', 260, 'French Southern and Antarctic Lands', 'Antarctic Region', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('TG', 'TGO', 768, 'Togo', 'Africa', 'CFA Franc BCEAO', 'XOF');
INSERT INTO `sys_countries` VALUES('TH', 'THA', 764, 'Thailand', 'Southeast Asia', 'Baht', 'THB');
INSERT INTO `sys_countries` VALUES('TJ', 'TJK', 762, 'Tajikistan', 'Commonwealth of Independent States', 'Somoni', 'TJS');
INSERT INTO `sys_countries` VALUES('TK', 'TKL', 772, 'Tokelau', 'Oceania', 'New Zealand Dollar', 'NZD');
INSERT INTO `sys_countries` VALUES('TL', 'TLS', 626, 'East Timor', NULL, 'Timor Escudo', 'TPE');
INSERT INTO `sys_countries` VALUES('TM', 'TKM', 795, 'Turkmenistan', 'Commonwealth of Independent States', 'Manat', 'TMM');
INSERT INTO `sys_countries` VALUES('TN', 'TUN', 788, 'Tunisia', 'Africa', 'Tunisian Dinar', 'TND');
INSERT INTO `sys_countries` VALUES('TO', 'TON', 776, 'Tonga', 'Oceania', 'Pa''anga', 'TOP');
INSERT INTO `sys_countries` VALUES('TR', 'TUR', 792, 'Turkey', 'Middle East', 'Turkish Lira', 'TRL');
INSERT INTO `sys_countries` VALUES('TT', 'TTO', 780, 'Trinidad and Tobago', 'Central America and the Caribbean', 'Trinidad and Tobago Dollar', 'TTD');
INSERT INTO `sys_countries` VALUES('TV', 'TUV', 798, 'Tuvalu', 'Oceania', 'Australian Dollar', 'AUD');
INSERT INTO `sys_countries` VALUES('TW', 'TWN', 158, 'Taiwan', 'Southeast Asia', 'New Taiwan Dollar', 'TWD');
INSERT INTO `sys_countries` VALUES('TZ', 'TZA', 834, 'Tanzania', 'Africa', 'Tanzanian Shilling', 'TZS');
INSERT INTO `sys_countries` VALUES('UA', 'UKR', 804, 'Ukraine', 'Commonwealth of Independent States', 'Hryvnia', 'UAH');
INSERT INTO `sys_countries` VALUES('UG', 'UGA', 800, 'Uganda', 'Africa', 'Uganda Shilling', 'UGX');
INSERT INTO `sys_countries` VALUES('UM', 'UMI', 581, 'United States Minor Outlying Islands', NULL, 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('US', 'USA', 840, 'United States', 'North America', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('UY', 'URY', 858, 'Uruguay', 'South America', 'Peso Uruguayo', 'UYU');
INSERT INTO `sys_countries` VALUES('UZ', 'UZB', 860, 'Uzbekistan', 'Commonwealth of Independent States', 'Uzbekistan Sum', 'UZS');
INSERT INTO `sys_countries` VALUES('VA', 'VAT', 336, 'Holy See (Vatican City)', 'Europe', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('VC', 'VCT', 670, 'Saint Vincent and the Grenadines', 'Central America and the Caribbean', 'East Caribbean Dollar', 'XCD');
INSERT INTO `sys_countries` VALUES('VE', 'VEN', 862, 'Venezuela', 'South America, Central America and the Caribbean', 'Bolivar', 'VEB');
INSERT INTO `sys_countries` VALUES('VG', 'VGB', 92, 'British Virgin Islands', 'Central America and the Caribbean', 'US dollar', 'USD');
INSERT INTO `sys_countries` VALUES('VI', 'VIR', 850, 'Virgin Islands', 'Central America and the Caribbean', 'US Dollar', 'USD');
INSERT INTO `sys_countries` VALUES('VN', 'VNM', 704, 'Vietnam', 'Southeast Asia', 'Dong', 'VND');
INSERT INTO `sys_countries` VALUES('VU', 'VUT', 548, 'Vanuatu', 'Oceania', 'Vatu', 'VUV');
INSERT INTO `sys_countries` VALUES('WF', 'WLF', 876, 'Wallis and Futuna', 'Oceania', 'CFP Franc', 'XPF');
INSERT INTO `sys_countries` VALUES('WS', 'WSM', 882, 'Samoa', 'Oceania', 'Tala', 'WST');
INSERT INTO `sys_countries` VALUES('YE', 'YEM', 887, 'Yemen', 'Middle East', 'Yemeni Rial', 'YER');
INSERT INTO `sys_countries` VALUES('YT', 'MYT', 175, 'Mayotte', 'Africa', 'Euro', 'EUR');
INSERT INTO `sys_countries` VALUES('ZA', 'ZAF', 710, 'South Africa', 'Africa', 'Rand', 'ZAR');
INSERT INTO `sys_countries` VALUES('ZM', 'ZWB', 894, 'Zambia', 'Africa', 'Kwacha', 'ZMK');
INSERT INTO `sys_countries` VALUES('ZW', 'ZWE', 716, 'Zimbabwe', 'Africa', 'Zimbabwe Dollar', 'ZWD');


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_email_templates` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Desc` varchar(255) NOT NULL,
  `LangID` tinyint(4) unsigned NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`,`LangID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_Activation', 'Your Profile Is Now Active', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your profile was reviewed and activated!</p>\r\n\r\n<p>Your Account: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<p>Member ID: <b><recipientID></b></p>\r\n\r\n<p>Your E-mail: <span style="color:#FF6633"><Email></span></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile activation notification.', 0),
('t_AdminEmail', '<SiteName> Admin: <MessageSubject>', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<Domain>"><SiteName></a> Admin sent you a message:</p>\r\n\r\n<hr>\r\n<p style="color:#3B5C8E"><MessageText></p>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message from the site Admin.', 0),
('t_AdminStats', 'Content Pending Admin Review', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><MessageText></p>\r\n\r\n<p>Go to <a href="<ViewLink>">administration panel</a> to review pending content.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message to admin about content pending review', 0),
('t_Compose', 'You''ve Got A New Message', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>You have received a message from <a href="<ProfileUrl>"><ProfileReference></a>!</p>\r\n\r\n<p>Go to your account to read and reply: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New message notification without message text', 0),
('t_Confirmation', 'Email Confirmation Request', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Thank you for registering at <SiteName>!</p>\r\n\r\n<p>Click to confirm your email:\r\n<a href="<ConfirmationLink>"><ConfirmationLink></a></p>\r\n\r\n<p>CONFIRMATION CODE: <ConfCode></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Email confirmation message.', 0),
('t_CupidMail', 'Your Matches', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Check out some <SiteName> members that may be a good match with you:</p>\r\n\r\n<p><a href="<MatchProfileLink>"><MatchProfileLink></a></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Matching profiles notification.', 0),
('t_Forgot', 'Your Account Password', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your member ID: <b><recipientID></b></p>\r\n\r\n<p>Your password: <b><Password></b></p>\r\n\r\n<p>You may login here: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Password retrieval', 0),
('t_FreeEmail', 'Member Email Address', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><profileNickName>''s contact info:</p> \r\n\r\n<p><profileContactInfo></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Requested Member Email Address', 0),
('t_MemExpiration', 'Your Membership Is About To Expire', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Your <SiteName> <MembershipName> membership will expire in <ExpireDays> days.</p>\r\n\r\n<p>NOTE: -1 means it has already expired</p>\r\n\r\n\r\n<p><a href="<Domain>modules/?r=membership/index/">Renew Membership</a></p>\r\n<bx_include_auto:_email_footer.html />', 'Membership expiration notice', 0),
('t_MemChanged', 'Your Membership Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Your membership level was changed to: <b><a href="<Domain>modules/?r=membership/index/"><MembershipLevel></a></b></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Membership change', 0),
('t_Message', 'You''ve Got A New Message', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<ProfileUrl>"><ProfileReference></a> sent you a message: </p>\r\n\r\n<hr>\r\n<p><MessageText></p>\r\n<hr>\r\n\r\n<p>Go to your account to reply: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New message notification with message text', 0),
('t_UserJoined', 'New Member Joined', '<bx_include_auto:_email_header.html />\r\n\r\n<p>New user: <RealName></p> \r\n<p>Email: <Email></p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about new member', 0),
('t_UserConfirmed', 'User Confirmed Email', '<bx_include_auto:_email_header.html />\r\n\r\n<p>User: <RealName></p> \r\n<p>Email: <Email></p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification - user confirmed email', 0),
('t_UserMemChanged', 'Member Membership Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p><RealName>''s membership level was changed to: <b><MembershipLevel></b></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about membership change', 0),
('t_UserUnregistered', 'Member Unregistered', '<bx_include_auto:_email_header.html />\r\n\r\n<p>User: <NickName></p> \r\n<p>Email: <Email></p> \r\n<p>was unregistered.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about unregistered member', 0),
('t_SpamReport', 'Profile Spam Report', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<Domain>profile.php?ID=<reporterID>"><reporterNick></a> reported Profile SPAM:  <a href="<Domain>profile.php?ID=<spamerID>"><b><spamerNick></b></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile Spam Report', 0),
('t_TellFriend', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p>I thought you''d be interested: <a href="<Link>"><Link></a><br />\r\n---<br />\r\n<a href="<SenderLink>"><SenderName></a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Friend Invitation', 0),
('t_TellFriendProfile', 'Look At This Profile', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n\r\n<p>Check out this profile: <a href="<Link>"><Link></a><br />\r\n---<br />\r\n<a href="<SenderLink>"><SenderName></a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Email profile to a friend', 0),
('t_VKiss', 'Greeting notification', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><ProfileReference> sent you a greeting!</p>\r\n\r\n<p><ProfileReference> may be interested in you or maybe just wants to say Hello!\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Greeting notification ', 0),
('t_VKiss_visitor', 'Greeting notification', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Our site visitor sent you a greeting!</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Greeting from visitor notification', 0),
('t_MessageCopy', 'Copy Of Your Message : <your subject here>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>You wrote:</p>\r\n<hr>\r\n<p><your message here></p>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message copy', 0),
('t_Subscription', 'Your Subscription', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>You subscribed to <a href="<ViewLink>"><Subscription></a></p>\r\n\r\n<p>You may cancel the subscription here: <a href="<SysUnsubscribeLink>"><SysUnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription confirmation', 0),
('t_sbsProfileComments', 'New Profile Comments', '<bx_include_auto:_email_header.html />\r\n\r\n <p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Profile you subscribed to got <a href="<ViewLink>">new comments</a>.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New comments to profile subscription', 0),
('t_sbsProfileEdit', 'Subscription: Profile Edited', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<ViewLink>">Profile you subscribed to</a> has been updated.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile info subscription.', 0),
('t_FriendRequest', 'Friendship Request', '<bx_include_auto:_email_header.html />\r\n\r\n    <p><b>Dear <Recipient></b>,</p>\r\n   \r\n    <p><a href="<SenderLink>"><Sender></a> wants to be friends with you. <a href="<RequestLink>">Respond</a>.</p>\r\n    <br /> \r\n    <bx_include_auto:_email_footer.html />', 'Friendship Request', 0),
('t_FriendRequestAccepted', 'Friendship Request Accepted', '<bx_include_auto:_email_header.html />\r\n\r\n    <p><b>Dear <Recipient></b>,</p>\r\n    \r\n    <p><a href="<SenderLink>"><Sender></a> accepted your friendship request.</p>\r\n    <br /> \r\n    <bx_include_auto:_email_footer.html />', 'Friendship Request Accepted', 0),
('t_SpamReportAuto', '<SiteName> Automatic Spam Report', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<b>Profile:</b> <a href="<SpammerUrl>"><SpammerNickName></a><br />\r\n\r\n<b>Page:</b> <Page><br />\r\n\r\n<b>GET variables:</b>\r\n<pre>\r\n<Get>\r\n</pre>\r\n\r\n<b>Spam Content:</b>\r\n<pre>\r\n<SpamContent>\r\n</pre>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Automatic spam report', 0),
('t_ModulesUpdates', '<SiteName> Automatic modules updates checker', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The following updates are available:</p>\r\n\r\n<p><MessageText></p>\r\n\r\n<p>If you want to install any of them you need to go to your site''s admin panel -> Modules -> Add & Manage and click Check For Updates button in Installed Modules block. It will load all available updates.</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message to admin about modules updates', 0),
('t_ExportReady', '<SiteName> Your data export is ready', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your data download link:</p>\r\n\r\n<p><FileUrl></p>\r\n\r\n<p>Link will be availiable for 24 hours.</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Notification about user data export', 0);


-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_member`
--

CREATE TABLE `sys_menu_member` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Caption` varchar(100) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Icon` varchar(100) NOT NULL,
  `Link` varchar(250) NOT NULL,
  `Script` varchar(250) NOT NULL,
  `Eval` text NOT NULL,
  `PopupMenu` text NOT NULL,
  `Order` int(5) NOT NULL,
  `Active` enum('1','0') NOT NULL,
  `Movable` tinyint(4) NOT NULL default '3',
  `Clonable` tinyint(1) NOT NULL default '1',
  `Editable` tinyint(1) NOT NULL default '1',
  `Deletable` tinyint(1) NOT NULL default '1',
  `Target` varchar(200) NOT NULL,
  `Position` enum('top','bottom','top_extra') NOT NULL default 'top',
  `Type` enum('link','system','linked_item') NOT NULL,
  `Parent` int(11) NOT NULL,
  `Bubble` text NOT NULL,
  `Description` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `Parent` (`Parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_menu_member`
--
INSERT INTO `sys_menu_member` (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Order`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Parent`, `Bubble`, `Description`) VALUES 
('{evalResult}', 'MemberBlock', '', '{ProfileLink}', '', 'return ''<b class="menu_item_username">'' . getNickName({ID}) . ''</b>'';', 'bx_import(''BxDolUserStatusView'');\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView->getMemberMenuStatuses();', 1, 1, 3, 1, 0, 0, '', 'top', 'link', 0, '', '_Presence'),

('_Mail', 'Mail', 'envelope', 'mail.php?mode=inbox', '', '', 'bx_import( ''BxTemplMailBox'' );\r\n// return list of messages ;\r\nreturn BxTemplMailBox::get_member_menu_messages_list({ID});', 1, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import( ''BxTemplMailBox'' );\r\n// return list of new messages ;\r\n$aRetEval= BxTemplMailBox::get_member_menu_bubble_new_messages({ID}, {iOldCount});', '_Mail'),
('_Friends', 'Friends', 'users', 'viewFriends.php?iUser={ID}', '', '', 'bx_import( ''BxDolFriendsPageView'' );\r\nreturn BxDolFriendsPageView::get_member_menu_friends_list({ID});', 3, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import( ''BxDolFriendsPageView'' );\r\n$aRetEval = BxDolFriendsPageView::get_member_menu_bubble_friend_requests( {ID}, {iOldCount});', '_Friends'),
('_sys_pmt_shopping_cart_caption', 'ShoppingCart', 'shopping-cart', 'cart.php', '', '', 'bx_import(''BxDolPayments'');\r\nreturn BxDolPayments::getInstance()->getCartItems();', 4, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import(''BxDolPayments'');\r\n$oPayment = BxDolPayments::getInstance();\r\nif($oPayment->isActive()) $aRetEval = $oPayment->getCartItemCount({ID}, {iOldCount}); else $isSkipItem = true;', '_sys_pmt_shopping_cart_description'),
('_Admin Panel', 'AdminPanel', 'wrench', '{evalResult}', '', 'return isAdmin() ? $GLOBALS[''site''][''url_admin''] : '''';', '', 5, 1, 3, 1, 1, 1, '', 'top_extra', 'link', 0, '', '_Go admin panel'),
('_sys_add_content', 'AddContent', 'plus', 'javascript:void(0);', '', 'return array(''evalResultCssClassWrapper'' => ''extra_item_add_content'');', 'bx_import( ''BxDolUserStatusView'' );\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView -> getStatusField({ID});', 6, 1, 3, 0, 0, 0, '', 'top_extra', 'link', 0, '$isSkipItem = $aReplaced[$sPosition][$iKey][''linked_items''] ? false : true;\r\n$aRetEval = false;', '_sys_add_content'),
('_Settings', 'Settings', 'cog', 'pedit.php?ID={ID}', '', '', '', 0, 0, 3, 1, 0, 0, '', 'top_extra', 'link', 0, '', '_Edit_profile_and_settings'),
('_Status Message', 'StatusMessage', 'edit', 'javascript:void(0);', '', '', 'bx_import( ''BxDolUserStatusView'' );\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView -> getStatusField({ID});', 0, 0, 3, 1, 1, 1, '', 'top_extra', 'link', 0, '', '_Status Message');


--
-- Table structure for table `sys_friend_list`
--

CREATE TABLE `sys_friend_list` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `Check` tinyint(2) NOT NULL default '0',
  `When` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `FriendPair` (`ID`,`Profile`),
  KEY `Profile` (`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------


CREATE TABLE `sys_options` (
  `Name` varchar(64) NOT NULL default '',
  `VALUE` mediumtext NOT NULL,
  `kateg` int(6) unsigned NOT NULL default '0',
  `desc` varchar(255) NOT NULL default '',
  `Type` enum('digit','text','checkbox','select','select_multiple','file','list') NOT NULL default 'digit',
  `check` text NOT NULL,
  `err_text` varchar(255) NOT NULL default '',
  `order_in_kateg` float default NULL,
  `AvailableValues` text NOT NULL default '',
  PRIMARY KEY  (`Name`),
  KEY `kateg` (`kateg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- CAT: Profiles
SET @iCatProfiles = 1;
INSERT INTO `sys_options` VALUES
('enable_global_couple', '', @iCatProfiles, 'Enable couple profiles', 'checkbox', '', '', 10, ''),
('votes', 'on', @iCatProfiles, 'Enable profile rating', 'checkbox', '', '', 20, ''),
('zodiac', '', @iCatProfiles, 'Enable zodiac signs', 'checkbox', '', '', 30, ''),
('anon_mode', '', @iCatProfiles, 'Enable anonymous mode', 'checkbox', '', '', 40, ''),
('reg_by_inv_only', '', @iCatProfiles, 'Enable registration by invitation only', 'checkbox', '', '', 50, ''),
('enable_cmts_profile_delete', '', @iCatProfiles, 'Allow profile comments deletion by profile owner', 'checkbox', '', '', 60, ''),
('member_online_time', '1', @iCatProfiles, 'Online status timeframe (minutes)', 'digit', 'return (int)$arg0 > 0;', 'Must be > 0', 70, ''),
('search_start_age', '18', @iCatProfiles, 'Lowest age possible for site members', 'digit', 'return setSearchStartAge((int)$arg0);', '', 80, ''),
('search_end_age', '75', @iCatProfiles, 'Highest age possible for site members', 'digit', 'return setSearchEndAge((int)$arg0);', '', 90, ''),
('friends_per_page', '14', @iCatProfiles, 'Number of friends displayed per page in profile', 'digit', '', '', 100, ''),
('featured_num', '8', @iCatProfiles, 'Number of Featured Members per page', 'digit', '', '', 110, ''),
('top_members_max_num', '8', @iCatProfiles, 'Number of Top Members per page', 'digit', '', '', 120, ''),
('sys_member_info_name', 'sys_username', @iCatProfiles, 'Member display-name', 'select', '', '', 130, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'name\');'),
('sys_member_info_info', 'sys_status_message', @iCatProfiles, 'Member brief info', 'select', '', '', 140, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'info\');'),
('sys_member_info_thumb', 'sys_avatar', @iCatProfiles, 'Member thumb', 'select', '', '', 150, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'thumb\');'),
('sys_member_info_thumb_icon', 'sys_avatar_icon', @iCatProfiles, 'Member thumb icon', 'select', '', '', 160, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'thumb_icon\');');

-- CAT: General
SET @iCatGeneral = 3;
INSERT INTO `sys_options` VALUES
('sys_ftp_host', '', @iCatGeneral, 'FTP host', 'digit', '', '', 1, ''),
('sys_ftp_login', '', @iCatGeneral, 'FTP login', 'digit', '', '', 2, ''),
('sys_ftp_password', '', @iCatGeneral, 'FTP password', 'digit', '', '', 3, ''),
('sys_ftp_dir', '', @iCatGeneral, 'Path to Dolphin on FTP server', 'digit', '', '', 4, ''),

('MetaDescription', '', @iCatGeneral, 'Homepage meta-description', 'text', '', '', 10, ''),
('MetaKeyWords', '', @iCatGeneral, 'Homepage meta-keywords', 'text', '', '', 20, ''),

('enable_tiny_in_comments', '', @iCatGeneral, 'Enable WYSIWYG editor in comments', 'checkbox', '', '', 30, ''),

('sys_make_album_cover_last', 'on', @iCatGeneral, 'Enable last-added item as album cover', 'checkbox', '', '', 70, ''),
('sys_album_default_name', 'Hidden', @iCatGeneral, 'Default album name', 'digit', '', '', 80, ''),

('news_enable', 'on', @iCatGeneral, 'Enable BoonEx News in Admin', 'checkbox', '', '', 90, ''),
('feeds_enable', 'on', @iCatGeneral, 'Enable BoonEx Market Feeds in Admin', 'checkbox', '', '', 100, ''),

('enable_contact_form', 'on', @iCatGeneral, 'Enable contact form', 'checkbox', '', '', 110, ''),

('default_country', 'US', @iCatGeneral, 'Default country', 'digit', '', '', 120, ''),

('boonexAffID', '', @iCatGeneral, 'BoonEx affiliate ID', 'digit', '', '', 140, ''),
('enable_gd', 'on', @iCatGeneral, 'Enable GD library for image processing', 'checkbox', '', '', 150, ''),
('useLikeOperator', 'on', @iCatGeneral, 'Disable full-text search', 'checkbox', '', '', 160, ''),

('sys_default_payment', '', @iCatGeneral, 'Payment module (at least one payment processing module should be installed)', 'select', '', '', 170, 'PHP:bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->getPayments();'),

('sys_embedly_key', '', @iCatGeneral, 'Embedly Key', 'digit', '', '', 180, '');

-- CAT: Massmailer
SET @iCatMassmailer = 4;
INSERT INTO `sys_options` VALUES
('msgs_per_start', '20', @iCatMassmailer, 'Number of emails to send from queue per run, it happens every 5m-1h', 'digit', '', '', 10, '');


-- CAT: Memberships
SET @iCatMemberships = 5;
INSERT INTO `sys_options` VALUES
('expire_notify_once', 'on', @iCatMemberships, 'Notify members about membership expiration only once (every day otherwise)', 'checkbox', '', '', 10, ''),
('expire_notification_days', '1', @iCatMemberships, 'Number of days before membership expiration to notify members (-1 = after expiration)', 'digit', '', '', 20, ''),
('enable_promotion_membership', '', @iCatMemberships, 'Enable promotional membership', 'checkbox', '', '', 30, ''),
('promotion_membership_days', '7', @iCatMemberships, 'Number of days for promotional membership', 'digit', '', '', 40, '');


-- CAT: Moderation
SET @iCatModeration = 6;
INSERT INTO `sys_options` VALUES
('autoApproval_ifJoin', '', @iCatModeration, 'Auto-activate profiles after joining', 'checkbox', '', '', 10, ''),
('autoApproval_ifProfile', 'on', @iCatModeration, 'Preserve profile status after profile info editing', 'checkbox', '', '', 20, ''),
('autoApproval_ifNoConfEmail', '', @iCatModeration, 'Auto-confirm profile without confirmation email', 'checkbox', '', '', 30, ''),
('newusernotify', 'on', @iCatModeration, 'Enable notification about new members', 'checkbox', '', '', 40, ''),
('unregisterusernotify', 'on', @iCatModeration, 'Enable notification about unregistered members', 'checkbox', '', '', 50, ''),
('ban_duration', '10', @iCatModeration, 'Profile ban duration (in days)', 'digit', '', '', 60, '');


-- CAT: Site 
SET @iCatSite = 7;
INSERT INTO `sys_options` VALUES
('site_email', 'captain@example.com', @iCatSite, 'Site Email', 'digit', '', '', 10, ''),
('site_title', 'Community', @iCatSite, 'Site Title', 'digit', '', '', 20, ''),
('site_email_notify', 'no-reply@example.com', @iCatSite, 'Email to send site''s mail from', 'digit', '', '', 30, ''),
('site_timezone', 'UTC', @iCatSite, 'Site Timezone', 'select', '', '', 40, 'PHP:return array_combine(timezone_identifiers_list(), timezone_identifiers_list());');


-- CAT: Privacy
SET @iCatPrivacy = 9;
INSERT INTO `sys_options` VALUES
('sys_ps_enable_create_group', '', @iCatPrivacy, 'Enable \'Create New Privacy Group\'', 'checkbox', '', '', 10, ''),
('sys_ps_enable_default_values', '', @iCatPrivacy, 'Enable \'Default Values\'', 'checkbox', '', '', 20, ''),
('sys_ps_enabled_group_1', '', @iCatPrivacy, 'Enable \'Default\' group', 'checkbox', '', '', 30, ''),
('sys_ps_enabled_group_2', 'on', @iCatPrivacy, 'Enable \'Me Only\' group', 'checkbox', '', '', 40, ''),
('sys_ps_enabled_group_3', 'on', @iCatPrivacy, 'Enable \'Public\' group', 'checkbox', '', '', 50, ''),
('sys_ps_enabled_group_4', 'on', @iCatPrivacy, 'Enable \'Members\' group', 'checkbox', '', '', 60, ''),
('sys_ps_enabled_group_5', 'on', @iCatPrivacy, 'Enable \'Friends\' group', 'checkbox', '', '', 70, ''),
('sys_ps_enabled_group_6', '', @iCatPrivacy, 'Enable \'Faves\' group', 'checkbox', '', '', 80, ''),
('sys_ps_enabled_group_7', '', @iCatPrivacy, 'Enable \'Contacts\' group', 'checkbox', '', '', 90, '');


-- CAT: Pruning
SET @iCatPruning = 11;
INSERT INTO `sys_options` VALUES
('db_clean_msg', '365', @iCatPruning, 'Delete messages older than (days)', 'digit', '', '', 10, ''),
('db_clean_profiles', '0', @iCatPruning, 'Delete profiles of members that didn\'t login for (days)', 'digit', '', '', 20, ''),
('db_clean_members_visits', '90', @iCatPruning, 'Delete stored members IPs older than (days)', 'digit', '', '', 30, ''),
('db_clean_banners_info', '60', @iCatPruning, 'Delete banner views and clicks data older than (days)', 'digit', '', '', 40, ''),
('db_clean_vkiss', '90', @iCatPruning, 'Delete greeting older than (days)', 'digit', '', '', 50, ''),
('db_clean_mem_levels', '30', @iCatPruning, 'Delete membership levels expired for (days)', 'digit', '', '', 60, '');


-- CAT: Matches
SET @iCatMatches = 12;
INSERT INTO `sys_options` VALUES
('enable_match', '', @iCatMatches, 'Enable matchmaking', 'checkbox', '', '', 10, ''),
('view_match_percent', '', @iCatMatches, 'Enable match percentage display', 'checkbox', '', '', 20, ''),
('match_percent', '85', @iCatMatches, 'Match percentage threshold for email notification (0-100)', 'digit', '', '', 30, '');


-- CAT: Template
SET @iCatTemplate = 13;
INSERT INTO `sys_options` VALUES
('template', 'evo', @iCatTemplate, 'Default template', 'select', 'global $dir; return (strlen($arg0) > 0 && file_exists($dir["root"]."templates/tmpl_".$arg0) ) ? true : false;', 'Template can not be empty and must be valid', 10, 'PHP:$aValues = get_templates_array(); $aResult = array(); foreach($aValues as $sKey => $sValue) $aResult[] = array(\'key\' => $sKey, \'value\' => $sValue); return $aResult;'),
('enable_template', 'on', @iCatTemplate, 'Allow users to choose templates', 'checkbox', '', '', 20, ''),
('nav_menu_elements_on_line_usr', '14', @iCatTemplate, 'Number of main menu tabs visible to members outside of "more" tab', 'digit', '', '', 30, ''),
('nav_menu_elements_on_line_gst', '14', @iCatTemplate, 'Number of main menu tabs visible to guests outside of "more" tab', 'digit', '', '', 40, ''),
('sys_template_page_width_min', '774', @iCatTemplate, 'Minimal allowed page width (pixels)', 'digit', '', '', 50, ''),
('sys_template_page_width_max', '1600', @iCatTemplate, 'Maximal allowed page width (pixels)', 'digit', '', '', 60, ''),
('ext_nav_menu_enabled', 'on', @iCatTemplate, 'Enable member menu', 'checkbox', '', '', 70, ''),
('ext_nav_menu_top_position', 'bottom', @iCatTemplate, 'Default position of member menu', 'select', '', '', 80, 'top,bottom,static');

-- CAT: Security
SET @iCatSecurity = 14;
INSERT INTO `sys_options` VALUES
('sys_security_form_token_enable', 'on', @iCatSecurity, 'Enable CSRF token in forms', 'checkbox', '', '', 30, ''),
('sys_security_form_token_lifetime', '86400', @iCatSecurity, 'CSRF token lifetime (seconds, 0 - no tracking)', 'digit', '', '', 40, ''),
('sys_recaptcha_key_public', '', @iCatSecurity, 'reCAPTCHA public key', 'digit', '', '', 50, ''),
('sys_recaptcha_key_private', '', @iCatSecurity, 'reCAPTCHA private key', 'digit', '', '', 60, ''),
('sys_safe_iframe_regexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%', @iCatSecurity, 'Safe iframe URI regular expression (don''t edit if unsure)', 'text', '', '', 100, '');

-- CAT: Watermark
SET @iCatWatermark = 16;
INSERT INTO `sys_options` VALUES
('enable_watermark', '', @iCatWatermark, 'Enable Watermark', 'checkbox', '', '', 10, ''),
('transparent1', '0', @iCatWatermark, 'Transparency for first image', 'digit', '', '', 20, ''),
('Water_Mark', '', @iCatWatermark, 'Water Mark', 'file', '', '', 30, '');


-- CAT: Languages
SET @iCatLanguages = 21;
INSERT INTO `sys_options` VALUES
('lang_default', 'en', @iCatLanguages, 'Default site language', 'text', '', '', 1, ''),
('lang_subst_from_en', 'on', @iCatLanguages, 'Substitute (during compilation) missing translations with english ones', 'checkbox', '', '', 2, ''),

('sys_calendar_starts_sunday', '', @iCatLanguages, 'Enable Sunday as the first weekday', 'checkbox', '', '', '30', ''),

('time_format_php', 'H:i', @iCatLanguages, 'Time format (for code)', 'digit', '', '', 40, ''),
('short_date_format_php', 'd.m.Y', @iCatLanguages, 'Short date format (for code)', 'digit', '', '', 50, ''),
('date_format_php', 'd.m.Y H:i', @iCatLanguages, 'Long date format (for code)', 'digit', '', '', 60, ''),

('time_format', '%H:%i', @iCatLanguages, 'Time format (for database)', 'digit', '', '', 70, ''),
('short_date_format', '%d.%m.%Y', @iCatLanguages, 'Short date format (for database)', 'digit', '', '', 80, ''),
('date_format', '%d.%m.%Y %H:%i', @iCatLanguages, 'Long date format (for database)', 'digit', '', '', 90, '');


-- CAT: IP List
SET @iCatIPList = 22;
INSERT INTO `sys_options` VALUES
('enable_member_store_ip', 'on', @iCatIPList, 'Enable member IP tracking', 'checkbox', '', '', 10, ''),
('ipBlacklistMode', '2', @iCatIPList, 'IP blacklist mode (1 - total block, 2 - login block)', 'digit', '', '', 20, ''),
('ipListGlobalType', '0', @iCatIPList, 'IP list type (0 - disabled, 1 - all allowed except listed, 2 - all blocked except listed)', 'digit', '', '', 30, '');


-- CAT: Antispam
SET @iCatAntispam = 23;
INSERT INTO `sys_options` VALUES
('sys_dnsbl_enable', 'on', @iCatAntispam, 'Enable DNS Block Lists', 'checkbox', '', '', 10, ''),
('sys_dnsbl_behaviour', 'approval', @iCatAntispam, 'User join behaviour if listed in DNS Block Lists', 'select', '', '', 11, 'block,approval'),
('sys_uridnsbl_enable', 'on', @iCatAntispam, 'Enable URI DNS Block Lists', 'checkbox', '', '', 20, ''),
('sys_akismet_enable', '', @iCatAntispam, 'Enable Akismet', 'checkbox', '', '', 30, ''),
('sys_akismet_api_key', '', @iCatAntispam, 'Akismet API Key', 'digit', '', '', 40, ''),
('sys_stopforumspam_enable', 'on', @iCatAntispam, 'Enable "Stop Forum Spam"', 'checkbox', '', '', 45, ''),
('sys_stopforumspam_api_key', '', @iCatAntispam, '"Stop Forum Spam" API Key', 'digit', '', '', 46, ''),
('sys_antispam_block', '', @iCatAntispam, 'Total block all spam content', 'checkbox', '', '', 50, ''),
('sys_antispam_report', 'on', @iCatAntispam, 'Send report to admin if spam content discovered', 'checkbox', '', '', 60, ''),
('sys_antispam_smart_check', 'on', @iCatAntispam, 'Smart antispam check', 'checkbox', '', '', 70, ''),
('sys_antispam_add_nofollow', 'on', @iCatAntispam, 'Add "nofollow" attribute for external links', 'checkbox', '', '', 80, '');


-- CAT: Caching
SET @iCatCaching = 24;
INSERT INTO `sys_options` VALUES
('enable_cache_system', 'on', @iCatCaching, 'Enable cache for profiles information', 'checkbox', '', '', 10, ''),
('sys_db_cache_enable', 'on', @iCatCaching, 'Enable DB cache', 'checkbox', '', '', 20, ''),
('sys_db_cache_engine', 'File', @iCatCaching, 'DB cache engine (other than File option may require custom server setup)', 'select', '', '', 30, 'File,Memcache,APC,XCache'),
('sys_cache_memcache_host', '', @iCatCaching, 'Memcached server host', 'digit', '', '', 40, ''),
('sys_cache_memcache_port', '11211', @iCatCaching, 'Memcached server port', 'digit', '', '', 50, ''),
('sys_pb_cache_enable', 'on', @iCatCaching, 'Enable page blocks cache', 'checkbox', '', '', 60, ''),
('sys_pb_cache_engine', 'File', @iCatCaching, 'Page blocks cache engine (other than File option may require custom server setup)', 'select', '', '', 70, 'File,Memcache,APC,XCache'),
('sys_mm_cache_engine', 'File', @iCatCaching, 'Member menu cache engine (other than File option may require custom server setup)', 'select', '', '', 80, 'File,Memcache,APC,XCache'),
('sys_template_cache_enable', 'on', @iCatCaching, 'Enable cache for HTML files', 'checkbox', '', '', 90, ''),
('sys_template_cache_engine', 'FileHtml', @iCatCaching, 'Template cache engine (other than FileHtml option may require custom server setup)', 'select', '', '', 100, 'FileHtml,Memcache,APC,XCache'),
('sys_template_cache_css_enable', 'on', @iCatCaching, 'Enable cache for CSS files', 'checkbox', '', '', 110, ''),
('sys_template_cache_js_enable', 'on', @iCatCaching, 'Enable cache for JS files', 'checkbox', '', '', 120, ''),
('sys_template_cache_compress_enable', 'on', @iCatCaching, 'Enable compression for JS/CSS files(cache must be enabled)', 'checkbox', '', '', 130, '');


-- CAT: Tags
SET @iCatTags = 25;
INSERT INTO `sys_options` VALUES
('tags_non_parsable', 'hi, hey, hello, all, i, i''m, i''d, am, for, in, to, a, the, on, it''s, is, my, of, are, from, i''m, me, you, and, we, not, will, at, where, there', @iCatTags, 'Ignored words (lower case, comma-separated)', 'text', '', '', 10, ''),
('tags_min_rating', '2', @iCatTags, 'Minimum tag repeats to display for browsing', 'digit', '', '', 20, ''),
('tags_perpage_browse', '30', @iCatTags, 'Number of tags per page displayed for browsing', 'digit', '', '', 30, ''),
('tags_show_limit', '50', @iCatTags, 'Number of hot tags displayed for browsing', 'digit', '', '', 40, '');


-- CAT: Permalinks
SET @iCatPermalinks = 26;
INSERT INTO `sys_options` VALUES
('enable_modrewrite', 'on', @iCatPermalinks, 'Enable friendly profile permalinks', 'checkbox', '', '', 10, ''),
('permalinks_browse', 'on', @iCatPermalinks, 'Enable friendly browse permalinks', 'checkbox', '', '', 20, '');


-- CAT: Categories
SET @iCatCategories = 27;
INSERT INTO `sys_options` VALUES
('categ_perpage_browse', '30', @iCatCategories, 'Number of categories to show on browse pages', 'digit', '', '', 10, ''),
('categ_show_limit', '50', @iCatCategories, 'Number of categories to show limit', 'digit', '', '', 20, ''),
('categ_show_columns', '3', @iCatCategories, 'Number of columns to show categories', 'digit', '', '', 30, '');


-- CAT: Hidden
SET @iCatHidden = 0;
INSERT INTO `sys_options` VALUES

('sys_tmp_version', '7.4.0', @iCatHidden, 'Dolphin version ', 'digit', '', '', 10, ''),
('license_code', '', @iCatHidden, 'Dolphin License Code', 'digit', '', '', 11, ''),
('license_expiration', '', @iCatHidden, 'Dolphin License Expiration', 'digit', '', '', 12, ''),
('license_checksum', '', @iCatHidden, 'Dolphin License Checksum', 'digit', '', '', 13, ''),
('enable_dolphin_footer', 'on', @iCatHidden, 'Enable BoonEx Footers', 'checkbox', '', '', 14, ''),

('splash_editor', 'on', @iCatHidden, '', 'checkbox', '', '', 30, ''),
('splash_code', '<div class="bx-splash bx-def-round-corners" style="background-image: url(templates/base/images/bx_splash_image.jpg);"><div class="bx-splash-txt"><div class="bx-splash-txt-cnt"><div class="bx-splash-txt-l1 bx-def-padding-sec-leftright"><h1 class="bx-cd-headline zoom"><span class="bx-cd-words-wrapper"><b class="bx-cd-word is-visible">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b></span></h1></div><div class="bx-splash-actions bx-hide-when-logged-in"><button class="bx-btn bx-btn-primary bx-btn-sa-join">Join</button><button class="bx-btn bx-def-margin-left bx-btn-sa-login">Login</button></div></div></div></div>', @iCatHidden, '', 'text', '', '', 31, ''),
('splash_visibility', 'index', @iCatHidden, '', 'text', '', '', 32, ''),
('splash_logged', 'on', @iCatHidden, '', 'checkbox', '', '', 33, ''),

('cmdDay', '10', @iCatHidden, '', 'digit', '', '', 50, ''),
('tags_last_parse_time', '0', @iCatHidden, 'Temporary value when tags cron-job was runed last time', 'digit', '', '', 51, ''),
('cupid_last_cron', '0', @iCatHidden, 'Temporary value when cupid mails checked was runed last time', 'text', '', '', 52, ''),
('sys_show_admin_help', 'on', @iCatHidden, 'Show help in admin dashboard', 'checkbox', '', '', 53, ''),
('sys_cron_time', '', @iCatHidden, 'Last cron execution time', 'digit', '', '', 54, ''),

('sys_main_logo', '', @iCatHidden, 'Main logo file name', 'text', '', '', 60, ''),
('sys_main_logo_w', '', @iCatHidden, 'Main logo width', 'digit', '', '', 61, ''),
('sys_main_logo_h', '', @iCatHidden, 'Main logo height', 'digit', '', '', 62, ''),
('main_div_width', '1140px', @iCatHidden, 'Width of the main container of the site', 'digit', '', '', 65, ''),

('sys_template_cache_image_enable', '', @iCatHidden, 'Enable cache for images (do not work for IE7)', 'checkbox', '', '', 70, ''),
('sys_template_cache_image_max_size', '5', @iCatHidden, 'Max image size to be cached(in kb)', 'digit', '', '', 71, ''),

('sys_sitemap_enable', '', @iCatHidden, 'Enable sitemap generation', 'checkbox', '', '', 80, ''),

('sys_captcha_default', 'sys_recaptcha', @iCatHidden, 'Default CAPTCHA', 'digit', '', '', 90, ''),
('sys_editor_default', 'sys_tinymce', @iCatHidden, 'Default HTML editor', 'digit', '', '', 91, '');




CREATE TABLE `sys_options_cats` (
  `ID` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `menu_order` float default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100;


INSERT INTO `sys_options_cats` VALUES(1, 'Profiles', 1);
INSERT INTO `sys_options_cats` VALUES(3, 'General', 3);
INSERT INTO `sys_options_cats` VALUES(4, 'Massmailer', 4);
INSERT INTO `sys_options_cats` VALUES(5, 'Memberships', 5);
INSERT INTO `sys_options_cats` VALUES(6, 'Moderation', 6);
INSERT INTO `sys_options_cats` VALUES(7, 'Site', 7);
INSERT INTO `sys_options_cats` VALUES(9, 'Privacy Groups', 9);
INSERT INTO `sys_options_cats` VALUES(11, 'Pruning', 11);
INSERT INTO `sys_options_cats` VALUES(12, 'Matches', 12);
INSERT INTO `sys_options_cats` VALUES(13, 'Template', 13);
INSERT INTO `sys_options_cats` VALUES(14, 'Security', 14);
INSERT INTO `sys_options_cats` VALUES(16, 'Watermark', 16);
INSERT INTO `sys_options_cats` VALUES(21, 'Languages', 21);
INSERT INTO `sys_options_cats` VALUES(22, 'IP Block List', 22);
INSERT INTO `sys_options_cats` VALUES(23, 'Antispam', 23);
INSERT INTO `sys_options_cats` VALUES(24, 'Caching', 24);
INSERT INTO `sys_options_cats` VALUES(25, 'Tags Settings', 25);
INSERT INTO `sys_options_cats` VALUES(26, 'Permalinks', 26);
INSERT INTO `sys_options_cats` VALUES(27, 'Categories Settings', 27);

-- --------------------------------------------------------

--
-- Table structure for table `sys_fave_list`
--

CREATE TABLE `sys_fave_list` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `When` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `HotPair` (`ID`,`Profile`),
  KEY `ID` (`ID`),
  KEY `Profile` (`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_fave_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_ip_list`
--

CREATE TABLE `sys_ip_list` (
  `ID` int(11) NOT NULL auto_increment,
  `From` int(10) unsigned NOT NULL,
  `To` int(10) unsigned NOT NULL,
  `Type` enum('allow','deny') NOT NULL default 'deny',
  `LastDT` int(11) unsigned NOT NULL,
  `Desc` varchar(128) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `From` (`From`),
  KEY `To` (`To`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_ip_list`
--

-- --------------------------------------------------------


--
-- Table structure for table `sys_ip_members_visits`
--

CREATE TABLE `sys_ip_members_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MemberID` int(10) unsigned NOT NULL,
  `From` int(10) unsigned NOT NULL,
  `DateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `From` (`From`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_ip_members_visits`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_localization_categories`
--

CREATE TABLE `sys_localization_categories` (
  `ID` int(6) unsigned NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_localization_categories`
--

INSERT INTO `sys_localization_categories` VALUES(1, 'System');

-- --------------------------------------------------------

--
-- Table structure for table `sys_localization_keys`
--

CREATE TABLE `sys_localization_keys` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `IDCategory` int(6) unsigned NOT NULL default '0',
  `Key` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Key` (`Key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_localization_keys`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_localization_languages`
--

CREATE TABLE `sys_localization_languages` (
  `ID` tinyint(3) unsigned NOT NULL auto_increment,
  `Name` varchar(5) NOT NULL default '',
  `Flag` varchar(2) NOT NULL default '',
  `Title` varchar(255) NOT NULL default '',
  `Direction` enum('LTR','RTL') NOT NULL DEFAULT 'LTR',
  `LanguageCountry` varchar(8) NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_localization_languages`
--

-- INSERT INTO `sys_localization_languages` VALUES(1, 'en', 'gb', 'English');

-- --------------------------------------------------------

--
-- Table structure for table `sys_localization_string_params`
--

CREATE TABLE `sys_localization_string_params` (
  `IDKey` int(10) unsigned NOT NULL default '0',
  `IDParam` tinyint(3) unsigned NOT NULL default '0',
  `Description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`IDKey`,`IDParam`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_localization_string_params`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_localization_strings`
--

CREATE TABLE `sys_localization_strings` (
  `IDKey` int(10) unsigned NOT NULL default '0',
  `IDLanguage` tinyint(3) unsigned NOT NULL default '0',
  `String` mediumtext NOT NULL,
  PRIMARY KEY  (`IDKey`,`IDLanguage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_localization_strings`
--

-- --------------------------------------------------------
--
-- Table structure for table `sys_acl_actions`
--

CREATE TABLE `sys_acl_actions` (
  `ID` smallint(5) unsigned NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL default '',
  `AdditionalParamName` varchar(80) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_actions`
--

INSERT INTO `sys_acl_actions` VALUES(1, 'send greetings', NULL);
INSERT INTO `sys_acl_actions` VALUES(2, 'view profiles', NULL);
INSERT INTO `sys_acl_actions` VALUES(3, 'vote', NULL);
INSERT INTO `sys_acl_actions` VALUES(4, 'send messages', NULL);
INSERT INTO `sys_acl_actions` VALUES(5, 'get other members emails', NULL);
INSERT INTO `sys_acl_actions` VALUES(6, 'comments post', NULL);
INSERT INTO `sys_acl_actions` VALUES(7, 'comments vote', NULL);
INSERT INTO `sys_acl_actions` VALUES(8, 'comments edit own', NULL);
INSERT INTO `sys_acl_actions` VALUES(9, 'comments remove own', NULL);
INSERT INTO `sys_acl_actions` VALUES(10, 'send friend request', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sys_acl_actions_track`
--

CREATE TABLE `sys_acl_actions_track` (
  `IDAction` smallint(5) unsigned NOT NULL default '0',
  `IDMember` int(10) unsigned NOT NULL default '0',
  `ActionsLeft` smallint(5) unsigned NOT NULL default '0',
  `ValidSince` datetime default NULL,
  PRIMARY KEY  (`IDAction`,`IDMember`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_actions_track`
--


-- --------------------------------------------------------


--
-- Table structure for table `sys_acl_matrix`
--

CREATE TABLE `sys_acl_matrix` (
  `IDLevel` smallint(5) unsigned NOT NULL default '0',
  `IDAction` smallint(5) unsigned NOT NULL default '0',
  `AllowedCount` smallint(5) unsigned default NULL,
  `AllowedPeriodLen` smallint(5) unsigned default NULL,
  `AllowedPeriodStart` datetime default NULL,
  `AllowedPeriodEnd` datetime default NULL,
  `AdditionalParamValue` varchar(255) default NULL,
  PRIMARY KEY  (`IDLevel`,`IDAction`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_matrix`
--

INSERT INTO `sys_acl_matrix` VALUES(1, 2, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(1, 3, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(1, 7, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 1, 4, 24, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 2, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 3, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 4, 10, 24, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 6, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 7, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 8, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 9, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(2, 10, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 1, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 2, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 3, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 4, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 6, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 7, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 8, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 9, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 10, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sys_acl_level_prices`
--

CREATE TABLE `sys_acl_level_prices` (
  `id` int(11) NOT NULL auto_increment,
  `IDLevel` smallint(5) unsigned NOT NULL default '0',
  `Days` int(10) unsigned NOT NULL default '1',
  `Price` float unsigned NOT NULL default '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`IDLevel`,`Days`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_level_prices`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_acl_levels`
--

CREATE TABLE `sys_acl_levels` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL default '',
  `Icon` varchar(255) NOT NULL default '',
  `Description` text NOT NULL default '',
  `Active` enum('yes','no') NOT NULL default 'no',
  `Purchasable` enum('yes','no') NOT NULL default 'yes',
  `Removable` enum('yes','no') NOT NULL default 'yes',
  `Order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_levels`
--

INSERT INTO `sys_acl_levels` VALUES(1, 'Non-member', 'non-member.png', '', 'yes', 'no', 'no', 0);
INSERT INTO `sys_acl_levels` VALUES(2, 'Standard',  'member.png', '', 'yes', 'no', 'no', 0);
INSERT INTO `sys_acl_levels` VALUES(3, 'Promotion',  'promotion.png', '', 'yes', 'no', 'no', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sys_messages`
--


--
-- Table structure for table `sys_messages`
--

CREATE TABLE `sys_messages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Sender` int(10) unsigned NOT NULL DEFAULT '0',
  `Recipient` int(10) unsigned NOT NULL DEFAULT '0',
  `Text` mediumtext NOT NULL,
  `Subject` varchar(255) NOT NULL DEFAULT '',
  `New` enum('0','1') NOT NULL DEFAULT '1',
  `Type` enum('letter','greeting') NOT NULL DEFAULT 'letter',
  `Trash` set('sender','recipient') NOT NULL,
  `TrashNotView` set('sender','recipient') NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Pair` (`Sender`,`Recipient`),
  KEY `TrashNotView` (`TrashNotView`),
  KEY `Trash` (`Trash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------


CREATE TABLE `sys_page_compose_pages` (
  `Name` varchar(255) NOT NULL default '',
  `Title` varchar(255) NOT NULL default '',
  `Order` int(10) unsigned NOT NULL default '0',
  `System` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_page_compose_pages` VALUES('index', 'Homepage', 1, 1);
INSERT INTO `sys_page_compose_pages` VALUES('member', 'Account', 6, 1);
INSERT INTO `sys_page_compose_pages` VALUES('profile', 'Profile', 7, 1);
INSERT INTO `sys_page_compose_pages` VALUES('pedit', 'Profile Edit', 8, 1);
INSERT INTO `sys_page_compose_pages` VALUES('profile_info', 'Profile Info', 14, 1);
INSERT INTO `sys_page_compose_pages` VALUES('profile_private', 'Profile Private', 15, 1);
INSERT INTO `sys_page_compose_pages` VALUES('browse_page', 'All Members', 17, 1);
INSERT INTO `sys_page_compose_pages` VALUES('mail_page', 'Mail Messages', 18, 1);
INSERT INTO `sys_page_compose_pages` VALUES('mail_page_view', 'Mail View Message', 19, 1);
INSERT INTO `sys_page_compose_pages` VALUES('mail_page_compose', 'Mail Compose Message', 20, 1);
INSERT INTO `sys_page_compose_pages` VALUES('search', 'Search Profiles', 21, 1);
INSERT INTO `sys_page_compose_pages` VALUES('join', 'Join Page', 22, 1);
INSERT INTO `sys_page_compose_pages` VALUES('friends', 'Friends', 23, 1);
INSERT INTO `sys_page_compose_pages` VALUES('communicator_page', 'Communicator', 24, 1);
INSERT INTO `sys_page_compose_pages` VALUES('search_home', 'Search Home', 25, 1);
INSERT INTO `sys_page_compose_pages` VALUES('tags_home', 'Tags Home', 26, 1);
INSERT INTO `sys_page_compose_pages` VALUES('tags_calendar', 'Tags Search', 27, 1);
INSERT INTO `sys_page_compose_pages` VALUES('tags_search', 'Tags Calendar', 28, 1);
INSERT INTO `sys_page_compose_pages` VALUES('tags_module', 'Tags Module', 29, 1);
INSERT INTO `sys_page_compose_pages` VALUES('categ_calendar', 'Categories Calendar', 30, 1);
INSERT INTO `sys_page_compose_pages` VALUES('categ_search', 'Categories Search', 31, 1);
INSERT INTO `sys_page_compose_pages` VALUES('categ_module', 'Categories Module', 32, 1);


CREATE TABLE `sys_page_compose_privacy` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL default '0',
  `block_id` int(11) unsigned NOT NULL default '0',
  `allow_view_block_to` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `block` (`user_id`, `block_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `sys_page_compose` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `Page` varchar(255) NOT NULL default '',
  `PageWidth` varchar(10) NOT NULL default '1140px',
  `Desc` text NOT NULL,
  `Caption` varchar(255) NOT NULL default '',
  `Column` tinyint(3) unsigned NOT NULL default '0',
  `Order` smallint(6) unsigned NOT NULL default '0',
  `Func` varchar(255) NOT NULL default '',
  `Content` text NOT NULL,
  `DesignBox` tinyint(3) unsigned NOT NULL default '1',
  `ColWidth` float unsigned NOT NULL default '0',
  `Visible` set('non','memb') NOT NULL default 'non,memb',
  `MinWidth` int(10) unsigned NOT NULL default '0',
  `Cache` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('', '1140px', 'RSS Feed', '_RSS Feed', 0, 0, 'Sample', 'RSS', 1, 0, 'non,memb', 0, 0),
('', '1140px', 'Simple HTML Block', '_HTML Block', 0, 0, 'Sample', 'Echo', 11, 0, 'non,memb', 0, 0),
('', '1140px', 'Simple Text Block', '_Text Block', 0, 0, 'Sample', 'Text', 11, 0, 'non,memb', 0, 0),

('index', '1140px', 'Shows statistic information about your site content', '_Site Stats', 2, 2, 'SiteStats', '', 1, 28.1, 'non,memb', 0, 3600),
('index', '1140px', 'Display form to subscribe to newsletters', '_Subscribe_block_caption', 2, 1, 'Subscribe', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'Quick search form', '_Quick Search', 0, 0, 'QuickSearch', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'List of featured profiles', '_featured members', 0, 0, 'Featured', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'Site Tags', '_Tags', 0, 0, 'Tags', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'Site Categories', '_Categories', 0, 0, 'Categories', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'List of profiles', '_Members', 2, 0, 'Members', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'Shows Login Form', '_Member_Login', 0, 0, 'LoginSection', '', 11, 28.1, 'non', 0, 86400),
('index', '1140px', '', '_BoonEx News', 1, 0, 'RSS', 'https://www.boonex.com/notes/featured_posts/?rss=1#4', 1, 71.9, 'non,memb', 0, 86400),
('index', '1140px', 'Download', '_sys_box_title_download', 0, 0, 'Download', '', 1, 28.1, 'non,memb', 0, 86400),

('member', '1140px', 'Quick Links', '_Quick Links', 1, 0, 'QuickLinks', '', 1, 71.9, 'memb', 0, 0),
('member', '1140px', 'Friend Requests', '_sys_bcpt_member_friend_requests', 2, 1, 'FriendRequests', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'New Messages', '_sys_bcpt_member_new_messages', 2, 2, 'NewMessages', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'Account Control', '_sys_bcpt_member_account_control', 2, 3, 'AccountControl', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'Member Friends', '_My Friends', 0, 0, 'Friends', '', 1, 28.1, 'memb', 0, 0),

('profile', '1140px', 'Profile cover', '_sys_bcpt_profile_cover', 1, 1, 'Cover', '', 0, 100, 'non,memb', 0, 0),
('profile', '1140px', 'Profile actions', '_Actions', 2, 2, 'ActionsMenu', '', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Friend request notification', '_FriendRequest', 2, 3, 'FriendRequest', '', 1, 28.1, 'memb', 0, 0),
('profile', '1140px', 'Profile description block', '_Description', 3, 2, 'Description', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_Admin Controls_View', 2, 4, 'PFBlock', '21', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_General Info_View', 2, 5, 'PFBlock', '17', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Profile rating form', '_rate profile', 2, 6, 'RateProfile', '', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Member friends list', '_Friends', 0, 0, 'Friends', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Mutual friends of viewing and viewed members', '_Mutual Friends', 0, 0, 'MutualFriends', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Comments on member profile', '_profile_comments', 0, 0, 'Cmts', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_Misc_View', 0, 0, 'PFBlock', '20', 1, 71.9, 'non,memb', 0, 0),

('profile_info', '1140px', '', '_FieldCaption_General Info_View', 1, 0, 'GeneralInfo', '', 1, 100, 'non,memb', 0, 0),
('profile_info', '1140px', '', '_Additional information', 1, 2, 'AdditionalInfo', '', 1, 100, 'non,memb', 0, 0),
('profile_info', '1140px', 'Profile''s description', '_Description', 1, 1, 'Description', '', 1, 100, 'non,memb', 0, 0),

('friends', '1140px', '', '_Member Friends', 1, 1, 'Friends', '', 1, 71.9, 'non,memb', 0, 0),
('friends', '1140px', '', '_Member Friends Requests', 2, 1, 'FriendsRequests', '', 1, 28.1, 'memb', 0, 0),
('friends', '1140px', '', '_Member Friends Mutual', 2, 2, 'FriendsMutual', '', 1, 28.1, 'memb', 0, 0),

('browse_page', '1140px', '', '_Browse', 2, 0, 'SettingsBlock', '', 0, 28.1, 'non,memb', 0, 0),
('browse_page', '1140px', '', '_People', 1, 0, 'SearchedMembersBlock', '', 1, 71.9, 'non,memb', 0, 0),

('mail_page', '1140px', '', '_Mail box', 1, 0, 'MailBox', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page', '1140px', '', '_My contacts', 2, 0, 'Contacts', '', 1, 28.1, 'non,memb', 0, 0),
('mail_page_view', '1140px', '', '_Mail box', 1, 0, 'ViewMessage', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page_view', '1140px', '', '_Archive', 2, 0, 'Archives', '', 1, 28.1, 'non,memb', 0, 0),
('mail_page_compose', '1140px', '', '_COMPOSE_H', 1, 0, 'ComposeMessage', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page_compose', '1140px', '', '_My contacts', 2, 0, 'Contacts', '', 1, 28.1, 'non,memb', 0, 0),

('search', '1140px', 'Search Results', '_Search result', 1, 0, 'Results', '', 1, 71.9, 'non,memb', 0, 0),
('search', '1140px', 'Search Form', '_Search profiles', 2, 0, 'SearchForm', '', 1, 28.1, 'non,memb', 0, 0),
('search_home', '1140px', 'Keyword Search', '_sys_box_title_search_keyword', 1, 0, 'Keyword', '', 1, 71.9, 'non,memb', 0, 86400),
('search_home', '1140px', 'People Search', '_sys_box_title_search_people', 2, 0, 'People', '', 1, 28.1, 'non,memb', 0, 0),

('join', '1140px', 'Join Form Block', '_Join_now', 1, 0, 'JoinForm', '', 1, 100, 'non', 413, 0),
('join', '1140px', 'Login Form Block', '_Login', 0, 0, 'LoginSection', 'no_join_text', 11, 100, 'non', 250, 86400),

('communicator_page', '1140px', '', '_sys_cnts_bcpt_connections', 1, 1, 'Connections', '', 1, 71.9, 'memb', 0, 0),
('communicator_page', '1140px', '', '_sys_cnts_bcpt_friend_requests', 2, 1, 'FriendRequests', '', 1, 28.1, 'memb', 0, 0),

('tags_home', '1140px', 'Recent Tags', '_tags_recent', 1, 0, 'Recent', '', 1, 28.1, 'non,memb', 0, 0),
('tags_home', '1140px', 'Popular Tags', '_popular_tags', 2, 0, 'Popular', '', 1, 71.9, 'non,memb', 0, 0),
('tags_calendar', '1140px', 'Calendar', '_tags_calendar', 1, 0, 'Calendar', '', 1, 100, 'non,memb', 0, 0),
('tags_calendar', '1140px', 'Date Tags', '_Tags', 1, 1, 'TagsDate', '', 1, 100, 'non,memb', 0, 0),
('tags_search', '1140px', 'Search Form', '_tags_search_form', 1, 0, 'Form', '', 1, 100, 'non,memb', 0, 86400),
('tags_search', '1140px', 'Founded Tags', '_tags_founded_tags', 1, 1, 'Founded', '', 1, 100, 'non,memb', 0, 0),
('tags_module', '1140px', 'Recent Tags', '_tags_recent', 1, 0, 'Recent', '', 1, 28.1, 'non,memb', 0, 0),
('tags_module', '1140px', 'All Tags', '_all_tags', 2, 0, 'All', '', 1, 71.9, 'non,memb', 0, 0),

('categ_calendar', '1140px', 'Calendar', '_categ_caption_calendar', 1, 0, 'Calendar', '', 1, 100, 'non,memb', 0, 0),
('categ_calendar', '1140px', 'Categories By Day', '_categ_caption_day', 1, 1, 'CategoriesDate', '', 1, 100, 'non,memb', 0, 0),
('categ_search', '1140px', 'Search Form', '_categ_caption_search_form', 1, 0, 'Form', '', 1, 100, 'non,memb', 0, 86400),
('categ_search', '1140px', 'Founded Categories', '_categ_caption_founded', 1, 1, 'Founded', '', 1, 100, 'non,memb', 0, 0),
('categ_module', '1140px', 'Common Categories', '_categ_caption_common', 1, 0, 'Common', '', 1, 28.1, 'non,memb', 0, 0),
('categ_module', '1140px', 'All Categories', '_categ_caption_all', 2, 0, 'All', '', 1, 71.9, 'non,memb', 0, 0),

('pedit', '1140px', 'Profile fields', '_edit_profile_info', 1, 1, 'Info', '', 1, 71.9, 'memb', 0, 0),
('pedit', '1140px', 'Profile privacy', '_edit_profile_privacy', 2, 1, 'Privacy', '', 1, 28.1, 'memb', 0, 0),
('pedit', '1140px', 'Profile membership', '_edit_profile_membership', 2, 2, 'Membership', '', 1, 28.1, 'memb', 0, 0),

('profile_private', '1140px', 'Actions that other members can do', '_Actions', 1, 0, 'ActionsMenu', '', 1, 28.1, 'non,memb', 0, 0),
('profile_private', '1140px', 'Some text to explain why this profile can not be viewed. Translation for this block is stored in ''_sys_profile_private_text'' language key.', '_sys_profile_private_text_title', 2, 0, 'PrivacyExplain', '', 1, 71.9, 'non,memb', 0, 0);


-- --------------------------------------------------------

--
-- Table structure for table `boon_sys_sessions`
--

CREATE TABLE IF NOT EXISTS `sys_sessions` (
  `id` varchar(32) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  `data` text collate utf8_unicode_ci,
  `date` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_stat_member`
--

CREATE TABLE `sys_stat_member` (
  `Type` varchar(10) NOT NULL,
  `SQL` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `sys_stat_member`
--

INSERT INTO `sys_stat_member` VALUES('mma', 'SELECT COUNT(*) FROM `sys_messages` WHERE `Recipient`=''__member_id__'' AND NOT FIND_IN_SET(''Recipient'', `sys_messages`.`Trash`)');
INSERT INTO `sys_stat_member` VALUES('mmn', 'SELECT COUNT(*) FROM `sys_messages` WHERE `Recipient`=''__member_id__'' AND `New`=''1'' AND NOT FIND_IN_SET(''Recipient'', `sys_messages`.`Trash`)');
INSERT INTO `sys_stat_member` VALUES('mfl', 'SELECT COUNT(*) FROM `sys_fave_list` WHERE `ID` = ''__member_id__'' ');
INSERT INTO `sys_stat_member` VALUES('mfr', 'SELECT COUNT(*) FROM `sys_friend_list` as f LEFT JOIN `Profiles` as p ON p.`ID` = f.`ID` WHERE f.`Profile` = __member_id__ AND f.`Check` = ''0'' AND p.`Status`=''Active''');
INSERT INTO `sys_stat_member` VALUES('mfa', 'SELECT COUNT(*) FROM `sys_friend_list` WHERE ( `ID`=''__member_id__'' OR `Profile`=''__member_id__'' ) AND `Check`=''1''');
INSERT INTO `sys_stat_member` VALUES('mgc', 'SELECT COUNT(*) FROM `sys_greetings` WHERE `ID` = ''__member_id__'' AND New = ''1''');
INSERT INTO `sys_stat_member` VALUES('mbc', 'SELECT COUNT(*) FROM `sys_block_list` WHERE `ID` = ''__member_id__''');
INSERT INTO `sys_stat_member` VALUES('mgmc', 'SELECT COUNT(*) FROM `sys_greetings` WHERE `Profile` = ''__member_id__'' AND New = ''1''');

-- --------------------------------------------------------

--
-- Table structure for table `sys_pre_values`
--

CREATE TABLE `sys_pre_values` (
  `Key` varchar(255) NOT NULL default '' COMMENT 'Key which defines link to values list',
  `Value` varchar(255) NOT NULL default '' COMMENT 'Simple value stored in the database',
  `Order` int(10) unsigned NOT NULL default '0',
  `LKey` varchar(255) NOT NULL default '' COMMENT 'Primary language key used for displaying this value',
  `LKey2` varchar(255) NOT NULL default '' COMMENT 'Additional key used in some other places',
  `LKey3` varchar(255) NOT NULL default '',
  `Extra` varchar(255) NOT NULL default '' COMMENT 'Some extra values. For example image link for sex',
  `Extra2` varchar(255) NOT NULL default '',
  `Extra3` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_pre_values`
--
INSERT INTO `sys_pre_values` VALUES 
('Country', 'AF', 1, '__Afghanistan', '', '', '', '', ''),
('Country', 'AX', 2, '__Aland_Islands', '', '', '', '', ''),
('Country', 'AL', 3, '__Albania', '', '', '', '', ''),
('Country', 'DZ', 4, '__Algeria', '', '', '', '', ''),
('Country', 'AS', 5, '__American Samoa', '', '', '', '', ''),
('Country', 'AD', 6, '__Andorra', '', '', '', '', ''),
('Country', 'AO', 7, '__Angola', '', '', '', '', ''),
('Country', 'AI', 8, '__Anguilla', '', '', '', '', ''),
('Country', 'AQ', 9, '__Antarctica', '', '', '', '', ''),
('Country', 'AG', 10, '__Antigua and Barbuda', '', '', '', '', ''),
('Country', 'AR', 11, '__Argentina', '', '', '', '', ''),
('Country', 'AM', 12, '__Armenia', '', '', '', '', ''),
('Country', 'AW', 13, '__Aruba', '', '', '', '', ''),
('Country', 'AU', 14, '__Australia', '', '', '', '', ''),
('Country', 'AT', 15, '__Austria', '', '', '', '', ''),
('Country', 'AZ', 16, '__Azerbaijan', '', '', '', '', ''),
('Country', 'BH', 17, '__Bahrain', '', '', '', '', ''),
('Country', 'BD', 18, '__Bangladesh', '', '', '', '', ''),
('Country', 'BB', 19, '__Barbados', '', '', '', '', ''),
('Country', 'BY', 20, '__Belarus', '', '', '', '', ''),
('Country', 'BE', 21, '__Belgium', '', '', '', '', ''),
('Country', 'BZ', 22, '__Belize', '', '', '', '', ''),
('Country', 'BJ', 23, '__Benin', '', '', '', '', ''),
('Country', 'BM', 24, '__Bermuda', '', '', '', '', ''),
('Country', 'BT', 25, '__Bhutan', '', '', '', '', ''),
('Country', 'BO', 26, '__Bolivia', '', '', '', '', ''),
('Country', 'BA', 27, '__Bosnia and Herzegovina', '', '', '', '', ''),
('Country', 'BW', 28, '__Botswana', '', '', '', '', ''),
('Country', 'BV', 29, '__Bouvet Island', '', '', '', '', ''),
('Country', 'BR', 30, '__Brazil', '', '', '', '', ''),
('Country', 'IO', 31, '__British Indian Ocean Territory', '', '', '', '', ''),
('Country', 'VG', 32, '__British Virgin Islands', '', '', '', '', ''),
('Country', 'BN', 33, '__Brunei Darussalam', '', '', '', '', ''),
('Country', 'BG', 34, '__Bulgaria', '', '', '', '', ''),
('Country', 'BF', 35, '__Burkina Faso', '', '', '', '', ''),
('Country', 'MM', 36, '__Burma', '', '', '', '', ''),
('Country', 'BI', 37, '__Burundi', '', '', '', '', ''),
('Country', 'KH', 38, '__Cambodia', '', '', '', '', ''),
('Country', 'CM', 39, '__Cameroon', '', '', '', '', ''),
('Country', 'CA', 40, '__Canada', '', '', '', '', ''),
('Country', 'CV', 41, '__Cape Verde', '', '', '', '', ''),
('Country', 'KY', 42, '__Cayman Islands', '', '', '', '', ''),
('Country', 'CF', 43, '__Central African Republic', '', '', '', '', ''),
('Country', 'TD', 44, '__Chad', '', '', '', '', ''),
('Country', 'CL', 45, '__Chile', '', '', '', '', ''),
('Country', 'CN', 46, '__China', '', '', '', '', ''),
('Country', 'CX', 47, '__Christmas Island', '', '', '', '', ''),
('Country', 'CC', 48, '__Cocos (Keeling) Islands', '', '', '', '', ''),
('Country', 'CO', 49, '__Colombia', '', '', '', '', ''),
('Country', 'KM', 50, '__Comoros', '', '', '', '', ''),
('Country', 'CD', 51, '__Congo, Democratic Republic of the', '', '', '', '', ''),
('Country', 'CG', 52, '__Congo, Republic of the', '', '', '', '', ''),
('Country', 'CK', 53, '__Cook Islands', '', '', '', '', ''),
('Country', 'CR', 54, '__Costa Rica', '', '', '', '', ''),
('Country', 'CI', 55, '__Cote d''Ivoire', '', '', '', '', ''),
('Country', 'HR', 56, '__Croatia', '', '', '', '', ''),
('Country', 'CU', 57, '__Cuba', '', '', '', '', ''),
('Country', 'CY', 58, '__Cyprus', '', '', '', '', ''),
('Country', 'CZ', 59, '__Czech Republic', '', '', '', '', ''),
('Country', 'DK', 60, '__Denmark', '', '', '', '', ''),
('Country', 'DJ', 61, '__Djibouti', '', '', '', '', ''),
('Country', 'DM', 62, '__Dominica', '', '', '', '', ''),
('Country', 'DO', 63, '__Dominican Republic', '', '', '', '', ''),
('Country', 'TL', 64, '__East Timor', '', '', '', '', ''),
('Country', 'EC', 65, '__Ecuador', '', '', '', '', ''),
('Country', 'EG', 66, '__Egypt', '', '', '', '', ''),
('Country', 'SV', 67, '__El Salvador', '', '', '', '', ''),
('Country', 'GQ', 68, '__Equatorial Guinea', '', '', '', '', ''),
('Country', 'ER', 69, '__Eritrea', '', '', '', '', ''),
('Country', 'EE', 70, '__Estonia', '', '', '', '', ''),
('Country', 'ET', 71, '__Ethiopia', '', '', '', '', ''),
('Country', 'FK', 72, '__Falkland Islands (Islas Malvinas)', '', '', '', '', ''),
('Country', 'FO', 73, '__Faroe Islands', '', '', '', '', ''),
('Country', 'FJ', 74, '__Fiji', '', '', '', '', ''),
('Country', 'FI', 75, '__Finland', '', '', '', '', ''),
('Country', 'FR', 76, '__France', '', '', '', '', ''),
('Country', 'GF', 77, '__French Guiana', '', '', '', '', ''),
('Country', 'PF', 78, '__French Polynesia', '', '', '', '', ''),
('Country', 'TF', 79, '__French Southern and Antarctic Lands', '', '', '', '', ''),
('Country', 'GA', 80, '__Gabon', '', '', '', '', ''),
('Country', 'GE', 81, '__Georgia', '', '', '', '', ''),
('Country', 'DE', 82, '__Germany', '', '', '', '', ''),
('Country', 'GH', 83, '__Ghana', '', '', '', '', ''),
('Country', 'GI', 84, '__Gibraltar', '', '', '', '', ''),
('Country', 'GR', 85, '__Greece', '', '', '', '', ''),
('Country', 'GL', 86, '__Greenland', '', '', '', '', ''),
('Country', 'GD', 87, '__Grenada', '', '', '', '', ''),
('Country', 'GP', 88, '__Guadeloupe', '', '', '', '', ''),
('Country', 'GU', 89, '__Guam', '', '', '', '', ''),
('Country', 'GT', 90, '__Guatemala', '', '', '', '', ''),
('Country', 'GG', 91, '__Guernsey', '', '', '', '', ''),
('Country', 'GN', 92, '__Guinea', '', '', '', '', ''),
('Country', 'GW', 93, '__Guinea-Bissau', '', '', '', '', ''),
('Country', 'GY', 94, '__Guyana', '', '', '', '', ''),
('Country', 'HT', 95, '__Haiti', '', '', '', '', ''),
('Country', 'HM', 96, '__Heard Island and McDonald Islands', '', '', '', '', ''),
('Country', 'VA', 97, '__Holy See (Vatican City)', '', '', '', '', ''),
('Country', 'HN', 98, '__Honduras', '', '', '', '', ''),
('Country', 'HK', 99, '__Hong Kong (SAR)', '', '', '', '', ''),
('Country', 'HU', 100, '__Hungary', '', '', '', '', ''),
('Country', 'IS', 101, '__Iceland', '', '', '', '', ''),
('Country', 'IN', 102, '__India', '', '', '', '', ''),
('Country', 'ID', 103, '__Indonesia', '', '', '', '', ''),
('Country', 'IR', 104, '__Iran', '', '', '', '', ''),
('Country', 'IQ', 105, '__Iraq', '', '', '', '', ''),
('Country', 'IE', 106, '__Ireland', '', '', '', '', ''),
('Country', 'IM', 107, '__Isle_of_Man', '', '', '', '', ''),
('Country', 'IL', 108, '__Israel', '', '', '', '', ''),
('Country', 'IT', 109, '__Italy', '', '', '', '', ''),
('Country', 'JM', 110, '__Jamaica', '', '', '', '', ''),
('Country', 'JP', 111, '__Japan', '', '', '', '', ''),
('Country', 'JE', 112, '__Jersey', '', '', '', '', ''),
('Country', 'JO', 113, '__Jordan', '', '', '', '', ''),
('Country', 'KZ', 114, '__Kazakhstan', '', '', '', '', ''),
('Country', 'KE', 115, '__Kenya', '', '', '', '', ''),
('Country', 'KI', 116, '__Kiribati', '', '', '', '', ''),
('Country', 'KP', 117, '__Korea, North', '', '', '', '', ''),
('Country', 'KR', 118, '__Korea, South', '', '', '', '', ''),
('Country', 'KW', 119, '__Kuwait', '', '', '', '', ''),
('Country', 'KG', 120, '__Kyrgyzstan', '', '', '', '', ''),
('Country', 'LA', 121, '__Laos', '', '', '', '', ''),
('Country', 'LV', 122, '__Latvia', '', '', '', '', ''),
('Country', 'LB', 123, '__Lebanon', '', '', '', '', ''),
('Country', 'LS', 124, '__Lesotho', '', '', '', '', ''),
('Country', 'LR', 125, '__Liberia', '', '', '', '', ''),
('Country', 'LY', 126, '__Libya', '', '', '', '', ''),
('Country', 'LI', 127, '__Liechtenstein', '', '', '', '', ''),
('Country', 'LT', 128, '__Lithuania', '', '', '', '', ''),
('Country', 'LU', 129, '__Luxembourg', '', '', '', '', ''),
('Country', 'MO', 130, '__Macao', '', '', '', '', ''),
('Country', 'MK', 131, '__Macedonia, The Former Yugoslav Republic of', '', '', '', '', ''),
('Country', 'MG', 132, '__Madagascar', '', '', '', '', ''),
('Country', 'MW', 133, '__Malawi', '', '', '', '', ''),
('Country', 'MY', 134, '__Malaysia', '', '', '', '', ''),
('Country', 'MV', 135, '__Maldives', '', '', '', '', ''),
('Country', 'ML', 136, '__Mali', '', '', '', '', ''),
('Country', 'MT', 137, '__Malta', '', '', '', '', ''),
('Country', 'MH', 138, '__Marshall Islands', '', '', '', '', ''),
('Country', 'MQ', 139, '__Martinique', '', '', '', '', ''),
('Country', 'MR', 140, '__Mauritania', '', '', '', '', ''),
('Country', 'MU', 141, '__Mauritius', '', '', '', '', ''),
('Country', 'YT', 142, '__Mayotte', '', '', '', '', ''),
('Country', 'MX', 143, '__Mexico', '', '', '', '', ''),
('Country', 'FM', 144, '__Micronesia, Federated States of', '', '', '', '', ''),
('Country', 'MD', 145, '__Moldova', '', '', '', '', ''),
('Country', 'MC', 146, '__Monaco', '', '', '', '', ''),
('Country', 'MN', 147, '__Mongolia', '', '', '', '', ''),
('Country', 'ME', 148, '__Montenegro', '', '', '', '', ''),
('Country', 'MS', 149, '__Montserrat', '', '', '', '', ''),
('Country', 'MA', 150, '__Morocco', '', '', '', '', ''),
('Country', 'MZ', 151, '__Mozambique', '', '', '', '', ''),
('Country', 'NA', 152, '__Namibia', '', '', '', '', ''),
('Country', 'NR', 153, '__Nauru', '', '', '', '', ''),
('Country', 'NP', 154, '__Nepal', '', '', '', '', ''),
('Country', 'NL', 155, '__Netherlands', '', '', '', '', ''),
('Country', 'AN', 156, '__Netherlands Antilles', '', '', '', '', ''),
('Country', 'NC', 157, '__New Caledonia', '', '', '', '', ''),
('Country', 'NZ', 158, '__New Zealand', '', '', '', '', ''),
('Country', 'NI', 159, '__Nicaragua', '', '', '', '', ''),
('Country', 'NE', 160, '__Niger', '', '', '', '', ''),
('Country', 'NG', 161, '__Nigeria', '', '', '', '', ''),
('Country', 'NU', 162, '__Niue', '', '', '', '', ''),
('Country', 'NF', 163, '__Norfolk Island', '', '', '', '', ''),
('Country', 'MP', 164, '__Northern Mariana Islands', '', '', '', '', ''),
('Country', 'NO', 165, '__Norway', '', '', '', '', ''),
('Country', 'OM', 166, '__Oman', '', '', '', '', ''),
('Country', 'PK', 167, '__Pakistan', '', '', '', '', ''),
('Country', 'PW', 168, '__Palau', '', '', '', '', ''),
('Country', 'PS', 169, '__Palestinian Territory, Occupied', '', '', '', '', ''),
('Country', 'PA', 170, '__Panama', '', '', '', '', ''),
('Country', 'PG', 171, '__Papua New Guinea', '', '', '', '', ''),
('Country', 'PY', 172, '__Paraguay', '', '', '', '', ''),
('Country', 'PE', 173, '__Peru', '', '', '', '', ''),
('Country', 'PH', 174, '__Philippines', '', '', '', '', ''),
('Country', 'PN', 175, '__Pitcairn Islands', '', '', '', '', ''),
('Country', 'PL', 176, '__Poland', '', '', '', '', ''),
('Country', 'PT', 177, '__Portugal', '', '', '', '', ''),
('Country', 'PR', 178, '__Puerto Rico', '', '', '', '', ''),
('Country', 'QA', 179, '__Qatar', '', '', '', '', ''),
('Country', 'RE', 180, '__Reunion', '', '', '', '', ''),
('Country', 'RO', 181, '__Romania', '', '', '', '', ''),
('Country', 'RU', 182, '__Russia', '', '', '', '', ''),
('Country', 'RW', 183, '__Rwanda', '', '', '', '', ''),
('Country', 'SH', 184, '__Saint Helena', '', '', '', '', ''),
('Country', 'KN', 185, '__Saint Kitts and Nevis', '', '', '', '', ''),
('Country', 'LC', 186, '__Saint Lucia', '', '', '', '', ''),
('Country', 'PM', 187, '__Saint Pierre and Miquelon', '', '', '', '', ''),
('Country', 'VC', 188, '__Saint Vincent and the Grenadines', '', '', '', '', ''),
('Country', 'BL', 189, '__Saint_Barthelemy', '', '', '', '', ''),
('Country', 'MF', 190, '__Saint_Martin_French_part', '', '', '', '', ''),
('Country', 'WS', 191, '__Samoa', '', '', '', '', ''),
('Country', 'SM', 192, '__San Marino', '', '', '', '', ''),
('Country', 'ST', 193, '__Sao Tome and Principe', '', '', '', '', ''),
('Country', 'SA', 194, '__Saudi Arabia', '', '', '', '', ''),
('Country', 'SN', 195, '__Senegal', '', '', '', '', ''),
('Country', 'RS', 196, '__Serbia', '', '', '', '', ''),
('Country', 'SC', 197, '__Seychelles', '', '', '', '', ''),
('Country', 'SL', 198, '__Sierra Leone', '', '', '', '', ''),
('Country', 'SG', 199, '__Singapore', '', '', '', '', ''),
('Country', 'SK', 200, '__Slovakia', '', '', '', '', ''),
('Country', 'SI', 201, '__Slovenia', '', '', '', '', ''),
('Country', 'SB', 202, '__Solomon Islands', '', '', '', '', ''),
('Country', 'SO', 203, '__Somalia', '', '', '', '', ''),
('Country', 'ZA', 204, '__South Africa', '', '', '', '', ''),
('Country', 'GS', 205, '__South Georgia and the South Sandwich Islands', '', '', '', '', ''),
('Country', 'ES', 206, '__Spain', '', '', '', '', ''),
('Country', 'LK', 207, '__Sri Lanka', '', '', '', '', ''),
('Country', 'SD', 208, '__Sudan', '', '', '', '', ''),
('Country', 'SR', 209, '__Suriname', '', '', '', '', ''),
('Country', 'SJ', 210, '__Svalbard', '', '', '', '', ''),
('Country', 'SZ', 211, '__Swaziland', '', '', '', '', ''),
('Country', 'SE', 212, '__Sweden', '', '', '', '', ''),
('Country', 'CH', 213, '__Switzerland', '', '', '', '', ''),
('Country', 'SY', 214, '__Syria', '', '', '', '', ''),
('Country', 'TW', 215, '__Taiwan', '', '', '', '', ''),
('Country', 'TJ', 216, '__Tajikistan', '', '', '', '', ''),
('Country', 'TZ', 217, '__Tanzania', '', '', '', '', ''),
('Country', 'TH', 218, '__Thailand', '', '', '', '', ''),
('Country', 'BS', 219, '__The Bahamas', '', '', '', '', ''),
('Country', 'GM', 220, '__The Gambia', '', '', '', '', ''),
('Country', 'TG', 221, '__Togo', '', '', '', '', ''),
('Country', 'TK', 222, '__Tokelau', '', '', '', '', ''),
('Country', 'TO', 223, '__Tonga', '', '', '', '', ''),
('Country', 'TT', 224, '__Trinidad and Tobago', '', '', '', '', ''),
('Country', 'TN', 225, '__Tunisia', '', '', '', '', ''),
('Country', 'TR', 226, '__Turkey', '', '', '', '', ''),
('Country', 'TM', 227, '__Turkmenistan', '', '', '', '', ''),
('Country', 'TC', 228, '__Turks and Caicos Islands', '', '', '', '', ''),
('Country', 'TV', 229, '__Tuvalu', '', '', '', '', ''),
('Country', 'UG', 230, '__Uganda', '', '', '', '', ''),
('Country', 'UA', 231, '__Ukraine', '', '', '', '', ''),
('Country', 'AE', 232, '__United Arab Emirates', '', '', '', '', ''),
('Country', 'GB', 233, '__United Kingdom', '', '', '', '', ''),
('Country', 'US', 234, '__United States', '', '', '', '', ''),
('Country', 'UM', 235, '__United States Minor Outlying Islands', '', '', '', '', ''),
('Country', 'UY', 236, '__Uruguay', '', '', '', '', ''),
('Country', 'UZ', 237, '__Uzbekistan', '', '', '', '', ''),
('Country', 'VU', 238, '__Vanuatu', '', '', '', '', ''),
('Country', 'VE', 239, '__Venezuela', '', '', '', '', ''),
('Country', 'VN', 240, '__Vietnam', '', '', '', '', ''),
('Country', 'VI', 241, '__Virgin Islands', '', '', '', '', ''),
('Country', 'WF', 242, '__Wallis and Futuna', '', '', '', '', ''),
('Country', 'EH', 243, '__Western Sahara', '', '', '', '', ''),
('Country', 'YE', 244, '__Yemen', '', '', '', '', ''),
('Country', 'ZM', 245, '__Zambia', '', '', '', '', ''),
('Country', 'ZW', 246, '__Zimbabwe', '', '', '', '', '');

INSERT INTO `sys_pre_values` VALUES('Sex', 'male', 1, '_Male', '_LookinMale', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Sex', 'female', 2, '_Female', '_LookinFemale', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Sex', 'intersex', 3, '_Intersex', '_LookinIntersex', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '1', 1, '__4''7" (140cm) or below', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '2', 2, '__4''8" - 4''11" (141-150cm)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '3', 3, '__5''0" - 5''3" (151-160cm)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '4', 4, '__5''4" - 5''7" (161-170cm)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '5', 5, '__5''8" - 5''11" (171-180cm)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '6', 6, '__6''0" - 6''3" (181-190cm)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Height', '7', 7, '__6''4" (191cm) or above', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '1', 1, '__Average', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '2', 2, '__Ample', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '3', 3, '__Athletic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '4', 4, '__Cuddly', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '5', 5, '__Slim', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('BodyType', '6', 6, '__Very Cuddly', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '1', 1, '__Adventist', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '2', 2, '__Agnostic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '3', 3, '__Atheist', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '4', 4, '__Baptist', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '5', 5, '__Buddhist', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '6', 6, '__Caodaism', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '7', 7, '__Catholic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '8', 8, '__Christian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '9', 9, '__Hindu', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '10', 10, '__Iskcon', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '11', 11, '__Jainism', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '12', 12, '__Jewish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '13', 13, '__Methodist', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '14', 14, '__Mormon', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '15', 15, '__Moslem', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '16', 16, '__Orthodox', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '17', 17, '__Pentecostal', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '18', 18, '__Protestant', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '19', 19, '__Quaker', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '20', 20, '__Scientology', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '21', 21, '__Shinto', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '22', 22, '__Sikhism', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '23', 23, '__Spiritual', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '24', 24, '__Taoism', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '25', 25, '__Wiccan', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Religion', '26', 26, '__Other', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '1', 1, '__African', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '2', 2, '__African American', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '3', 3, '__Asian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '4', 4, '__Caucasian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '5', 5, '__East Indian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '6', 6, '__Hispanic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '7', 7, '__Indian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '8', 8, '__Latino', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '9', 9, '__Mediterranean', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '10', 10, '__Middle Eastern', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Ethnicity', '11', 11, '__Mixed', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '1', 1, '__Single', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '2', 2, '__Attached', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '3', 3, '__Divorced', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '4', 4, '__Married', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '5', 5, '__Separated', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('MaritalStatus', '6', 6, '__Widow', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '0', 0, '__English', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '1', 1, '__Afrikaans', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '2', 2, '__Arabic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '3', 3, '__Bulgarian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '4', 4, '__Burmese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '5', 5, '__Cantonese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '6', 6, '__Croatian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '7', 7, '__Danish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '8', 8, '__Dutch', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '9', 9, '__Esperanto', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '10', 10, '__Estonian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '11', 11, '__Finnish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '12', 12, '__French', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '13', 13, '__German', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '14', 14, '__Greek', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '15', 15, '__Gujrati', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '16', 16, '__Hebrew', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '17', 17, '__Hindi', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '18', 18, '__Hungarian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '19', 19, '__Icelandic', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '20', 20, '__Indian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '21', 21, '__Indonesian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '22', 22, '__Italian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '23', 23, '__Japanese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '24', 24, '__Korean', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '25', 25, '__Latvian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '26', 26, '__Lithuanian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '27', 27, '__Malay', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '28', 28, '__Mandarin', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '29', 29, '__Marathi', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '30', 30, '__Moldovian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '31', 31, '__Nepalese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '32', 32, '__Norwegian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '33', 33, '__Persian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '34', 34, '__Polish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '35', 35, '__Portuguese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '36', 36, '__Punjabi', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '37', 37, '__Romanian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '38', 38, '__Russian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '39', 39, '__Serbian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '40', 40, '__Spanish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '41', 41, '__Swedish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '42', 42, '__Tagalog', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '43', 43, '__Taiwanese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '44', 44, '__Tamil', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '45', 45, '__Telugu', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '46', 46, '__Thai', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '47', 47, '__Tongan', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '48', 48, '__Turkish', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '49', 49, '__Ukrainian', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '50', 50, '__Urdu', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '51', 51, '__Vietnamese', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Language', '52', 52, '__Visayan', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '1', 1, '__High School graduate', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '2', 2, '__Some college', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '3', 3, '__College student', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '4', 4, '__AA (2 years college)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '5', 5, '__BA/BS (4 years college)', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '6', 6, '__Some grad school', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '7', 7, '__Grad school student', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '8', 8, '__MA/MS/MBA', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '9', 9, '__PhD/Post doctorate', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Education', '10', 10, '__JD', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Income', '1', 1, '__$10,000/year and less', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Income', '2', 2, '__$10,000-$30,000/year', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Income', '3', 3, '__$30,000-$50,000/year', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Income', '4', 4, '__$50,000-$70,000/year', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Income', '5', 5, '__$70,000/year and more', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Smoker', '1', 1, '__No', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Smoker', '2', 2, '__Rarely', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Smoker', '3', 3, '__Often', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Smoker', '4', 4, '__Very often', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Drinker', '1', 1, '__No', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Drinker', '2', 2, '__Rarely', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Drinker', '3', 3, '__Often', '', '', '', '', '');
INSERT INTO `sys_pre_values` VALUES('Drinker', '4', 4, '__Very often', '', '', '', '', '');
-- --------------------------------------------------------

--
-- Table structure for table `sys_profile_fields`
--

CREATE TABLE `sys_profile_fields` (
  `ID` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Type` enum('text','html_area','area','pass','date','select_one','select_set','num','range','bool','system','block') NOT NULL DEFAULT 'text',
  `Control` enum('select','checkbox','radio') DEFAULT NULL COMMENT 'input element for selectors',
  `Extra` text NOT NULL,
  `Min` float DEFAULT NULL,
  `Max` float DEFAULT NULL,
  `Values` text NOT NULL,
  `UseLKey` enum('LKey','LKey2','LKey3') NOT NULL DEFAULT 'LKey',
  `Check` text NOT NULL,
  `Unique` tinyint(1) NOT NULL DEFAULT '0',
  `Default` text NOT NULL,
  `Mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `Deletable` tinyint(1) NOT NULL DEFAULT '1',
  `JoinPage` int(10) unsigned NOT NULL DEFAULT '0',
  `JoinBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `JoinOrder` float DEFAULT NULL,
  `EditOwnBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `EditOwnOrder` float DEFAULT NULL,
  `EditAdmBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `EditAdmOrder` float DEFAULT NULL,
  `EditModBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `EditModOrder` float DEFAULT NULL,
  `ViewMembBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `ViewMembOrder` float DEFAULT NULL,
  `ViewAdmBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `ViewAdmOrder` float DEFAULT NULL,
  `ViewModBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `ViewModOrder` float DEFAULT NULL,
  `ViewVisBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `ViewVisOrder` float DEFAULT NULL,
  `SearchParams` text NOT NULL,
  `SearchSimpleBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `SearchSimpleOrder` float DEFAULT NULL,
  `SearchQuickBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `SearchQuickOrder` float DEFAULT NULL,
  `SearchAdvBlock` int(10) unsigned NOT NULL DEFAULT '0',
  `SearchAdvOrder` float DEFAULT NULL,
  `MatchField` int(10) unsigned NOT NULL DEFAULT '0',
  `MatchPercent` tinyint(7) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_profile_fields`
--

INSERT INTO `sys_profile_fields` VALUES(1, 'ID', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 1, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 17, 1, 17, 1, 0, NULL, '', 17, 2, 17, 2, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(2, 'NickName', 'text', NULL, '', 4, 16, '', 'LKey', 'return ( preg_match( ''/^[a-zA-Z0-9_-]+$/'', $arg0 ) and !file_exists( $dir[''root''] . $arg0 ) );', 1, '', 1, 0, 0, 17, 2, 0, NULL, 0, NULL, 17, 1, 0, NULL, 17, 3, 17, 2, 0, NULL, '', 17, 1, 17, 1, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(3, 'Password', 'pass', NULL, '', 5, 16, '', 'LKey', '', 0, '', 1, 0, 0, 17, 5, 17, 7, 17, 6, 17, 8, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(4, 'Email', 'text', NULL, '', 6, NULL, '', 'LKey', 'return (bool) preg_match(''/^([a-z0-9\\+\\_\\-\\.]+)@([a-z0-9\\+\\_\\-\\.]+)$/i'', $arg0);', 1, '', 1, 0, 0, 17, 6, 17, 4, 17, 4, 17, 2, 0, NULL, 21, 1, 21, 1, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(5, 'DateReg', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 21, 2, 21, 2, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(6, 'DateLastEdit', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 21, 4, 21, 4, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(7, 'Status', 'system', NULL, '', NULL, NULL, 'Unconfirmed\nApproval\nActive\nRejected\nSuspended', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 21, 1, 21, 1, 0, NULL, 17, 6, 17, 3, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(8, 'DateLastLogin', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 21, 3, 21, 3, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(9, 'Featured', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 21, 2, 21, 2, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(10, 'Sex', 'select_one', 'select', '', NULL, NULL, '#!Sex', 'LKey', '', 0, '', 1, 0, 0, 20, 2, 17, 6, 17, 5, 17, 3, 17, 4, 17, 7, 17, 4, 17, 5, '', 20, 2, 20, 2, 17, 2, 11, 30);
INSERT INTO `sys_profile_fields` VALUES(11, 'LookingFor', 'select_set', 'checkbox', '', NULL, NULL, '#!Sex', 'LKey2', '', 0, '', 0, 0, 0, 0, NULL, 20, 1, 20, 1, 17, 7, 17, 8, 17, 11, 17, 5, 17, 7, '', 20, 1, 20, 1, 0, NULL, 10, 30);
INSERT INTO `sys_profile_fields` VALUES(12, 'DescriptionMe', 'area', NULL, '', 20, NULL, '', 'LKey', '', 0, '', 1, 0, 0, 20, 4, 20, 4, 20, 4, 20, 2, 0, NULL, 0, NULL, 22, 2, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(13, 'DateOfBirth', 'date', NULL, '', 18, 75, '', 'LKey', '', 0, '', 1, 0, 0, 20, 3, 20, 2, 20, 2, 17, 4, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 20, 3, 20, 3, 17, 3, 13, 0);
INSERT INTO `sys_profile_fields` VALUES(15, 'Country', 'select_one', 'select', '', NULL, NULL, '#!Country', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 20, 5, 20, 5, 17, 5, 17, 6, 17, 9, 20, 2, 17, 4, '', 20, 4, 20, 4, 20, 1, 15, 25);
INSERT INTO `sys_profile_fields` VALUES(16, 'City', 'text', NULL, '', 2, 64, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 20, 6, 20, 6, 17, 6, 17, 7, 17, 10, 20, 3, 17, 6, '', 0, NULL, 0, NULL, 20, 2, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(17, 'General Info', 'block', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, 1, 0, 1, 0, 2, 0, 2, 0, 1, 0, 1, 0, 1, 0, 1, '', 0, 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(18, 'Location', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 20, 5, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(19, 'Keyword', 'system', NULL, 'DescriptionMe\nHeadline', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 20, 3, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(20, 'Misc Info', 'block', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 1, 0, 2, 0, 2, 0, 3, 0, 3, 0, 2, 0, 2, 0, 2, 0, 2, '', 0, 2, 0, 2, 0, 2, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(21, 'Admin Controls', 'block', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 0, NULL, 0, 1, 0, 1, 0, NULL, 0, 3, 0, 4, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(23, 'Couple', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 1, 0, 0, 17, 1, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 17, 1, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(24, 'Captcha', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 1, 0, 0, 20, 6, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(41, 'EmailNotify', 'bool', 'checkbox', '', NULL, NULL, '', 'LKey', '', 0, '1', 0, 0, 0, 0, NULL, 17, 5, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(39, 'zip', 'text', NULL, '', 1, 32, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 20, 7, 20, 7, 20, 3, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(34, 'DateLastNav', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(37, 'aff_num', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '0', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(38, 'Tags', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 20, 9, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 20, 5, 20, 5, 20, 4, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(42, 'TermsOfUse', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(44, 'Age', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '0', 0, 1, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 17, 5, 17, 8, 20, 1, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(45, 'ProfilePhoto', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 20, 5, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(46, 'UserStatus', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(47, 'UserStatusMessage', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(48, 'UserStatusMessageWhen', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(49, 'Avatar', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(51, 'Height', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 8, 20, 8, 20, 4, 20, 1, 20, 1, 0, NULL, 20, 1, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(52, 'Weight', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 9, 20, 9, 20, 5, 20, 2, 20, 2, 0, NULL, 20, 2, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(53, 'Income', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 10, 20, 10, 20, 6, 20, 3, 20, 3, 0, NULL, 20, 3, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(54, 'Occupation', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 11, 20, 11, 20, 7, 20, 4, 20, 4, 0, NULL, 20, 4, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(55, 'Religion', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 12, 20, 12, 20, 8, 20, 5, 20, 5, 0, NULL, 20, 5, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(56, 'Education', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 13, 20, 13, 20, 9, 20, 6, 20, 6, 0, NULL, 20, 6, '', 0, NULL, 0, NULL, 0, NULL, 56, 0);
INSERT INTO `sys_profile_fields` VALUES(57, 'RelationshipStatus', 'select_one', 'select', '', NULL, NULL, 'Single\nIn a Relationship\nEngaged\nMarried\nIt''s Complicated\nIn an Open Relationship', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 14, 20, 14, 20, 10, 20, 7, 20, 7, 0, NULL, 20, 7, '', 0, NULL, 0, NULL, 0, NULL, 57, 10);
INSERT INTO `sys_profile_fields` VALUES(58, 'Hobbies', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 15, 20, 15, 20, 11, 20, 8, 20, 8, 0, NULL, 20, 8, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(59, 'Interests', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 16, 20, 16, 20, 12, 20, 9, 20, 9, 0, NULL, 20, 9, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(60, 'Ethnicity', 'text', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 17, 20, 17, 20, 13, 20, 10, 20, 10, 0, NULL, 20, 10, '', 0, NULL, 0, NULL, 0, NULL, 60, 5);
INSERT INTO `sys_profile_fields` VALUES(61, 'FavoriteSites', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 18, 20, 18, 20, 14, 20, 11, 20, 11, 0, NULL, 20, 11, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(62, 'FavoriteMusic', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 19, 20, 19, 20, 15, 20, 12, 20, 12, 0, NULL, 20, 12, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(63, 'FavoriteFilms', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 20, 20, 20, 20, 16, 20, 13, 20, 13, 0, NULL, 20, 13, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(64, 'FavoriteBooks', 'area', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 1, 0, 0, NULL, 20, 21, 20, 21, 20, 17, 20, 14, 20, 14, 0, NULL, 20, 14, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(66, 'FullName', 'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 1, 0, 20, 1, 17, 2, 17, 2, 0, NULL, 17, 2, 17, 5, 0, NULL, 17, 2, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(67, 'allow_view_to', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(68, 'Agree', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, 20, 7, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(NULL, 'FirstName', 'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(NULL, 'LastName',  'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);


-- --------------------------------------------------------

--
-- Table structure for table `sys_acl_levels_members`
--

CREATE TABLE `sys_acl_levels_members` (
  `IDMember` int(10) unsigned NOT NULL default '0',
  `IDLevel` smallint(5) unsigned NOT NULL default '0',
  `DateStarts` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateExpires` datetime default NULL,
  `TransactionID` varchar(16) NOT NULL default '',
  `Expiring` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`IDMember`,`IDLevel`,`DateStarts`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_acl_levels_members`
--


-- --------------------------------------------------------

--
-- Table structure for table `Profiles`
--

CREATE TABLE `Profiles` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `NickName` varchar(255) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `Password` varchar(40) NOT NULL default '',
  `Salt` varchar(10) NOT NULL default '',
  `Status` enum('Unconfirmed','Approval','Active','Rejected','Suspended') NOT NULL default 'Unconfirmed',
  `Role` tinyint(4) unsigned NOT NULL default '1',
  `Couple` int(10) unsigned NOT NULL default '0',
  `Sex` varchar(255) NOT NULL default '',
  `LookingFor` set('male','female') NOT NULL default '',
  `DescriptionMe` text NOT NULL,
  `Country` varchar(255) NOT NULL default '',
  `City` varchar(255) NOT NULL,
  `DateOfBirth` date NOT NULL,
  `Featured` tinyint(1) NOT NULL default '0',
  `DateReg` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateLastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateLastLogin` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateLastNav` datetime NOT NULL default '0000-00-00 00:00:00',
  `aff_num` int(10) unsigned NOT NULL default '0',
  `Tags` varchar(255) NOT NULL default '',
  `zip` varchar(255) NOT NULL,
  `EmailNotify` tinyint(1) NOT NULL default '1',
  `LangID` int(11) NOT NULL,
  `UpdateMatch` tinyint(1) NOT NULL default '1',
  `Views` int(11) NOT NULL,
  `Rate` float NOT NULL,
  `RateCount` int(11) NOT NULL,
  `CommentsCount` int(11) NOT NULL,
  `PrivacyDefaultGroup` int(11) NOT NULL default '3',
  `allow_view_to` int(11) NOT NULL default '3', 
  `UserStatus` varchar(64) NOT NULL default 'online',
  `UserStatusMessage` varchar(255) NOT NULL default '',
  `UserStatusMessageWhen` int(10) NOT NULL,
  `Avatar` int(10) unsigned NOT NULL,
  `Height` varchar(255) NOT NULL,
  `Weight` varchar(255) NOT NULL,
  `Income` varchar(255) NOT NULL,
  `Occupation` varchar(255) NOT NULL,
  `Religion` varchar(255) NOT NULL,
  `Education` varchar(255) NOT NULL,
  `RelationshipStatus` enum('Single','In a Relationship','Engaged','Married','It''s Complicated','In an Open Relationship') default NULL,
  `Hobbies` text NOT NULL,
  `Interests` text NOT NULL,
  `Ethnicity` varchar(255) NOT NULL,
  `FavoriteSites` text NOT NULL,
  `FavoriteMusic` text NOT NULL,
  `FavoriteFilms` text NOT NULL,
  `FavoriteBooks` text NOT NULL,
  `FullName` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `NickName` (`NickName`),
  KEY `Country` (`Country`),
  KEY `DateOfBirth` (`DateOfBirth`),
  KEY `DateReg` (`DateReg`),
  KEY `DateLastNav` (`DateLastNav`),
  FULLTEXT KEY `NickName_2` (`NickName`,`FullName`,`FirstName`,`LastName`,`City`,`DescriptionMe`,`Tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Profiles`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_profile_views_track`
--

CREATE TABLE IF NOT EXISTS `sys_profile_views_track` (
  `id` int(10) unsigned NOT NULL,
  `viewer` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  KEY `id` (`id`,`viewer`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_profile_views_track`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_profiles_match`
--

CREATE TABLE `sys_profiles_match` (
  `profile_id` int(10) NOT NULL,
  `sort` enum('none','activity','date_reg') NOT NULL default 'none',
  `profiles_match` text,
  UNIQUE KEY `profile_id` (`profile_id`,`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table structure for table `sys_profiles_match_mails`
--

CREATE TABLE `sys_profiles_match_mails` (
  `profile_id` int(10) NOT NULL,
  `profiles_match` text NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `sys_profile_rating`
--

CREATE TABLE `sys_profile_rating` (
  `pr_id` int(10) unsigned NOT NULL default '0',
  `pr_rating_count` int(11) NOT NULL default '0',
  `pr_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `med_id` (`pr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_profile_rating`
--


-- --------------------------------------------------------

--
-- Table structure for table `sys_profile_voting_track`
--

CREATE TABLE `sys_profile_voting_track` (
  `pr_id` int(10) unsigned NOT NULL default '0',
  `pr_ip` varchar(20) default NULL,
  `pr_date` datetime default NULL,
  KEY `pr_ip` (`pr_ip`,`pr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_profile_voting_track`
--

-- RAY AS IT WAS INTEGRATED

-- --------------------------------------------------------

--
-- Table structure for table `RayBoardBoards`
--

CREATE TABLE IF NOT EXISTS `RayBoardCurrentUsers` (
  `ID` varchar(20) NOT NULL default '',
  `Nick` varchar(255) NOT NULL,
  `Sex` enum('M','F') NOT NULL default 'M',
  `Age` int(11) NOT NULL default '0',
  `Photo` varchar(255) NOT NULL default '',
  `Profile` varchar(255) NOT NULL default '',
  `Desc` varchar(255) NOT NULL,
  `When` int(11) NOT NULL default '0',
  `Status` enum('new','old','idle') NOT NULL default 'new',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `RayBoardBoards` (
  `ID` int(11) NOT NULL auto_increment,  
  `Name` varchar(255) NOT NULL default '',
  `Password` varchar(255) NOT NULL default '',
  `OwnerID` varchar(20) NOT NULL default '0', 
  `When` int(11) default NULL,
  `Status` enum('new', 'normal','delete') NOT NULL default 'new',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `RayBoardUsers` (
  `ID` int(11) NOT NULL auto_increment,  
  `Board` int(11) NOT NULL default '0',
  `User` varchar(20) NOT NULL default '',
  `When` int(11) default NULL,
  `Status` enum('normal','delete') NOT NULL default 'normal',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `RayChatCurrentUsers`
--

CREATE TABLE `RayChatCurrentUsers` (
  `ID` varchar(20) NOT NULL default '',
  `Nick` varchar(36) NOT NULL default '',
  `Sex` enum('M','F') NOT NULL default 'M',
  `Age` int(11) NOT NULL default '0',
  `Desc` text NOT NULL,
  `Photo` varchar(255) NOT NULL default '',
  `Profile` varchar(255) NOT NULL default '',
  `Online` varchar(10) NOT NULL default 'online',
  `Start` int(11) NOT NULL default '0',
  `When` int(11) NOT NULL default '0',
  `Status` enum('new','old','idle','kick','type','online') NOT NULL default 'new',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatCurrentUsers`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayChatMessages`
--

CREATE TABLE `RayChatMessages` (
  `ID` int(11) NOT NULL auto_increment,
  `Room` int(11) NOT NULL default 0,
  `SndRcp` varchar(40) NOT NULL default '',
  `Sender` varchar(20) NOT NULL default '',
  `Recipient` varchar(20) NOT NULL default '',
  `Whisper` enum('true','false') NOT NULL default 'false',
  `Message` text NOT NULL,
  `Style` text NOT NULL,
  `Type` varchar(10) NOT NULL default 'text',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatMessages`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayChatHistory`
--

CREATE TABLE `RayChatHistory` (
  `ID` int(11) NOT NULL auto_increment,
  `Room` int(11) NOT NULL default 0, 
  `SndRcp` varchar(40) NOT NULL default '', 
  `Sender` varchar(20) NOT NULL default '', 
  `Recipient` varchar(20) NOT NULL default '', 
  `Message` text NOT NULL default '',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayChatProfiles`
--

CREATE TABLE `RayChatProfiles` (
  `ID` varchar(20) NOT NULL default '0',
  `Banned` enum('true','false') NOT NULL default 'false',
  `Type` varchar(10) NOT NULL default 'full',
  `Smileset` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatProfiles`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayChatRooms`
--

CREATE TABLE `RayChatRooms` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL default '',
  `Password` varchar(255) NOT NULL default '',
  `Desc` text NOT NULL,
  `OwnerID` varchar(20) NOT NULL default '0',
  `When` int(11) default NULL,
  `Status` enum('normal','delete') NOT NULL default 'normal',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatRooms`
--

INSERT INTO `RayChatRooms` VALUES(1, 'Lobby', '', 'Welcome to our chat! You are in the "Lobby" now, but you can pass into any other public room you wish to - take a look at the "All rooms" box.', '0', 0, 'normal');
INSERT INTO `RayChatRooms` VALUES(2, 'Friends', '', 'Welcome to the "Friends" room! This is a public room where you can have a fun chat with existing friends or make new ones! Enjoy!', '0', 1, 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `RayChatRoomsUsers`
--

CREATE TABLE `RayChatRoomsUsers` (
  `ID` int(11) NOT NULL auto_increment,
  `Room` int(11) NOT NULL default 0,
  `User` varchar(20) NOT NULL default '',
  `When` int(11) default NULL,
  `Status` enum('normal','delete') NOT NULL default 'normal',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayChatRoomsUsers`
--

CREATE TABLE IF NOT EXISTS `RayChatMembershipsSettings` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(20) NOT NULL default '' UNIQUE,
  `Caption` varchar(255) NOT NULL default '',
  `Type` enum('boolean','number','custom') NOT NULL default 'boolean',
  `Default` varchar(255) NOT NULL default '',
  `Range` int(3) NOT NULL default '3',
  `Error` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
);
TRUNCATE TABLE `RayChatMembershipsSettings`;

INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('RoomCreate', 'New Rooms Creating:', 'boolean', 'true', '1', 'RayzRoomCreate');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('PrivateRoomCreate', 'Private Rooms Creating:', 'boolean', 'true', '1', 'RayzPrivateRoomCreate');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('AVCasting', 'Audio/Video Casting:', 'boolean', 'true', '1', 'RayzAVCasting');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('AVPlaying', 'Audio/Video Playing (for Messenger):', 'boolean', 'true', '1', 'RayzAVPlaying');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('AVLargeWindow', 'Enable Large Video Window:', 'boolean', 'true', '1', 'RayzAVLargeWindow');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('FileSend', 'Files Sending:', 'boolean', 'true', '1', 'RayzFileSend');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('WhisperMessages', 'Whispering Messages:', 'boolean', 'true', '1', 'RayzWhisperMessages');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('DirectMessages', 'Addressed Messages:', 'boolean', 'true', '1', 'RayzDirectMessages');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('RoomsNumber', 'Maximum Rooms Number:', 'number', '100', '3', 'RayzRoomsNumber');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('ChatsNumber', 'Maximum Private Chats Number:', 'number', '100', '3', 'RayzChatsNumber');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('AVWindowsNumber', 'Maximum Video Windows Number:', 'number', '100', '3', 'RayzAVWindowsNumber');
INSERT INTO `RayChatMembershipsSettings`(`Name`, `Caption`, `Type`, `Default`, `Range`, `Error`) VALUES('RestrictedRooms', 'Restricted Rooms:', 'custom', '', '1', 'RayzRestrictedRooms');

CREATE TABLE IF NOT EXISTS `RayChatMemberships` (
  `ID` int(11) NOT NULL auto_increment,
  `Setting` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  `Membership` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);
TRUNCATE TABLE `RayChatMemberships`;


-- --------------------------------------------------------

--
-- Table structure for table `RayImContacts`
--

CREATE TABLE `RayImContacts` (
  `ID` int(11) NOT NULL auto_increment,
  `SenderID` int(11) NOT NULL default '0',
  `RecipientID` int(11) NOT NULL default '0',
  `Online` varchar(10) NOT NULL default 'online',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayImContacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayImMessages`
--

CREATE TABLE `RayImMessages` (
  `ID` int(11) NOT NULL auto_increment,
  `ContactID` int(11) NOT NULL default '0',
  `Message` text NOT NULL,
  `Style` text NOT NULL,
  `Type` varchar(10) NOT NULL default 'text',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayImMessages`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayImPendings`
--

CREATE TABLE `RayImPendings` (
  `ID` int(11) NOT NULL auto_increment,
  `SenderID` int(11) NOT NULL default '0',
  `RecipientID` int(11) NOT NULL default '0',
  `Message` varchar(255) NOT NULL default '',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `RecipientID` (`RecipientID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayImPendings`
--


-- --------------------------------------------------------

--
-- Table structure for table `RayImProfiles`
--

CREATE TABLE `RayImProfiles` (
  `ID` int(11) NOT NULL default '0',
  `Smileset` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayImProfiles`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayMp3Files`
--

CREATE TABLE `RayMp3Files` (
  `ID` int(11) NOT NULL auto_increment,
  `Categories` text NOT NULL,
  `Title` varchar(255) NOT NULL default '',
  `Uri` varchar(255) NOT NULL default '',
  `Tags` text NOT NULL,
  `Description` text NOT NULL,
  `Time` int(11) NOT NULL default '0',
  `Date` int(20) NOT NULL default '0',
  `Reports` int(11) NOT NULL default '0',
  `Owner` varchar(64) NOT NULL default '',
  `Listens` int(12) default '0',
  `Rate` float NOT NULL,
  `RateCount` int(11) NOT NULL,
  `CommentsCount` int(11) NOT NULL,
  `Featured` tinyint(4) NOT NULL,
  `Status` enum('approved','disapproved','pending','processing','failed') NOT NULL default 'pending',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Uri` (`Uri`), 
  KEY (`Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayMp3Files`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayMp3Tokens`
--

CREATE TABLE `RayMp3Tokens` (
  `ID` int(11) NOT NULL default '0',
  `Token` varchar(32) NOT NULL default '',
  `Date` int(20) NOT NULL default '0',
  PRIMARY KEY `TokenId` (`ID`,`Token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayMp3Tokens`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayShoutboxMessages`
--

CREATE TABLE `RayShoutboxMessages` (
  `ID` int(11) NOT NULL auto_increment,
  `UserID` varchar(20) NOT NULL default '0',
  `Msg` text NOT NULL,
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayShoutboxMessages`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayVideoFiles`
--

CREATE TABLE `RayVideoFiles` (
  `ID` int(11) NOT NULL auto_increment,
  `Categories` text NOT NULL,
  `Title` varchar(255) NOT NULL default '',
  `Uri` varchar(255) NOT NULL default '',
  `Tags` text NOT NULL,
  `Description` text NOT NULL,
  `Time` int(11) NOT NULL default '0',
  `Date` int(20) NOT NULL default '0',
  `Owner` varchar(64) NOT NULL default '',
  `Views` int(12) default '0',
  `Rate` float NOT NULL,
  `RateCount` int(11) NOT NULL,
  `CommentsCount` int(11) NOT NULL,
  `Featured` tinyint(4) NOT NULL,
  `Status` enum('approved','disapproved','pending','processing','failed') NOT NULL default 'pending',
  `Source` varchar(20) NOT NULL default '',
  `Video` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Uri` (`Uri`),
  KEY (`Owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayVideoFiles`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayVideoFiles`
--

CREATE TABLE `RayVideoTokens` (
  `ID` int(11) NOT NULL default '0',
  `Token` varchar(32) NOT NULL default '',
  `Date` int(20) NOT NULL default '0',
  PRIMARY KEY `TokenId` (`ID`,`Token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `RayVideoFiles`
--

-- --------------------------------------------------------

--
-- Table structure for table `RayVideo_commentsFiles`
--

CREATE TABLE `RayVideo_commentsFiles` (
  `ID` int(11) NOT NULL auto_increment,
  `Categories` text NOT NULL,
  `Title` varchar(255) NOT NULL default '',
  `Uri` varchar(255) NOT NULL default '',
  `Tags` text NOT NULL,
  `Description` text NOT NULL,
  `Time` int(11) NOT NULL default '0',
  `Date` int(20) NOT NULL default '0',
  `Owner` varchar(64) NOT NULL default '',
  `Views` int(12) default '0',
  `Status` enum('approved','disapproved','pending','processing','failed') NOT NULL default 'pending',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Uri` (`Uri`),
  KEY (`Owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `RayVideo_commentsTokens`
--

CREATE TABLE `RayVideo_commentsTokens` (
  `ID` int(11) NOT NULL default '0',
  `Token` varchar(32) NOT NULL default '',
  `Date` int(20) NOT NULL default '0',
  PRIMARY KEY `TokenId` (`ID`,`Token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- RAY AS IT WAS INTEGRATED [END]
-- --------------------------------------------------------

--
-- Table structure for table `sys_stat_site`
--

CREATE TABLE `sys_stat_site` (
  `ID` tinyint(4) unsigned NOT NULL auto_increment,
  `Name` varchar(20) NOT NULL default '',
  `Title` varchar(50) NOT NULL default '',
  `UserLink` varchar(255) NOT NULL default '',
  `UserQuery` varchar(255) NOT NULL default '',
  `AdminLink` varchar(255) NOT NULL default '',
  `AdminQuery` varchar(255) NOT NULL default '',
  `IconName` varchar(50) NOT NULL default '',
  `StatOrder` int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_stat_site`
--
INSERT INTO `sys_stat_site`(`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('all', 'Members', 'browse.php', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Active'' AND (`Couple`=''0'' OR `Couple`>`ID`)', '{admin_url}profiles.php?action=browse&by=status&value=approval', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Approval'' AND (`Couple`=''0'' OR `Couple`>`ID`)', 'user', 1);

-- --------------------------------------------------------

--
-- Table structure for table 'sys_objects_search'
--

CREATE TABLE `sys_objects_search` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ObjectName` varchar(50) NOT NULL  default '',
  `Title` varchar(50) NOT NULL default '',
  `ClassName` varchar(50) NOT NULL  default '',
  `ClassPath` varchar(100) NOT NULL  default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table 'sys_objects_search'
--

INSERT INTO `sys_objects_search` VALUES(3, 'profile', '_Profiles', 'BxTemplSearchProfile', 'templates/tmpl_{tmpl}/scripts/BxTemplSearchProfile.php');

-- --------------------------------------------------------

--
-- Table structure for table 'sys_shared_sites'
--

CREATE TABLE `sys_shared_sites` (
  `ID` tinyint(4) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `URL` varchar(255) NOT NULL default '',
  `Icon` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_shared_sites` (`Name`, `URL`, `Icon`) VALUES
('digg', 'http://digg.com/submit?phase=2&url=', 'digg.png'),
('delicious', 'http://del.icio.us/post?url=', 'delicious.png'),
('newsvine', 'http://www.newsvine.com/_tools/seed&save?u=', 'newsvine.png'),
('reddit', 'http://reddit.com/submit?url=', 'reddit.png'),
('facebook', 'http://www.facebook.com/sharer/sharer.php?u=', 'facebook.png'),
('twitter', 'https://twitter.com/share?url=', 'twitter.png');

--
-- Dumping data for table 'sys_shared_sites'
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_tags`
--

CREATE TABLE `sys_tags` (
  `Tag` varchar(32) NOT NULL default '',
  `ObjID` int(10) unsigned NOT NULL default '0',
  `Type` varchar(20) NOT NULL default 'profile',
  `Date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`Tag`,`ObjID`,`Type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_tags`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_objects_tag`
--

CREATE TABLE `sys_objects_tag` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ObjectName` varchar(50) NOT NULL,
  `Query` text NOT NULL,
  `PermalinkParam` varchar(50) NOT NULL default '',
  `EnabledPermalink` varchar(100) NOT NULL default '',
  `DisabledPermalink` varchar(100) NOT NULL default '',
  `LangKey` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_objects_tag`
--

INSERT INTO `sys_objects_tag` VALUES(1, 'profile', 'SELECT `Tags` FROM `Profiles` WHERE `ID` = {iID} AND `Status` = ''Active''', 'enable_modrewrite', 'search/tag/{tag}', 'search.php?Tags={tag}', '_Profiles');

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_top`
--

CREATE TABLE `sys_menu_top` (
  `ID` smallint(6) unsigned NOT NULL auto_increment,
  `Parent` smallint(6) unsigned NOT NULL default '0',
  `Name` varchar(50) NOT NULL default '',
  `Caption` varchar(50) NOT NULL default '',
  `Link` varchar(255) NOT NULL default '',
  `Order` smallint(6) unsigned NOT NULL default '0',
  `Visible` set('non','memb') NOT NULL default '',
  `Target` varchar(20) NOT NULL default '',
  `Onclick` mediumtext NOT NULL,
  `Check` varchar(255) NOT NULL default '',
  `Movable` tinyint(4) NOT NULL default '3',
  `Clonable` tinyint(1) NOT NULL default '1',
  `Editable` tinyint(1) NOT NULL default '1',
  `Deletable` tinyint(1) NOT NULL default '1',
  `Active` tinyint(1) NOT NULL default '1',
  `Type` enum('system','top','custom') NOT NULL default 'top',
  `Picture` varchar(128) NOT NULL,
  `Icon` varchar(128) NOT NULL,
  `BQuickLink` tinyint(1) NOT NULL default '0',
  `Statistics` varchar(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Dumping data for table `sys_menu_top`
--

INSERT INTO `sys_menu_top` (`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES 
(4, 0, 'My Profile', '{memberNick}', '{memberLink}|{memberUsername}|change_status.php', 3, 'memb', '', '', '', 1, 0, 0, 0, 1, 'system', 'user', '', 0, ''),
(5, 0, 'Home', '_Home', 'index.php', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'top', 'home', '', 0, ''),
(6, 0, 'People', '_People', 'browse.php|search.php|calendar.php|tags.php?tags_mode=profile|search.php?show=match', 5, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'top', 'user', '', 0, ''),
(7, 6, 'All members', '_All Members', 'browse.php|browse', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(8, 6, 'Search Members', '_Search', 'search.php', 9, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(9, 0, 'Profile View', '{profileNick}', '{profileUsername}|pedit.php?ID={profileID}', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'system', '', '', 0, ''),
(11, 4, 'View My Profile', '_Profile', '{memberLink}|{memberUsername}|profile.php?ID={memberID}', 0, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(12, 179, 'Mail Compose', '_Compose', 'mail.php?mode=compose', 0, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(17, 179, 'Mail Inbox', '_Inbox', 'mail.php?mode=inbox', 1, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 1, 'mma'),
(14, 179, 'Mail Outbox', '_Outbox', 'mail.php?mode=outbox', 2, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(18, 179, 'Mail Trash', '_Trash', 'mail.php?mode=trash', 3, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(20, 0, 'Edit My Profile', '_Customize', 'pedit.php?ID={memberID}', 0, 'memb', '', '', '', 3, 1, 1, 0, 0, 'custom', 'user', '', 1, ''),
(25, 6, 'Online Members', '_Online', 'search.php?online_only=1', 2, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(60, 9, 'View Profile', '_Profile', '{profileLink}|{profileUsername}|profile.php?ID={profileID}', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(80, 4, 'My Friends', '_Friends', 'viewFriends.php?iUser={memberID}', 2, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, 'mfa'),
(82, 9, 'Info', '_profile_info', 'profile_info.php?ID={profileID}', 1, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(83, 9, 'Member Friends', '_Friends', 'viewFriends.php?iUser={profileID}', 2, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(98, 0, 'Join', '_Account', 'join.php', 1, 'non', '', '', '', 3, 1, 1, 0, 1, 'system', 'user', '', 0, ''),
(99, 0, 'Login', '_Login', 'member.php', 0, 'non', '', '', '', 3, 1, 0, 0, 0, 'custom', 'user', '', 0, ''),
(100, 0, 'Main', '_Main', 'index.php|', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 0, 'custom', '', '', 0, ''),
(101, 118, 'Account home', '_Account Home', 'member.php', 0, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(129, 6, 'Top Rated', '_Top Rated', 'search.php?show=top_rated', 4, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(127, 6, 'Match', '_Match', 'search.php?show=match', 1, 'non,memb', '', '', 'return isLogged() && getParam(\'enable_match\') == \'on\';', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(128, 6, 'Featured', '_Featured', 'search.php?show=featured', 3, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(107, 118, 'Privacy Groups', '_ps_tmenu_privacy_settings', 'member_privacy.php', 5, 'memb', '', '', 'bx_import(\'BxDolPrivacy\'); return BxDolPrivacy::isPrivacyPage();', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(109, 118, 'Unregister', '_Unregister', 'unregister.php', 8, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(111, 4, 'Profile Info', '_Info', 'profile_info.php', 1, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(118, 0, 'Dashboard', '_Dashboard', 'member.php', 2, 'memb', '', '', '', 1, 0, 1, 0, 1, 'system', 'tachometer', '', 0, ''),
(120, 0, 'About', '_About', 'about_us.php', 13, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'top', 'info-circle', '', 0, ''),
(122, 120, 'Terms of Use', '_TERMS_OF_USE_H', 'terms_of_use.php', 2, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(123, 120, 'Privacy Policy', '_PRIVACY_H', 'privacy.php', 3, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(126, 118, 'Activity', '_Activity', 'communicator.php', 3, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(130, 6, 'Popular', '_Popular', 'search.php?show=popular', 5, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(131, 6, 'Birthdays', '_Birthdays', 'search.php?show=birthdays', 6, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(132, 6, 'People Calendar', '_People_Calendar', 'calendar.php', 8, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(138, 0, 'Search', '_Search', 'search_home.php', 9, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'top', 'search', '', 0, ''),
(139, 138, 'Keyword Search', '_Keyword_Search', 'searchKeyword.php', 1, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(141, 138, 'People Search', '_People_Search', 'search.php', 3, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(159, 0, 'Help', '_help', 'help.php', 12, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'top', 'question-circle', '', 0, ''),
(160, 159, 'FAQ', '_FAQ', 'faq.php', 1, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(161, 159, 'Contact', '_Contact', 'contact.php', 2, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(162, 159, 'Advice', '_Advice', 'advice.php', 3, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(163, 159, 'Help', '_help', 'help.php', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(175, 120, 'About', '_About', 'about_us.php', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(176, 138, 'Search Home', '_Search_Home', 'search_home.php', 0, 'non,memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(179, 0, 'Mail', '_Mail', 'mail.php?mode=inbox', 4, 'memb', '', '', '', 3, 1, 1, 0, 1, 'system', 'envelope', '', 0, ''),
(191, 118, 'Subscriptions', '_sbs_tmenu_my_subscriptions', 'member_subscriptions.php', 7, 'memb', '', '', '', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(192, 118, 'Cart', '_sys_pmt_tmenu_cart', 'cart.php|modules/?r={sys_payment_module_uri}/cart/|modules/?r={sys_payment_module_uri}/history/', 9, 'memb', '', '', 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();', 3, 1, 1, 0, 1, 'custom', '', '', 0, ''),
(193, 118, 'Payments', '_sys_pmt_tmenu_payments', 'orders.php|modules/?r={sys_payment_module_uri}/orders/|modules/?r={sys_payment_module_uri}/details/', 10, 'memb', '', '', 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();', 3, 1, 1, 0, 1, 'custom', '', '', 0, '');


-- --------------------------------------------------------


CREATE TABLE `sys_objects_actions` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Caption` varchar(100) NOT NULL,
  `Icon` varchar(100) NOT NULL,
  `Url` varchar(250) NOT NULL,
  `Script` varchar(250) NOT NULL,
  `Eval` text NOT NULL,
  `Order` int(5) NOT NULL,
  `Type` varchar(20) NOT NULL,
  `bDisplayInSubMenuHeader` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{evalResult}', 'edit', 'pedit.php?ID={ID}', '', 'if ({ID} == {member_id} || isAdmin({member_id}) || isModerator({member_id})) return _t(''{cpt_edit}'');', 1, 'Profile', 0),
('{evalResult}', 'envelope', 'mail.php?mode=compose&recipient_id={ID}', '', 'if ({ID} == {member_id}) return;\r\nreturn _t(''{cpt_send_letter}'');', 2, 'Profile', 0),
('{cpt_fave}', 'asterisk', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFaveAdd({ID}, {member_id});', 3, 'Profile', 0),
('{cpt_remove_fave}', 'asterisk', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFaveCancel({ID}, {member_id});', 3, 'Profile', 0),
('{cpt_befriend}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAdd({ID}, {member_id});', 4, 'Profile', 0),
('{cpt_remove_friend}', 'minus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendCancel({ID}, {member_id}, false);', 4, 'Profile', 0),
('{cpt_greet}', 'hand-o-right', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn "$.post(''greet.php'', { sendto: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 5, 'Profile', 0),
('{cpt_get_mail}', 'envelope-o', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\n$bAnonymousMode  = ''{anonym_mode}'';\r\n\r\nif ( !$bAnonymousMode ) {\r\n    return "$.post(''freemail.php'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n}\r\n', 6, 'Profile', 0),
('{cpt_share}', 'share-square-o', '', 'return launchTellFriendProfile({ID});', '', 7, 'Profile', 0),
('{cpt_report}', 'exclamation-circle', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=spam'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 8, 'Profile', 0),
('{cpt_block}', 'ban', '', '{evalResult}', 'if ( {ID} == {member_id} || isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=block'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0),
('{sbs_profile_title}', 'paperclip', '', '{sbs_profile_script}', '', 10, 'Profile', 0),
('{cpt_unblock}', 'ban', '', '{evalResult}', 'if ({ID} == {member_id} || !isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn "$.post(''list_pop.php?action=unblock'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0),

('{cpt_activate}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_activate}&ID={ID}'', false, ''post''); return false;', '', 11, 'Profile', 0),
('{cpt_ban}', 'exclamation-circle', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_ban}&ID={ID}'', false, ''post''); return false;', '', 12, 'Profile', 0),
('{cpt_delete}', 'times', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete&ID={ID}'', false, ''post'', true); return false;', '', 13, 'Profile', 0),
('{cpt_delete_spam}', 'times', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete_spam&ID={ID}'', false, ''post'', true); return false;', '', 14, 'Profile', 0),
('{cpt_feature}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_feature}&ID={ID}'', false, ''post''); return false;', '', 15, 'Profile', 0),

('{evalResult}', 'plus', '{BaseUri}mail.php?mode=compose', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_sys_am_mailbox_compose'') : '''';', 1, 'Mailbox', 1),

('{cpt_am_friend_add}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAdd({ID}, {member_id}, false);', 1, 'ProfileTitle', 1),
('{cpt_am_friend_accept}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAccept({ID}, {member_id}, false);', 2, 'ProfileTitle', 1),
('{cpt_am_friend_cancel}', 'minus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendCancel({ID}, {member_id}, false);', 3, 'ProfileTitle', 1),
('{cpt_am_profile_message}', 'envelope', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlProfileMessage({ID});', 4, 'ProfileTitle', 1),
('{cpt_am_profile_account_page}', 'tachometer', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlAccountPage({ID});', 5, 'ProfileTitle', 1),

('{cpt_am_account_profile_page}', 'user', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlProfilePage({ID});', 1, 'AccountTitle', 1);


-- --------------------------------------------------------


--
-- Table structure for table `sys_greetings`
--

CREATE TABLE `sys_greetings` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `Number` smallint(5) unsigned NOT NULL default '0',
  `When` date NOT NULL default '0000-00-00',
  `New` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`ID`,`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_greetings`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_objects_vote`
--

CREATE TABLE `sys_objects_vote` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ObjectName` varchar(50) NOT NULL,
  `TableRating` varchar(50) NOT NULL,
  `TableTrack` varchar(50) NOT NULL,
  `RowPrefix` varchar(20) NOT NULL,
  `MaxVotes` smallint(2) NOT NULL,
  `PostName` varchar(50) NOT NULL,
  `IsDuplicate` varchar(80) NOT NULL,
  `IsOn` smallint(1) NOT NULL,
  `className` varchar(50) NOT NULL default '',
  `classFile` varchar(100) NOT NULL default '',
  `TriggerTable` varchar(32) NOT NULL,
  `TriggerFieldRate` varchar(32) NOT NULL,
  `TriggerFieldRateCount` varchar(32) NOT NULL,
  `TriggerFieldId` varchar(32) NOT NULL,
  `OverrideClassName` varchar(32) NOT NULL,
  `OverrideClassFile` varchar(256) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_objects_vote`
--

INSERT INTO `sys_objects_vote` VALUES(1, 'profile', 'sys_profile_rating', 'sys_profile_voting_track', 'pr_', '5', 'vote_send_result', 'BX_PERIOD_PER_VOTE', '1', '', '', 'Profiles', 'Rate', 'RateCount', 'ID', '', '');


-- -------------------------------------------------------

ALTER TABLE `RayMp3Files` ADD FULLTEXT KEY `ftMain` (`Title`, `Tags`, `Description`, `Categories`);
ALTER TABLE `RayMp3Files` ADD FULLTEXT KEY `ftTags` (`Tags`);
ALTER TABLE `RayMp3Files` ADD FULLTEXT KEY `ftCategories` (`Categories`);

ALTER TABLE `RayVideoFiles` ADD FULLTEXT KEY `ftMain` (`Title`, `Tags`, `Description`, `Categories`);
ALTER TABLE `RayVideoFiles` ADD FULLTEXT KEY `ftTags` (`Tags`);
ALTER TABLE `RayVideoFiles` ADD FULLTEXT KEY `ftCategories` (`Categories`);

--
-- Table structure for table `sys_modules`
--

CREATE TABLE `sys_modules` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `vendor` varchar(64) NOT NULL default '',
  `version` varchar(32) NOT NULL default '',
  `update_url` varchar(128) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',  
  `uri` varchar(32) NOT NULL default '',
  `class_prefix` varchar(32) NOT NULL default '',
  `db_prefix` varchar(32) NOT NULL default '',
  `dependencies` varchar(255) NOT NULL default '',
  `date` int(11) unsigned NOT NULL default '0',  
  PRIMARY KEY  (`id`),
  UNIQUE KEY `path` (`path`),
  UNIQUE KEY `uri` (`uri`),
  UNIQUE KEY `class_prefix` (`class_prefix`),
  UNIQUE KEY `db_prefix` (`db_prefix`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_modules_file_tracks`
--
CREATE TABLE `sys_modules_file_tracks` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `module_id` int(11) unsigned NOT NULL default '0',
  `file` varchar(255) NOT NULL default '',
  `hash` varchar(64) NOT NULL default '',  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_injections`
--

CREATE TABLE `sys_injections` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `page_index` int(11) NOT NULL default '0',
  `key` varchar(128) NOT NULL default '',
  `type` enum('text', 'php') NOT NULL default 'text',
  `data` text NOT NULL default '',
  `replace` TINYINT NOT NULL DEFAULT '0',
  `active` TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_injections`
--
INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('flash_integration', '0', 'injection_header', 'php', 'return getRayIntegrationJS(true);', '0', '1'),
('banner_bottom', 0, 'banner_bottom', 'php', 'return banner_put_nv(4);', 0, 1),
('banner_right', 0, 'banner_right', 'php', 'return banner_put_nv(3);', 0, 1),
('banner_top', 0, 'banner_top', 'php', 'return banner_put_nv(1);', 0, 1),
('banner_left', 0, 'banner_left', 'php', 'return banner_put_nv(2);', 0, 1),
('sys_confirm_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxConfirm.html'', array());', '0', '1'),
('sys_prompt_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxPrompt.html'', array());', '0', '1'),
('sys_head', 0, 'injection_head', 'text', '', 0, 1),
('sys_body', 0, 'injection_footer', 'text', '', 0, 1);


--
-- Table structure for table `sys_injections_admin`
--

CREATE TABLE `sys_injections_admin` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `page_index` int(11) NOT NULL default '0',
  `key` varchar(128) NOT NULL default '',
  `type` enum('text','php') NOT NULL default 'text',
  `data` text NOT NULL,
  `replace` tinyint(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_injections`
--
INSERT INTO `sys_injections_admin` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('flash_integration', '0', 'injection_header', 'php', 'return getRayIntegrationJS();', '0', '1'),
('lfa', '0', 'injection_header', 'php', 'return lfa();', '0', '1'),
('sys_confirm_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxConfirm.html'', array());', '0', '1'),
('sys_prompt_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxPrompt.html'', array());', '0', '1');

--
-- Table structure for table `sys_permalinks`
--

CREATE TABLE `sys_permalinks` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `standard` varchar(128) NOT NULL default '',
  `permalink` varchar(128) NOT NULL default '',
  `check` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `check` (`standard`, `permalink`, `check`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_alerts_handlers`
--
CREATE TABLE `sys_alerts_handlers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `class` varchar(128) NOT NULL default '',
  `file` varchar(255) NOT NULL default '',
  `eval` text NOT NULL default '', 
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_alerts_handlers`
--
INSERT INTO `sys_alerts_handlers` (`id`, `name`, `class`, `file`) VALUES
(1, 'system', 'BxDolAlertsResponseSystem', 'inc/classes/BxDolAlertsResponseSystem.php'),
(2, 'profile', 'BxDolAlertsResponseProfile', 'inc/classes/BxDolAlertsResponseProfile.php'),
(3, 'membersData', 'BxDolUpdateMembersCache', 'inc/classes/BxDolUpdateMembersCache.php'),
(4, 'profileMatch', 'BxDolAlertsResponceMatch', 'inc/classes/BxDolAlertsResponceMatch.php');

--
-- Table structure for table `sys_alerts`
--
CREATE TABLE `sys_alerts` (
  `id` int(11) unsigned NOT NULL auto_increment,  
  `unit` varchar(64) NOT NULL default '',
  `action` varchar(64) NOT NULL default 'none',
  `handler_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_handler` (`unit`, `action`, `handler_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_alerts`
--
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('system', 'begin', 1),
('profile', 'before_join', 2),
('profile', 'join', 2),
('profile', 'before_login', 2),
('profile', 'login', 2),
('profile', 'logout', 2),
('profile', 'edit', 2),
('profile', 'delete', 2),
('profile', 'join', 3),
('profile', 'edit', 3),
('profile', 'delete', 3),
('profile', 'join', 4),
('profile', 'edit', 4),
('profile', 'delete', 4),
('profile', 'change_status', 4);


-- 
-- Deleting video comments handler
-- 
INSERT INTO `sys_alerts_handlers`(`name`, `class`, `file`) VALUES('bx_videos_comments_delete', 'BxDolVideoDeleteResponse', 'flash/modules/video_comments/inc/classes/BxDolVideoDeleteResponse.php');
SET @iHandlerId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_alerts`(`unit`, `action`, `handler_id`) VALUES('profile', 'commentRemoved', @iHandlerId);

-- --------------------------------------------------------

--
-- Table structure for table `sys_objects_views`
--

CREATE TABLE `sys_objects_views` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `table_track` varchar(32) NOT NULL,
  `period` int(11) NOT NULL default '86400',
  `trigger_table` varchar(32) NOT NULL,
  `trigger_field_id` varchar(32) NOT NULL,
  `trigger_field_views` varchar(32) NOT NULL,
  `is_on` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_objects_views`
--

INSERT INTO `sys_objects_views` VALUES(NULL, 'profiles', 'sys_profile_views_track', 86400, 'Profiles', 'ID', 'Views', 1);

--
-- Table structure for table `sys_privacy_groups`
--
CREATE TABLE `sys_privacy_groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner_id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `home_url` varchar(255) NOT NULL default '',
  `get_parent` text NOT NULL default '',
  `get_content` text NOT NULL default '',
  `members_count` int(11) NOT NULL default '0', 
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_privacy_groups`
--
INSERT INTO `sys_privacy_groups`(`id`, `owner_id`, `parent_id`, `title`, `home_url`, `get_parent`, `get_content`, `members_count`) VALUES
('1', '0', '0', '', '', '$aProfile = getProfileInfo($arg1); return (int)$aProfile[\'PrivacyDefaultGroup\'];', '', 0),
('2', '0', '0', '', '', '', 'return false;', 0),
('3', '0', '0', '', '', '', 'return true;', 0),
('4', '0', '0', '', '', '', 'return isMember() && isProfileActive($arg2);', 0),
('5', '0', '0', '', 'communicator.php?communicator_mode=friends_list', '', '$aIds = $arg0->fromMemory($arg0->_sGroupFriendsCache . $arg1, "getColumn", "SELECT `p`.`ID` AS `id` FROM `Profiles` AS `p` LEFT JOIN `sys_friend_list` AS `f1` ON (`f1`.`ID`=`p`.`ID` AND `f1`.`Profile`=\'" . $arg1 . "\' AND `f1`.`Check`=1) LEFT JOIN `sys_friend_list` AS `f2` ON (`f2`.`Profile`=p.`ID` AND `f2`.`ID`=\'" . $arg1 . "\' AND `f2`.`Check`=1) WHERE 1 AND (`f1`.`ID` IS NOT NULL OR `f2`.`ID` IS NOT NULL)"); return isProfileActive($arg2) && in_array($arg2, $aIds);', 0),
('6', '0', '0', '', 'communicator.php?&communicator_mode=hotlist_requests', '', '$aIds = $arg0->fromMemory($arg0->_sGroupFavesCache . $arg1, "getColumn", "SELECT `Profile` AS `id` FROM `sys_fave_list` WHERE `ID`=\'" . $arg1 . "\'"); return isProfileActive($arg2) && in_array($arg2, $aIds);', 0),
('7', '0', '0', '', 'mail.php?&mode=inbox&contacts_mode=Contacted', '', '$aIds = $arg0->fromMemory($arg0->_sGroupContactsCache . $arg1, "getColumn", "SELECT `tp`.`ID` AS `id` FROM `sys_messages` AS `tm` INNER JOIN `Profiles` AS `tp` ON (`tm`.`Sender`=`tp`.`ID` AND `tm`.`Recipient`=\'" . $arg1 . "\') OR (`tm`.`Recipient`=`tp`.`ID` AND `tm`.`Sender`=\'" . $arg1 . "\')"); return isProfileActive($arg2) && in_array($arg2, $aIds);', 0),
('8', '0', '0', '', '', '', '', 0);

--
-- Table structure for table `sys_privacy_members`
--
CREATE TABLE `sys_privacy_members` (
  `id` int(11) NOT NULL auto_increment,  
  `group_id` int(11) NOT NULL default '0',
  `member_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `group_member` (`group_id`, `member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_privacy_actions`
--
CREATE TABLE `sys_privacy_actions` (
  `id` int(11) NOT NULL auto_increment,  
  `module_uri` varchar(64) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `default_group` varchar(255) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `action` (`module_uri`, `name` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_privacy_actions`
--
INSERT INTO `sys_privacy_actions`(`module_uri`, `name`, `title`, `default_group`) VALUES
('profile', 'view_block', '_ps_view_block', '3');

--
-- Table structure for table `sys_privacy_defaults`
--
CREATE TABLE `sys_privacy_defaults` (  
  `owner_id` int(11) NOT NULL default '0',
  `action_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`owner_id`, `action_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_sbs_users`
--
CREATE TABLE `sys_sbs_users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `email` varchar(64) NOT NULL default '',
  `date` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriber` (`name`, `email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_sbs_entries`
--
CREATE TABLE `sys_sbs_entries` (
  `id` int(11) NOT NULL auto_increment,
  `subscriber_id` int(11) NOT NULL default '0',
  `subscriber_type` tinyint(4) NOT NULL default '0',
  `subscription_id` int(11) NOT NULL default '0',  
  `object_id` int(11) NOT NULL default '0',  
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subscriber_id`, `subscriber_type`, `subscription_id`, `object_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_sbs_types`
--
CREATE TABLE `sys_sbs_types` (  
  `id` int(11) NOT NULL auto_increment,
  `unit` varchar(32) NOT NULL default '',
  `action` varchar(32) NOT NULL default '',
  `template` varchar(64) NOT NULL default '',
  `params` text NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription` (`unit`, `action`, `template`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_sbs_types`
--
INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('system', '', '', 'return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_mass_mailer''), ''ViewLink'' => BX_DOL_URL_ROOT));'),
('system', 'mass_mailer', 't_AdminEmail', 'return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_mass_mailer''), ''ViewLink'' => BX_DOL_URL_ROOT));'),
('profile', '', '', '$aUser = getProfileInfo($arg3); return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_profile'', $aUser[''NickName'']), ''ViewLink'' => getProfileLink($arg3)));'),
('profile', 'commentPost', 't_sbsProfileComments', '$aUser = getProfileInfo($arg3); return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_profile_comments'', $aUser[''NickName'']), ''ViewLink'' => getProfileLink($arg3)));'),
('profile', 'edit', 't_sbsProfileEdit', '$aUser = getProfileInfo($arg3); return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_profile_edit'', $aUser[''NickName'']), ''ViewLink'' => getProfileLink($arg3)));');

--
-- Table structure for table `sys_sbs_queue`
--
CREATE TABLE `sys_sbs_queue` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(64) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_sbs_messages`
--
CREATE TABLE `sys_sbs_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `subject` varchar(128) NOT NULL default '',  
  `body` mediumtext NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_box_download`
--
CREATE TABLE IF NOT EXISTS `sys_box_download` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `onclick` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  `disabled` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_box_download` (`id`, `title`, `url`, `onclick`, `desc`, `icon`, `order`, `disabled`) VALUES
(1, '_sbd_iPhone_title', 'http://itunes.apple.com/us/app/oo/id345450186', '', '_sbd_iPhone_desc', 'apple', 2, 0),
(2, '_sbd_Android_title', 'https://play.google.com/store/apps/details?id=com.boonex.oo', '', '_sbd_Android_desc', 'android', 3, 0);


--
-- Table structure for table `sys_cron_jobs`
--

CREATE TABLE `sys_cron_jobs` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `time` varchar(128) NOT NULL default '*',
  `class` varchar(128) NOT NULL default '',
  `file` varchar(255) NOT NULL default '',
  `eval` text NOT NULL default '', 
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sys_cron_jobs`
--

INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
('cmd', '0 0 * * *', 'BxDolCronCmd', 'inc/classes/BxDolCronCmd.php', ''),
('notifies', '*/10 * * * *', 'BxDolCronNotifies', 'inc/classes/BxDolCronNotifies.php', ''),
('video_comments', '* * * * *', 'BxDolCronVideoComments', 'flash/modules/video_comments/inc/classes/BxDolCronVideoComments.php', ''),
('sitemap', '0 2 * * *', '', '', 'bx_import(''BxDolSiteMaps'');\r\nBxDolSiteMaps::generateAllSiteMaps();'),
('modules', '0 0 * * 0', 'BxDolCronModules', 'inc/classes/BxDolCronModules.php', '');


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_dnsbl_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chain` enum('spammers','whitelist','uridns') NOT NULL,
  `zonedomain` varchar(255) NOT NULL,
  `postvresp` varchar(32) NOT NULL,
  `url` varchar(255) NOT NULL,
  `recheck` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `added` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_dnsbl_rules` (`id`, `chain`, `zonedomain`, `postvresp`, `url`, `recheck`, `comment`, `added`, `active`) VALUES
(1, 'whitelist', 'au.countries.nerd.dk.', '127.0.0.2', 'http://countries.nerd.dk/', '', 'Country based zone, any ip from Australia is whitelisted', 1287642420, 0),
(2, 'spammers', 'sbl.spamhaus.org.', 'any', 'http://www.spamhaus.org/sbl/', 'http://www.spamhaus.org/query/bl?ip=%s', 'Any non-failure result from sbl.spamhaus.org is a positive match', 1287642420, 1),
(4, 'spammers', 'cn.countries.nerd.dk.', '127.0.0.2', 'http://countries.nerd.dk/', '', 'Country based zone, any ip from China is blocked', 1287642420, 0),
(5, 'uridns', 'multi.surbl.org.', 'any', 'http://www.surbl.org/', 'http://george.surbl.org/lookup.html', 'SURBLs are lists of web sites that have appeared in unsolicited messages. Unlike most lists, SURBLs are not lists of message senders.', 1287642420, 1),
(6, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.5', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Bottler', 1368854835, 1),
(7, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.6', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Unknown spambot or drone', 1368854835, 1),
(8, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.7', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'DDOS Drone', 1368854835, 1),
(9, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.8', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'SOCKS Proxy', 1368854835, 1),
(10, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.9', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'HTTP Proxy', 1368854835, 1),
(11, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.10', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'ProxyChain', 1368854835, 1),
(12, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.14', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Open Wingate Proxy', 1368854835, 1),
(13, 'spammers', 'dnsbl.dronebl.org.', '127.0.0.15', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Compromised router / gateway', 1368854835, 1),
(14, 'spammers', 'dnsbl.tornevall.org.', '230', 'http://dnsbl.tornevall.org/', '', 'Block anonymous/elite proxies and abuse IPs', 1369274751, 1),
(15, 'spammers', 'uribl.swinog.ch.', '127.0.0.3', 'http://antispam.imp.ch/06-dnsbl.php?lng=1', '', 'ImproWare Antispam', 1393336086, 0),
(16, 'uridns', 'uribl.swinog.ch.', 'any', 'http://antispam.imp.ch/05-uribl.php?lng=1', '', 'ImproWare Antispam', 1393336170, 0);


CREATE TABLE IF NOT EXISTS `sys_antispam_block_log` (
  `ip` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `extra` text NOT NULL,
  `added` int(11) NOT NULL,
  KEY `ip` (`ip`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sys_dnsbluri_zones` (
  `level` tinyint(4) NOT NULL,
  `zone` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sys_menu_mobile_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page` (`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_menu_mobile_pages` (`id`, `page`, `title`, `order`) VALUES
(1, 'homepage', '_adm_mobile_page_homepage', 1),
(2, 'profile', '_adm_mobile_page_profile', 2),
(3, 'search', '_adm_mobile_page_search', 3);

CREATE TABLE IF NOT EXISTS `sys_menu_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `page` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `action` int(11) NOT NULL,
  `action_data` varchar(255) NOT NULL,
  `eval_bubble` text NOT NULL,
  `eval_hidden` text NOT NULL,
  `order` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('system', 'homepage', '_sys_mobile_status', 'home_status.png', 1, '', '', '', 1, 1),
('system', 'homepage', '_sys_mobile_mail', 'home_messages.png', 3, '', 'return getNewLettersNum({member_id});', '', 2, 1),
('system', 'homepage', '_sys_mobile_friends', 'home_friends.png', 4, '', 'return getFriendRequests({member_id});', '', 3, 1),
('system', 'homepage', '_sys_mobile_info', 'home_info.png', 5, '', '', '', 4, 1),
('system', 'homepage', '_sys_mobile_search', 'home_search.png', 6, '', '', '', 5, 1),
('system', 'profile', '_sys_mobile_profile_info', '', 5, '', '', '', 1, 1),
('system', 'profile', '_sys_mobile_profile_contact', '', 3, '', '', '', 2, 1),
('system', 'profile', '_sys_mobile_profile_friends', '', 4, '', 'return getFriendNumber(''{profile_id}'');', '', 3, 1),
('system', 'search', '_sys_mobile_search_by_keyword', '', 30, '', '', '', 1, 1),
('system', 'search', '_sys_mobile_search_by_location', '', 31, '', '', '', 2, 1);


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_social_sharing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `order` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_social_sharing` (`object`, `content`, `order`, `active`) VALUES
('facebook', '<iframe src="//www.facebook.com/plugins/like.php?href={url_encoded}&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;locale={locale}" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100%; height:21px;" allowTransparency="true"></iframe>', 1, 1),
('googleplus', '<div style="height:21px;">\r\n<div class="g-plusone" data-size="medium" data-href="{url}"></div>\r\n<script type="text/javascript">\r\n  window.___gcfg = {lang: ''{lang}''};\r\n  (function() {\r\n    var po = document.createElement(''script''); po.type = ''text/javascript''; po.async = true;\r\n    po.src = ''https://apis.google.com/js/plusone.js'';\r\n    var s = document.getElementsByTagName(''script'')[0]; s.parentNode.insertBefore(po, s);\r\n  })();\r\n</script>\r\n</div>', 2, 1),
('twitter', '<iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/tweet_button.html?url={url_encoded}&amp;text={title_encoded}&amp;size=medium&amp;count=horizontal&amp;lang={lang}" style="width:100%;height:21px;"></iframe>', 3, 1),
('pinterest', '<a href="http://pinterest.com/pin/create/button/?url={url_encoded}&media={img_url_encoded}&description={title_encoded}" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>\r\n\r\n<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>', 4, 1);


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_site_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `priority` varchar(5) NOT NULL DEFAULT '0.6',
  `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never','auto') NOT NULL DEFAULT 'auto',
  `class_name` varchar(255) NOT NULL,
  `class_file` varchar(255) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('system', '_sys_sitemap_system', '0.6', 'weekly', 'BxDolSiteMapsSystem', '', 1, 1),
('profiles', '_sys_sitemap_profiles', '0.8', 'daily', 'BxDolSiteMapsProfiles', '', 2, 1),
('profiles_info', '_sys_sitemap_profiles_info', '0.8', 'daily', 'BxDolSiteMapsProfilesInfo', '', 3, 1),
('pages', '_sys_sitemap_pages', '0.8', 'weekly', 'BxDolSiteMapsPages', '', 4, 1);


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_charts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `field_date_ts` varchar(255) NOT NULL,
  `field_date_dt` varchar(255) NOT NULL,
  `column_date` int(11) NOT NULL DEFAULT '0',
  `column_count` int(11) NOT NULL DEFAULT '1',
  `type` varchar(255) NOT NULL,
  `options` text NOT NULL,
  `query` text NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('sys_profiles', '_Members', 'Profiles', '', 'DateReg', '', 1, 1),
('sys_subscribers', '_Subscribers', 'sys_sbs_users', 'date', '', '', 1, 2),
('sys_messages', '_Messages', 'sys_messages', '', 'Date', '', 1, 3),
('sys_greetings', '_Greetings', 'sys_greetings', '', 'When', '', 1, 4),
('sys_tags', '_Tags', 'sys_tags', '', 'Date', '', 1, 5),
('sys_categories', '_Categories', 'sys_categories', '', 'Date', '', 1, 6),
('sys_banners', '_adm_bann_clicks_chart', 'sys_banners_clicks', 'Date', '', '', 1, 7);


-- --------------------------------------------------------


CREATE TABLE `sys_objects_captcha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_captcha` (`object`, `title`, `override_class_name`, `override_class_file`) VALUES
('sys_recaptcha', 'reCAPTCHA', 'BxTemplCaptchaReCAPTCHA', '');


-- --------------------------------------------------------


CREATE TABLE `sys_objects_editor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `skin` varchar(255) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_editor` (`object`, `title`, `skin`, `override_class_name`, `override_class_file`) VALUES
('sys_tinymce', 'TinyMCE', 'lightgray', 'BxTemplEditorTinyMCE', '');


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_member_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('sys_username', '_sys_member_info_username', 'name', '', ''),
('sys_full_name', '_sys_member_info_full_name', 'name', '', ''),
('sys_status_message', '_sys_member_info_status_message', 'info', '', ''),
('sys_age_sex', '_sys_member_info_age_sex', 'info', '', ''),
('sys_location', '_sys_member_info_location', 'info', '', ''),
('sys_avatar', '_sys_member_thumb_avatar', 'thumb', '', ''),
('sys_avatar_2x', '_sys_member_thumb_avatar_2x', 'thumb_2x', '', ''),
('sys_avatar_icon', '_sys_member_thumb_icon_avatar', 'thumb_icon', '', ''),
('sys_avatar_icon_2x', '_sys_member_thumb_icon_avatar_2x', 'thumb_icon_2x', '', '');


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `uri` varchar(32) NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `sys_objects_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `class_file` varchar(255) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('profiles', '_Profiles', 'BxDolExportProfile', '', 1, 1),
('flash', '_adm_admtools_Flash', 'BxDolExportFlash', '', 2, 1);

