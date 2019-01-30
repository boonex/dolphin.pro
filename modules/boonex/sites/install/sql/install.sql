
-- create tables
CREATE TABLE IF NOT EXISTS `[db_prefix]main` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  `title` varchar(100) NOT NULL,
  `entryUri` varchar(255) NOT NULL,
  `description` text NOT NULL default '',
  `status` enum('approved','pending') NOT NULL default 'approved',
  `photo` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `ownerid` int(10) unsigned NOT NULL default '0',
  `allowView` int(11) NOT NULL,
  `allowComments` int(11) NOT NULL default '0',
  `allowRate` int(11) NOT NULL default '0',
  `tags` varchar(255) NOT NULL default '',
  `categories` text NOT NULL,
  `views` int(11) NOT NULL default 0,
  `rate` float NOT NULL default 0,
  `rateCount` int(11) NOT NULL default 0,
  `commentsCount` int(11) NOT NULL,
  `featured` tinyint(4) NOT NULL default 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entryUri` (`entryUri`),
  KEY `date` (`date`),
  FULLTEXT KEY `title` (`title`,`description`,`tags`,`categories`),
  FULLTEXT KEY `tags` (`tags`),
  FULLTEXT KEY `categories` (`categories`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]rating` (
  `sites_id` smallint( 6 ) NOT NULL default '0',
  `sites_rating_count` int( 11 ) NOT NULL default '0',
  `sites_rating_sum` int( 11 ) NOT NULL default '0',
  UNIQUE KEY `sites_id` (`sites_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[db_prefix]rating_track` (
  `sites_id` smallint( 6 ) NOT NULL default '0',
  `sites_ip` varchar( 20 ) default NULL,
  `sites_date` datetime default NULL,
  KEY `sites_ip` (`sites_ip`, `sites_id`)
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

-- top menu
SET @iMaxMenuOrder := (SELECT `Order` + 1 FROM `sys_menu_top` WHERE `Parent` = 0 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES 
(0, 'Sites', '_bx_sites', 'modules/?r=sites/view/', '', 'non,memb', '', '', '', 0, 0, 1, 'system', 'link', '', 0, '');

INSERT INTO `sys_menu_top`(`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(0, 'Sites', '_bx_sites', 'modules/?r=sites/home/|modules/?r=sites/', @iMaxMenuOrder, 'non,memb', '', '', '', 1, 1, 1, 'top', 'link', 'link', 1, '');
SET @iCatRoot := LAST_INSERT_ID();

INSERT INTO `sys_menu_top`(`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(@iCatRoot, 'SitesHome', '_bx_sites_home_top_menu_sitem', 'modules/?r=sites/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesAll', '_bx_sites_all_top_menu_sitem', 'modules/?r=sites/browse/all', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesAdmin', '_bx_sites_admin_top_menu_sitem', 'modules/?r=sites/browse/admin', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesUsers', '_bx_sites_profile_top_menu_sitem', 'modules/?r=sites/browse/users', 3, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesTop', '_bx_sites_top_top_menu_sitem', 'modules/?r=sites/browse/top', 4, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesPopular', '_bx_sites_popular_top_menu_sitem', 'modules/?r=sites/browse/popular', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesFeatured', '_bx_sites_featured_top_menu_sitem', 'modules/?r=sites/browse/featured', 8, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, 'bx_sites'),
(@iCatRoot, 'SitesTags', '_bx_sites_tags_top_menu_sitem', 'modules/?r=sites/tags', 9, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, 'bx_sites'),
(@iCatRoot, 'SitesCategories', '_bx_sites_categories_top_menu_sitem', 'modules/?r=sites/categories', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesHoN', '_bx_sites_hon_top_menu_sitem', 'modules/?r=sites/hon', 11, 'memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesCalendar', '_bx_sites_calendar_top_menu_sitem', 'modules/?r=sites/calendar', 12, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, ''),
(@iCatRoot, 'SitesSearch', '_bx_sites_search_top_menu_sitem', 'modules/?r=sites/search', 13, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(9, 'Sites', '_bx_sites_menu_my_sites_profile', 'modules/?r=sites/browse/user/{profileUsername}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top`(`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(4, 'Sites', '_bx_sites_menu_my_sites_profile', 'modules/?r=sites/browse/my', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');

-- member menu
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_sites', `Eval` = 'return BxDolService::call(''sites'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);

-- permalinks
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=sites/', 'm/sites/', 'bx_sites_permalinks');

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Sites', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_sites_permalinks', 'on', 26, 'Enable friendly permalinks in sites', 'checkbox', '', '', 0, ''),
('bx_sites_autoapproval', 'on', @iCategId, 'Activate all sites after creation automatically', 'checkbox', '', '', 1, ''),
('bx_sites_comments', 'on', @iCategId, 'Allow comments for sites', 'checkbox', '', '', 2, ''),
('bx_sites_votes', 'on', @iCategId, 'Allow votes for sites', 'checkbox', '', '', 3, ''),
('bx_sites_per_page', '10', @iCategId, 'The number of items shown on the page', 'digit', '', '', 4, ''),
('bx_sites_max_rss_num', '10', @iCategId, 'Max number of rss items to provide', 'digit', '', '', 5, ''),
('category_auto_app_bx_sites', 'on', @iCategId, 'Activate all categories for all sites after creation automatically', 'checkbox', '', '', 6, ''),
-- begin stw integration
('bx_sites_key_id', '', @iCategId, 'ShrinkTheWeb Access Key', 'digit', '', '', '7', ''),
('bx_sites_secret_key', '', @iCategId, 'ShrinkTheWeb Secret Key', 'digit', '', '', '8', ''),
('bx_sites_account_type', 'No Automated Screenshots', @iCategId, 'ShrinkTheWeb Account Type', 'select', 'return strlen($arg0) > 0;', 'cannot be empty.', '9', 'No Automated Screenshots,Enabled'),
('bx_sites_cache_days', '7', @iCategId, 'Cache days<br>(how many days the images are valid in your cache, Enter 0 (zero) to never update screenshots once cached or -1 to disable caching and always use embedded method instead)', 'digit', '', '', '10', ''),
('bx_sites_debug', 'off', @iCategId, 'Debug<br>(store debug info in database)', 'checkbox', '', '', '11', ''),
('bx_sites_inside_pages', 'off', @iCategId, 'Inside Page Captures<br>(i.e. not just homepages and sub-domains, select if you have purchased this pro package)', 'checkbox', '', '', '12', ''),
('bx_sites_custom_msg_url', '', @iCategId, 'Custom Messages URL<br>(specify the URL where your custom message images are stored)', 'digit', '', '', '13', ''),
('bx_sites_thumb_size', 'lg', @iCategId, 'Default Thumbnail size<br>(width: mcr 75px, tny 90px, vsm 100px, sm 120px, lg 200px, xlg 320px)', 'select', 'return strlen($arg0) > 0;', 'cannot be empty.', '14', 'mcr,tny,vsm,sm,lg,xlg'),
('bx_sites_thumb_size_custom', '', @iCategId, 'Custom Width<br>(enter your custom image width, this will override default size)', 'digit', '', '', '15', ''),
('bx_sites_full_size', '', @iCategId, 'Full-Length capture', 'checkbox', '', '', '16', ''),
('bx_sites_max_height', '', @iCategId, 'Max height<br>(use if you want to set maxheight for fullsize capture)', 'digit', '', '', '17', ''),
('bx_sites_native_res', '', @iCategId, 'Native resolution<br>(i.e. 640 for 640x480)', 'digit', '', '', '18', ''),
('bx_sites_widescreen_y', '', @iCategId, 'Widescreen resolution Y<br>(i.e. 900 for 1440x900 if 1440 is set for Native resolution)', 'digit', '', '', '19', ''),
('bx_sites_redo', 'off', @iCategId, 'Refresh On-Demand<br>(select if you have purchased this pro package and want to allow your members to use it)', 'checkbox', '', '', '20', ''),
('bx_sites_delay', '', @iCategId, 'Flash delay<br>(max. 45)', 'digit', '', '', '21', ''),
('bx_sites_quality', '', @iCategId, 'Quality<br>(0 .. 100)', 'digit', '', '', '22', '');
-- end stw integration


-- page compose
SET @iPCPOrder := (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES 
('bx_sites_main', 'Sites Home', @iPCPOrder + 1),
('bx_sites_profile', 'Sites User', @iPCPOrder + 2),
('bx_sites_view', 'Sites View Page', @iPCPOrder + 3),
('bx_sites_hon', 'Sites Rate', @iPCPOrder + 4);

-- index page
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Show list of latest sites', '_bx_sites_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'sites\', \'index_block\');', 1, 71.9, 'non,memb', 0);

-- profile page
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('profile', '1140px', 'Show list profile sites', '_bx_sites', 0, 0, 'PHP', 'return BxDolService::call(\'sites\', \'profile_block\', array($this->oProfileGen->_aProfile[\'NickName\']));', 1, 71.9, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('bx_sites_main', '1140px', 'View public feature', '_bx_sites_caption_public_feature', 2, 0, 'ViewFeature', '', 1, 28.1, 'non,memb', 0),
('bx_sites_main', '1140px', 'View site categories', '_bx_sites_caption_categories', 2, 1, 'Categories', '', 1, 28.1, 'non,memb', 0),
('bx_sites_main', '1140px', 'View site tags', '_bx_sites_caption_tags', 2, 2, 'Tags', '', 1, 28.1, 'non,memb', 0),
('bx_sites_main', '1140px', 'View recently public site', '_bx_sites_caption_public_last_featured', 1, 0, 'ViewRecent', '', 1, 71.9, 'non,memb', 0),
('bx_sites_main', '1140px', 'View latest public sites', '_bx_sites_caption_public_latest', 1, 1, 'ViewAll', '', 1, 71.9, 'non,memb', 0),

('bx_sites_profile', '1140px', 'Administration', '_bx_sites_bcaption_administration', 1, 0, 'Administration', '', 1, 100, 'non,memb', 0),
('bx_sites_profile', '1140px', 'Owner Sites', '_bx_sites_bcaption_owner_sites', 1, 1, 'Owner', '', 1, 100, 'non,memb', 0),

('bx_sites_view', '1140px', 'Information on Site', '_bx_sites_bcaption_information', 2, 0, 'ViewInformation', '', 1, 28.1, 'non,memb', 0),
('bx_sites_view', '1140px', 'Actions for Site', '_bx_sites_bcaption_actions', 2, 1, 'ViewActions', '', 1, 28.1, 'non,memb', 0),
('bx_sites_view', '1140px', 'Site Social Sharing', '_sys_block_title_social_sharing', 2, 2, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('bx_sites_view', '1140px', 'Image Site', '_bx_sites_bcaption_image', 1, 0, 'ViewImage', '', 1, 71.9, 'non,memb', 0),
('bx_sites_view', '1140px', 'Description Site', '_bx_sites_bcaption_description', 1, 1, 'ViewDescription', '', 1, 71.9, 'non,memb', 0),
('bx_sites_view', '1140px', 'Comments for Site', '_bx_sites_bcaption_comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),

('bx_sites_hon', '1140px', 'Previously Rated', '_bx_sites_bcaption_previously', 2, 0, 'ViewPreviously', '', 1, 28.1, 'non,memb', 0),
('bx_sites_hon', '1140px', 'Rate Site', '_bx_sites_bcaption_rate', 1, 0, 'ViewRate', '', 1, 71.9, 'non,memb', 0);

-- vote objects
INSERT INTO `sys_objects_vote` VALUES (NULL, 'bx_sites', 'bx_sites_rating', 'bx_sites_rating_track', 'sites_', '5', 'vote_send_result', 'BX_PERIOD_PER_VOTE', '1', '', '', 'bx_sites_main', 'rate', 'rateCount', 'id', 'BxSitesVoting', 'modules/boonex/sites/classes/BxSitesVoting.php');

-- comments objects
INSERT INTO `sys_objects_cmts` VALUES (NULL, 'bx_sites', 'bx_sites_cmts', 'bx_sites_cmts_track', '0', '1', '90', '5', '1', '-3', 'none', '0', '1', '0', 'cmt', 'bx_sites_main', 'id', 'commentsCount', 'BxSitesCmts', 'modules/boonex/sites/classes/BxSitesCmts.php');

-- views objects
INSERT INTO `sys_objects_views` VALUES(NULL, 'bx_sites', 'bx_sites_views_track', 86400, 'bx_sites_main', 'id', 'views', 1);

-- search objects
INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`)
VALUES ('bx_sites', '_bx_sites', 'BxSitesSearchResult', 'modules/boonex/sites/classes/BxSitesSearchResult.php');

-- tag objects
INSERT INTO `sys_objects_tag` VALUES (NULL, 'bx_sites', 'SELECT `tags` FROM `[db_prefix]main` WHERE `id` = {iID} AND `status` = ''approved''', 'bx_sites_permalinks', 'm/sites/browse/tag/{tag}', 'modules/?r=sites/browse/tag/{tag}', '_bx_sites');

-- category objects
INSERT INTO `sys_objects_categories` VALUES (NULL, 'bx_sites', 'SELECT `categories` FROM `[db_prefix]main` WHERE `id` = {iID} AND `status` = ''approved''', 'bx_sites_permalinks', 'm/sites/browse/category/{tag}', 'modules/?r=sites/browse/category/{tag}', '_bx_sites');

-- categories
INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES
    ('Sites', '0', 'bx_photos', '0', 'active'),
    ('Technology', '0', 'bx_sites', '0', 'active'),
    ('World & Business', '0', 'bx_sites', '0', 'active'),
    ('Science', '0', 'bx_sites', '0', 'active'),
    ('Gaming', '0', 'bx_sites', '0', 'active'),
    ('Lifestyle', '0', 'bx_sites', '0', 'active'),
    ('Entertainment', '0', 'bx_sites', '0', 'active'),
    ('Sports', '0', 'bx_sites', '0', 'active');

-- users actions
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  $oConfig->getBaseUri() . ''edit/{ID}'';', 0, 'bx_sites'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', 1, 'bx_sites'),
    ('{TitleShare}', 'share-square-o', '', 'bx_site_show_share_popup()', '', 2, 'bx_sites'),
    ('{AddToFeatured}', 'star-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'');return false;', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''featured/{ID}'';', 3, 'bx_sites'),
    ('{evalResult}', 'plus', '{BaseUri}browse/my/add', '', 'if (($GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin'']) && {isAllowedAdd} == 1) return _t(''_bx_sites_action_add_site''); return;', 1, 'bx_sites_title'),
    ('{evalResult}', 'link', '{BaseUri}browse/my', '', 'if ($GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin'']) return _t(''_bx_sites_action_my_sites''); return;', 2, 'bx_sites_title'),
    ('{sbs_sites_title}', 'paperclip', '', '{sbs_sites_script}', '', 6, 'bx_sites'),
    ('{repostCpt}', 'repeat', '', '{repostScript}', '', 7, 'bx_sites');

-- site stats
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'sts', 'bx_sites', 'modules/?r=sites/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `status`=''approved''', 'modules/?r=sites/administration', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `status`=''pending''', 'link', @iStatSiteOrder);

-- PQ statistics
INSERT INTO `sys_stat_member` VALUES ('bx_sites', 'SELECT COUNT(*) FROM `[db_prefix]main` WHERE `ownerid` = ''__member_id__'' AND `status`=''approved''');
INSERT INTO `sys_stat_member` VALUES ('bx_sitesp', 'SELECT COUNT(*) FROM `[db_prefix]main` WHERE `ownerid` = ''__member_id__'' AND `status`!=''approved''');
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_sites', '__bx_sites__ (<a href="modules/?r=sites/browse/my/add">__l_add__</a>)');

-- membership actions
SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites view', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites browse', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites search', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites add', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);
    
INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites edit any site', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites delete any site', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites mark as featured', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'sites approve', NULL);
    
-- alert handlers
INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_sites', 'BxSitesProfileDeleteResponse', 'modules/boonex/sites/classes/BxSitesProfileDeleteResponse.php', '');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'profile', 'delete', @iHandler);

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_sites', '_bx_sites', '{siteUrl}modules/?r=sites/administration', 'Sites module by BoonEx', 'link', @iMax+1);

-- privacy
INSERT INTO `sys_privacy_actions`(`module_uri`, `name`, `title`, `default_group`) VALUES
('bx_sites', 'view', '_bx_sites_privacy_view', '3'),
('bx_sites', 'comments', '_bx_sites_privacy_comment', '3'),
('bx_sites', 'rate', '_bx_sites_privacy_vote', '3');

-- subscriptions
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsSitesComments', 'New Comments To A Site Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">site you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to site post', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('bx_sites', '', '', 'return BxDolService::call(''sites'', ''get_subscription_params'', array($arg2, $arg3));'),
('bx_sites', 'commentPost', 't_sbsSitesComments', 'return BxDolService::call(''sites'', ''get_subscription_params'', array($arg2, $arg3));');

-- sitemap
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_sites', '_bx_sites', '0.8', 'auto', 'BxSitesSiteMaps', 'modules/boonex/sites/classes/BxSitesSiteMaps.php', @iMaxOrderSiteMaps, 1);

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_sites', '_bx_sites', 'bx_sites_main', 'date', '', '', 1, @iMaxOrderCharts);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_sites', '_sys_module_sites', 'BxSitesExport', 'modules/boonex/sites/classes/BxSitesExport.php', @iMaxOrderExports, 1);


-- begin stw integration
-- stw requests
CREATE TABLE IF NOT EXISTS `[db_prefix]stw_requests` (
  `siteid` INT(10) NOT NULL AUTO_INCREMENT,
  `domain` VARCHAR(255) collate utf8_unicode_ci NOT NULL default '',
  `hash` VARCHAR(32) collate utf8_unicode_ci NOT NULL,
  `timestamp` VARCHAR(12) collate utf8_unicode_ci NOT NULL default '',
  `capturedon` VARCHAR(12) collate utf8_unicode_ci NOT NULL,
  `quality` SMALLINT(3) NOT NULL default '90',
  `full` TINYINT(1) NOT NULL default '0',
  `xmax` SMALLINT(4) NOT NULL default '200',
  `ymax` SMALLINT(4) NOT NULL default '150',
  `nrx` SMALLINT(4) NOT NULL default '1024',
  `nry` SMALLINT(4) NOT NULL default '768',
  `invalid` TINYINT(1) NOT NULL,
  `stwerrcode` VARCHAR(50) collate utf8_unicode_ci NOT NULL,
  `error` TINYINT(1) NOT NULL default '0',
  `errcode` VARCHAR(50) collate utf8_unicode_ci NOT NULL,
  `referrer` TINYINT(1) NOT NULL,
  PRIMARY KEY  (`siteid`),
  UNIQUE KEY `hash_idx` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- stw account info
CREATE TABLE IF NOT EXISTS `[db_prefix]stwacc_info` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `key_id` VARCHAR(255) collate utf8_unicode_ci NOT NULL default '',
  `account_level` TINYINT(1) NOT NULL default '0',
  `inside_pages` TINYINT(1) NOT NULL default '0',
  `custom_size` TINYINT(1) NOT NULL default '0',
  `full_length` TINYINT(1) NOT NULL default '0',
  `refresh_ondemand` TINYINT(1) NOT NULL default '0',
  `custom_delay` TINYINT(1) NOT NULL default '0',
  `custom_quality` TINYINT(1) NOT NULL default '0',
  `custom_resolution` TINYINT(1) NOT NULL default '0',
  `custom_messages` TINYINT(1) NOT NULL default '0',
  `timestamp` VARCHAR(12) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- end stw integration
