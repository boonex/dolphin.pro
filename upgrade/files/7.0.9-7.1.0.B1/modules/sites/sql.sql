
-- menu top

SET @iMenuSitesSystem = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'Sites');
UPDATE `sys_menu_top` SET `Picture` = 'link' WHERE `ID` = @iMenuSitesSystem;

SET @iMenuSitesTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Sites');
UPDATE `sys_menu_top` SET `Picture` = 'link', `Icon` = 'link' WHERE `ID` = @iMenuSitesTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuSitesTop;
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=sites/browse/user/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Sites';


-- member menu

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_sites';

SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_sites', `Eval` = 'return BxDolService::call(''sites'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- options

DELETE FROM `sys_options` WHERE `Name` IN ('bx_sites_thumb_url', 'bx_sites_thumb_service', 'bx_sites_thumb_action', 'bx_sites_thumb_access_key', 'bx_sites_thumb_pswd');
DELETE FROM `sys_options` WHERE `Name` IN ('bx_sites_key_id', 'bx_sites_secret_key', 'bx_sites_account_type', 'bx_sites_cache_days', 'bx_sites_debug', 'bx_sites_inside_pages', 'bx_sites_custom_msg_url', 'bx_sites_thumb_size', 'bx_sites_thumb_size_custom', 'bx_sites_full_size', 'bx_sites_max_height', 'bx_sites_native_res', 'bx_sites_widescreen_y', 'bx_sites_redo', 'bx_sites_delay', 'bx_sites_quality');

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Sites' LIMIT 1);

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_sites_key_id', '', @iCategId, 'ShrinkTheWeb Access Key', 'digit', '', '', '7', ''),
('bx_sites_secret_key', '', @iCategId, 'ShrinkTheWeb Secret Key', 'digit', '', '', '8', ''),
('bx_sites_account_type', 'No Automated Screenshots', @iCategId, 'ShrinkTheWeb Account Type', 'select', 'return strlen($arg0) > 0;', 'cannot be empty.', '9', 'No Automated Screenshots,Enabled'),
('bx_sites_cache_days', '7', @iCategId, 'Cache days<br>(how many days the images are valid in your cache,<br>Enter 0 (zero) to never update screenshots once cached or<br>-1 to disable caching and always use embedded method instead)', 'digit', '', '', '10', ''),
('bx_sites_debug', 'off', @iCategId, 'Debug<br>(store debug info in database)', 'checkbox', '', '', '11', ''),
('bx_sites_inside_pages', 'off', @iCategId, 'Inside Page Captures<br>(i.e. not just homepages and sub-domains,<br>select if you have purchased this pro package)', 'checkbox', '', '', '12', ''),
('bx_sites_custom_msg_url', '', @iCategId, 'Custom Messages URL<br>(specify the URL where your custom<br>message images are stored)', 'digit', '', '', '13', ''),
('bx_sites_thumb_size', 'lg', @iCategId, 'Default Thumbnail size<br>(width: mcr 75px, tny 90px, vsm 100px,<br>sm 120px, lg 200px, xlg 320px)', 'select', 'return strlen($arg0) > 0;', 'cannot be empty.', '14', 'mcr,tny,vsm,sm,lg,xlg'),
('bx_sites_thumb_size_custom', '', @iCategId, 'Custom Width<br>(enter your custom image width,<br>this will override default size)', 'digit', '', '', '15', ''),
('bx_sites_full_size', '', @iCategId, 'Full-Length capture', 'checkbox', '', '', '16', ''),
('bx_sites_max_height', '', @iCategId, 'Max height<br>(use if you want to set maxheight for fullsize capture)', 'digit', '', '', '17', ''),
('bx_sites_native_res', '', @iCategId, 'Native resolution<br>(i.e. 640 for 640x480)', 'digit', '', '', '18', ''),
('bx_sites_widescreen_y', '', @iCategId, 'Widescreen resolution Y<br>(i.e. 900 for 1440x900 if 1440 is<br>set for Native resolution)', 'digit', '', '', '19', ''),
('bx_sites_redo', 'off', @iCategId, 'Refresh On-Demand<br>(select if you have purchased this pro package<br>and want to allow your members to use it)', 'checkbox', '', '', '20', ''),
('bx_sites_delay', '', @iCategId, 'Flash delay<br>(max. 45)', 'digit', '', '', '21', ''),
('bx_sites_quality', '', @iCategId, 'Quality<br>(0 .. 100)', 'digit', '', '', '22', '');


-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile') AND `Desc` IN ('Show list of latest sites', 'Show list profile sites');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_main' AND `Func` IN ('ViewFeature', 'Categories', 'Tags', 'ViewRecent', 'ViewAll');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_profile' AND `Func` IN ('Administration', 'Owner');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_view' AND `Func` IN ('ViewInformation', 'ViewActions', 'SocialSharing', 'ViewImage', 'ViewDescription', 'ViewComments');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_hon' AND `Func` IN ('ViewPreviously', 'ViewRate');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('bx_sites_main', 'bx_sites_profile', 'bx_sites_view', 'bx_sites_hon');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Show list of latest sites', '_bx_sites_bcaption_latest', 0, 0, 'PHP', 'return BxDolService::call(\'sites\', \'index_block\');', 1, 71.9, 'non,memb', 0);

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


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_sites' AND `Caption` IN ('{TitleEdit}', '{TitleDelete}', '{TitleShare}', '{AddToFeatured}', '{sbs_sites_title}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_sites_title' AND (`Eval` LIKE '%_bx_sites_action_add_site%' OR `Eval` LIKE '%_bx_sites_action_my_sites%' OR `Eval` LIKE '%_bx_sites_action_home_sites%');

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  $oConfig->getBaseUri() . ''edit/{ID}'';', 0, 'bx_sites'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', 1, 'bx_sites'),
    ('{TitleShare}', 'share', '', 'bx_site_show_share_popup()', '', 2, 'bx_sites'),
    ('{AddToFeatured}', 'star-empty', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'');return false;', '$oConfig = $GLOBALS[''oBxSitesModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''featured/{ID}'';', 3, 'bx_sites'),
    ('{evalResult}', 'plus', '{BaseUri}browse/my/add', '', 'if (($GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin'']) && {isAllowedAdd} == 1) return _t(''_bx_sites_action_add_site''); return;', 1, 'bx_sites_title'),
    ('{evalResult}', 'link', '{BaseUri}browse/my', '', 'if ($GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin'']) return _t(''_bx_sites_action_my_sites''); return;', 2, 'bx_sites_title'),
    ('{sbs_sites_title}', 'paper-clip', '', '{sbs_sites_script}', '', 6, 'bx_sites');


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'sts';

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'sts', 'bx_sites', 'modules/?r=sites/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `status`=''approved''', 'modules/?r=sites/administration', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `status`=''pending''', 'link', @iStatSiteOrder);


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'link' WHERE `name` = 'bx_sites';


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsSitesComments', 't_sbsSitesRates');

INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsSitesComments', 'New Comments To A Site Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">site you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to site post', 0);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_sites' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_sites' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_sites';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_sites', '_bx_sites', '0.8', 'auto', 'BxSitesSiteMaps', 'modules/boonex/sites/classes/BxSitesSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_sites';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_sites', '_bx_sites', 'bx_sites_main', 'date', '', '', 1, @iMaxOrderCharts);


-- stw integration

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



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_sites_action_home_sites','_bx_sites_bcaption_add','_bx_sites_bcaption_pending_approval','_bx_sites_bcaption_profile_all','_bx_sites_bcaption_site','_bx_sites_box_caption','_bx_sites_caption_browse','_bx_sites_caption_public_recent','_bx_sites_err_allow_comments','_bx_sites_err_allow_rate','_bx_sites_err_allow_view','_bx_sites_err_not_logged_in','_bx_sites_info_allow_comments','_bx_sites_info_allow_rate','_bx_sites_info_allow_view','_bx_sites_main','_bx_sites_page_caption','_bx_sites_pending','_bx_sites_sbs_main','_bx_sites_sbs_votes');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_sites_action_home_sites','_bx_sites_bcaption_add','_bx_sites_bcaption_pending_approval','_bx_sites_bcaption_profile_all','_bx_sites_bcaption_site','_bx_sites_box_caption','_bx_sites_caption_browse','_bx_sites_caption_public_recent','_bx_sites_err_allow_comments','_bx_sites_err_allow_rate','_bx_sites_err_allow_view','_bx_sites_err_not_logged_in','_bx_sites_info_allow_comments','_bx_sites_info_allow_rate','_bx_sites_info_allow_view','_bx_sites_main','_bx_sites_page_caption','_bx_sites_pending','_bx_sites_sbs_main','_bx_sites_sbs_votes');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'sites' AND `version` = '1.0.9';

