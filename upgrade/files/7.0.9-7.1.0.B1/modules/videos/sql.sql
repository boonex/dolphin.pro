
-- update hidden album privacy

SET @sHiddenAlbumName = (SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_album_default_name');
SET @sHiddenAlbumName = IFNULL(@sHiddenAlbumName, 'Hidden');
UPDATE `sys_albums` SET `AllowAlbumView` = 8 WHERE `Caption` = @sHiddenAlbumName AND `Type` = 'bx_videos';

-- create tables

CREATE TABLE IF NOT EXISTS `[db_prefix]_cmts_albums` (
  `cmt_id` int(11) NOT NULL auto_increment,
  `cmt_parent_id` int(11) NOT NULL default '0',
  `cmt_object_id` int(10) unsigned NOT NULL default '0',
  `cmt_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL,
  `cmt_mood` tinyint NOT NULL default '0',
  `cmt_rate` int(11) NOT NULL default '0',
  `cmt_rate_count` int(11) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_id`),
  KEY `cmt_object_id` (`cmt_object_id`,`cmt_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- options

DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_album_slideshow_on';
DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_album_slideshow_height';

SET @iKatID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Videos');
DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_max_file_size';
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('[db_prefix]_max_file_size', '64', @iKatID, 'Maximal size of one file (in Megabytes)', 'digit', '', '', 3, '');


-- page builder

DELETE FROM `sys_page_compose_pages` WHERE `Name` IN ('[db_prefix]_view', '[db_prefix]_home', '[db_prefix]_rate', '[db_prefix]_album_view', '[db_prefix]_albums_owner');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('[db_prefix]_view', 'View Video', @iPCPOrder+1),
('[db_prefix]_home', 'Videos Home', @iPCPOrder+2),
('[db_prefix]_rate', 'Videos Rate', @iPCPOrder+3),
('[db_prefix]_album_view', 'Video Album', @iPCPOrder+4),
('[db_prefix]_albums_owner', 'Profile Albums', @iPCPOrder+5);

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile', 'member') AND `Desc` IN ('Public Videos', 'Video Albums', 'Profile Videos');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_view' AND `Func` IN ('ViewFile', 'MainFileInfo', 'ViewComments', 'FileInfo', 'ActionList', 'LastAlbums', 'RelatedFiles', 'SocialSharing', 'FileAuthor', 'ViewAlbum');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_home' AND `Func` IN ('Albums', 'Special', 'Favorited', 'LatestFile', 'All', 'Calendar', 'Tags');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_rate' AND `Func` IN ('RatedSet', 'RateObject');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_album_view' AND `Func` IN ('Objects', 'Comments', 'Author');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_albums_my' AND `Func` IN ('adminShort', 'add', 'adminFull', 'adminFullDisapproved', 'edit', 'delete', 'organize', 'addObjects', 'manageObjects', 'manageObjectsDisapproved', 'manageObjectsNotProcessed', 'manageObjectsFailed', 'adminAlbumShort', 'albumObjects', 'my');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_albums_owner' AND `Func` IN ('Browse', 'Favorited');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('[db_prefix]_view', '[db_prefix]_home', '[db_prefix]_rate', '[db_prefix]_album_view', '[db_prefix]_albums_owner', '[db_prefix]_albums_my');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_album_view', '1140px', '', '', 1, 0, 0, 71.9, 0, 'non,memb', '', 'Objects'),
('[db_prefix]_album_view', '1140px', '', '', 1, 1, 1, 71.9, 0, 'non,memb', '_[db_prefix]_comments', 'Comments'),
('[db_prefix]_album_view', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_author', 'Author');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '1140px', '', '_[db_prefix]_view', 1, 1, 'ViewFile', '', 1, 71.9, 'non,memb', 380),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_author', 2, 1, 'FileAuthor', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info_main', 2, 2, 'MainFileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_actions', 2, 3, 'ActionList', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_album_videos_rest', 2, 5, 'ViewAlbum', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '1140px', '', '_[db_prefix]_albums', 2, 1, 'Albums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_special', 0, 0, 'Special', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_latest_file', 1, 0, 'LatestFile', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_public', 1, 2, 'All', '', 1, 71.9, 'non,memb', 380);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_home', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_calendar', 'Calendar'),
('[db_prefix]_home', '1140px', '', '', 2, 2, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_tags', 'Tags');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_admin', 1, 1, 'adminShort', '', 1, 100, 'memb', 380),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_add', 1, 0, 'add', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_admin', 1, 3, 'adminFull', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_disapproved', 1, 5, 'adminFullDisapproved', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_edit', 1, 6, 'edit', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_delete', 1, 7, 'delete', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_organize', 1, 8, 'organize', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_add_objects', 1, 9, 'addObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_manage_objects', 1, 10, 'manageObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_manage_objects_disapproved', 1, 11, 'manageObjectsDisapproved', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_manage_objects_not_processed', 1, 12, 'manageObjectsNotProcessed', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_manage_objects_failed', 1, 13, 'manageObjectsFailed', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_main_objects', 1, 14, 'adminAlbumShort', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_objects', 1, 20, 'albumObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_my', 1, 30, 'my', '', 1, 100, 'memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_albums_owner', 1, 1, 'Browse', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_favorited', 2, 1, 'Favorited', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_rate', '1140px', '', '_[db_prefix]_previous_rated', 1, 0, 'RatedSet', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_rate', '1140px', '', '_[db_prefix]_rate_header', 2, 0, 'RateObject', '', 1, 71.9, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Public Videos', '_[db_prefix]_public', 0, 0, 'PHP', 'require_once(BX_DIRECTORY_PATH_MODULES . ''boonex/videos/classes/BxVideosSearch.php'');\r\n $oMedia = new BxVideosSearch();\r\n $aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0)\r\n $aVisible[] = BX_DOL_PG_MEMBERS;\r\n $aCode = $oMedia->getBrowseBlock(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting'' => getParam(''[db_prefix]_mode_index''), ''per_page''=>(int)getParam(''[db_prefix]_number_index'')));\r\n return array($aCode[''code''], $aCode[''menu_top''], $aCode[''menu_bottom''], $aCode[''wrapper'']);', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Video Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''videos'', ''get_profile_albums_block'', array($this->iMember, BX_DOL_URL_ROOT . ''member.php''), ''Search'');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Video Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''videos'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0),
('profile', '1140px', 'Profile Videos', '_[db_prefix]_profile_videos', 0, 0, 'PHP', 'return BxDolService::call(''videos'', ''profile_video_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 71.9, 'non,memb', 0);


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_videos';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_videos', `Eval` = 'return BxDolService::call(''videos'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- objects: comments

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = '[db_prefix]_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`) VALUES 
('[db_prefix]_albums', '[db_prefix]_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '');


-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'film' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'VideosUnit';
UPDATE `sys_menu_top` SET `Picture` = 'film' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'VideosAlbum';
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=videos/albums/browse/owner/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Videos';
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=videos/albums/my/main/|modules/?r=videos/albums/my/add/|modules/?r=videos/albums/my/manage/|modules/?r=videos/albums/my/disapproved/' WHERE `Parent` = 4 AND `Name` = 'Videos';

SET @iMenuFilesTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Videos');
UPDATE `sys_menu_top` SET `Picture` = 'film', `Icon` = 'film' WHERE `ID` = @iMenuFilesTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuFilesTop AND `Name` = 'VideosHome';


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND `Caption` IN ('_[db_prefix]_action_share', '{featuredCpt}', '{sbs_[db_prefix]_title}');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND (`Script` LIKE '%favorite%');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND (`Eval` LIKE '%_Edit%' OR `Eval` LIKE '%_Delete%' OR `Eval` LIKE '%_[db_prefix]_action_report%');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]_title' AND (`Eval` LIKE '%_[db_prefix]_albums_add%' OR `Eval` LIKE '%_[db_prefix]_albums_my%' OR `Eval` LIKE '%_[db_prefix]_home%' OR `Eval` LIKE '%_sys_upload%'); 

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '_[db_prefix]_action_share', 'share', '', 'showPopupAnyHtml(''{moduleUrl}share/{fileUri}'')', '', 1),
('[db_prefix]', '{evalResult}', 'exclamation-sign', '', 'showPopupAnyHtml(''{moduleUrl}report/{fileUri}'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}'', false, ''post''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{evalResult}', 'edit', '', 'showPopupAnyHtml(''{moduleUrl}edit/{ID}'')', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''videos'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'', false, ''post'', true); return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''videos'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]', '{featuredCpt}', 'star-empty', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}feature/{ID}/{featured}'', false, ''post''); return false;', '', 7),
('[db_prefix]_title', '{evalResult}', 'plus', '', 'showPopupAnyHtml(''{BaseUri}upload'');', 'return (getLoggedId() && BxDolModule::getInstance(''BxVideosModule'')->isAllowedAdd()) ? _t(''_sys_upload'') : '''';', 8),
('[db_prefix]_title', '{evalResult}', 'film', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 9);

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'paper-clip', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_bx_videos_share', 't_bx_videos_report', 't_sbs_bx_videos_comments', 't_sbs_bx_videos_rates');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_bx_videos_share', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <a href="<MediaUrl>"><MediaType></a> with you!</p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Video sharing', 0),
('t_bx_videos_report', '<SenderNickName> Reported A Video', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<MediaUrl>">Reported <MediaType></a></p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Video report', 0),
('t_sbs_bx_videos_comments', 'New Comments To A Video', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<br /><p>The <a href="<ViewLink>">video you subscribed to got new comments!</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to video', 0);


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'pvi';

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('pvi', '[db_prefix]', 'modules/?r=videos/browse/all', 'SELECT COUNT(`ID`) FROM `RayVideoFiles` WHERE `Status`=''approved''', 'modules/?r=videos/administration/disapproved', 'SELECT COUNT(`ID`) FROM `RayVideoFiles` WHERE `Status`=''disapproved''', 'film', @iStatSiteOrder);


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'film' WHERE `name` = '[db_prefix]';


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = '[db_prefix]' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = '[db_prefix]' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = '[db_prefix]' OR `object` = '[db_prefix]_albums';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_videos', '0.8', 'auto', 'BxVideosSiteMapsVideos', 'modules/boonex/videos/classes/BxVideosSiteMapsVideos.php', @iMaxOrderSiteMaps, 1),
('[db_prefix]_albums', '_bx_videos_albums', '0.8', 'auto', 'BxVideosSiteMapsAlbums', 'modules/boonex/videos/classes/BxVideosSiteMapsAlbums.php', @iMaxOrderSiteMaps + 1, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = '[db_prefix]';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('[db_prefix]', '_bx_videos', 'RayVideoFiles', 'Date', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_videos_sbs_main','_bx_videos_sbs_rates','_bx_videos_wall_video');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_videos_sbs_main','_bx_videos_sbs_rates','_bx_videos_wall_video');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'videos' AND `version` = '1.0.9';

