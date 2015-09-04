
-- update hidden album privacy

SET @sHiddenAlbumName = (SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_album_default_name');
SET @sHiddenAlbumName = IFNULL(@sHiddenAlbumName, 'Hidden');
UPDATE `sys_albums` SET `AllowAlbumView` = 8 WHERE `Caption` = @sHiddenAlbumName AND `Type` = 'bx_photos';

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

SET @iKatID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Photos');

UPDATE `sys_options` SET `VALUE` = '5' WHERE `Name` = '[db_prefix]_number_top' AND `VALUE` = '4';
UPDATE `sys_options` SET `VALUE` = '750' WHERE `Name` = '[db_prefix]_file_width' AND `VALUE` = '600';
UPDATE `sys_options` SET `VALUE` = '750' WHERE `Name` = '[db_prefix]_file_height' AND `VALUE` = '600';

DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_album_slideshow_on';
DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_album_slideshow_height';

DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_number_albums_public_objects';
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('[db_prefix]_number_albums_public_objects', '4', @iKatID, 'Minimum number of photos required to display album in Public Albums block', 'digit', '', '', 29, '');

DELETE FROM `sys_options` WHERE `Name` = '[db_prefix]_header_cache';
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('[db_prefix]_header_cache', '0', @iKatID, 'Header Cache time (in seconds, leave 0 to disable)', 'digit', '', '', 41, '');


-- page builder

DELETE FROM `sys_page_compose_pages` WHERE `Name` IN ('[db_prefix]_view', '[db_prefix]_home', '[db_prefix]_album_view', '[db_prefix]_albums_owner', '[db_prefix]_rate');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('[db_prefix]_view', 'View Photo', @iPCPOrder+1),
('[db_prefix]_home', 'Photos Home', @iPCPOrder+2),
('[db_prefix]_rate', 'Photos Rate', @iPCPOrder+3),
('[db_prefix]_album_view', 'Photo Album', @iPCPOrder+4), 
('[db_prefix]_albums_owner', 'Profile Albums', @iPCPOrder+5);

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile', 'member') AND `Desc` IN ('Public Photos', 'Profile Photo Block', 'Profile Photo Album Block', 'Photo Albums', 'Profile Photo Switcher Block', 'Profile Photos Block');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_view' AND `Func` IN ('ViewFile', 'MainFileInfo', 'ViewComments', 'FileInfo', 'ActionList', 'LastAlbums', 'RelatedFiles', 'SocialSharing', 'FileAuthor', 'ViewAlbum');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_home' AND `Func` IN ('Albums', 'Special', 'Favorited', 'LatestFile', 'All', 'Calendar', 'Tags');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_rate' AND `Func` IN ('RatedSet', 'RateObject');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_album_view' AND `Func` IN ('Objects', 'Comments', 'Author');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_albums_my' AND `Func` IN ('adminShort', 'add', 'adminFull', 'adminFullDisapproved', 'edit', 'delete', 'organize', 'addObjects', 'manageObjects', 'manageObjectsDisapproved', 'manageObjectsPending', 'adminAlbumShort', 'albumObjects', 'my');
DELETE FROM `sys_page_compose` WHERE `Page` = '[db_prefix]_albums_owner' AND `Func` IN ('ProfilePhotos', 'Browse', 'Favorited');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('[db_prefix]_view', '[db_prefix]_home', '[db_prefix]_rate', '[db_prefix]_album_view', '[db_prefix]_albums_owner', '[db_prefix]_albums_my');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '1140px', '', '_[db_prefix]_view', 1, 1, 'ViewFile', '', 1, 71.9, 'non,memb', 380),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_author', 2, 1, 'FileAuthor', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info_main', 2, 2, 'MainFileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_actions', 2, 3, 'ActionList', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_album_photos_rest', 2, 5, 'ViewAlbum', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '1140px', '', '_[db_prefix]_albums', 2, 1, 'Albums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_special', 0, 0, 'Special', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_latest_file', 1, 0, 'LatestFile', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_public', 1, 2, 'All', '', 1, 71.9, 'non,memb', 380);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_home', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_calendar', 'Calendar'),
('[db_prefix]_home', '1140px', '', '', 2, 2, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_tags', 'Tags');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_album_view', '1140px', '', '', 1, 0, 0, 71.9, 0, 'non,memb', '', 'Objects'),
('[db_prefix]_album_view', '1140px', '', '', 1, 1, 1, 71.9, 0, 'non,memb', '_[db_prefix]_comments', 'Comments'),
('[db_prefix]_album_view', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_author', 'Author');

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
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_manage_objects_pending', 1, 12, 'manageObjectsPending', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_main_objects', 1, 15, 'adminAlbumShort', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_album_objects', 1, 20, 'albumObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_my', 1, 34, 'my', '', 1, 100, 'memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_photo_album_block', 1, 1, 'ProfilePhotos', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_albums_owner', 1, 2, 'Browse', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_favorited', 2, 1, 'Favorited', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_rate', '1140px', '', '_[db_prefix]_previous_rated', 1, 0, 'RatedSet', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_rate', '1140px', '', '_[db_prefix]_rate_header', 2, 0, 'RateObject', '', 1, 71.9, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Public Photos', '_[db_prefix]_public', 1, 10, 'PHP', 'require_once(BX_DIRECTORY_PATH_MODULES . ''boonex/photos/classes/BxPhotosSearch.php'');\r\n $oMedia = new BxPhotosSearch();\r\n $aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0)\r\n $aVisible[] = BX_DOL_PG_MEMBERS;\r\n $aCode = $oMedia->getBrowseBlock(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting'' => getParam(''[db_prefix]_mode_index''), ''per_page''=>(int)getParam(''[db_prefix]_number_index'')));\r\n return array($aCode[''code''], $aCode[''menu_top''], $aCode[''menu_bottom''], $aCode[''wrapper'']);', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Profile Photo Block', '_[db_prefix]_photo_block', 1, 1, 'PHP', 'return BxDolService::call(''photos'', ''profile_photo_block'', array(array(''PID'' => $this->oProfileGen->_iProfileID)), ''Search'');', 1, 28.1, 'non,memb', 263),
('profile', '1140px', 'Profile Photo Album Block', '_[db_prefix]_photo_album_block', 2, 1, 'PHP', 'return BxDolService::call(''photos'', ''get_profile_album_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Photo Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''photos'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0),
('profile', '1140px', 'Profile Photo Switcher Block', '_[db_prefix]_photo_switcher_block', 0, 0, 'PHP', 'return BxDolService::call(''photos'', ''profile_photo_switcher_block'', array(array(''PID'' => $this->oProfileGen->_iProfileID)), ''Search'');', 1, 28.1, 'non,memb', 263),
('member', '1140px', 'Profile Photos Block', '_[db_prefix]_photo_block', 0, 0, 'PHP', '$iPID = $this->iMember;\r\n if ($iPID > 0) {\r\n $aParams = array();\r\n $aParams[''PID''] = $iPID;\r\n $aParams[''Limit''] = 10;\r\n $aParams[''DisplayPagination''] = 1;\r\n	 $sRet = BxDolService::call(''photos'', ''profile_photo_block'', array($aParams), ''Search'');\r\n return $sRet;\r\n }', 1, 28.1, 'non,memb', 270);


-- objects: comments

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = '[db_prefix]_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`) VALUES
('[db_prefix]_albums', '[db_prefix]_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '');


-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'picture' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'PhotosUnit';
UPDATE `sys_menu_top` SET `Picture` = 'picture' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'PhotosAlbum';
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=photos/albums/browse/owner/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Photos';

SET @iMenuFilesTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Photos');
UPDATE `sys_menu_top` SET `Picture` = 'picture', `Icon` = 'picture' WHERE `ID` = @iMenuFilesTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuFilesTop AND `Name` = 'PhotosHome';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_photos';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_photos', `Eval` = 'return BxDolService::call(''photos'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND `Caption` IN ('_[db_prefix]_action_view_original', '_[db_prefix]_action_share', '{featuredCpt}', '{sbs_[db_prefix]_title}', '{TitleSetAsAvatar}');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND (`Script` LIKE '%favorite%');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]' AND (`Eval` LIKE '%_Edit%' OR `Eval` LIKE '%_Delete%' OR `Eval` LIKE '%_[db_prefix]_action_report%');
DELETE FROM `sys_objects_actions` WHERE `Type` = '[db_prefix]_title' AND (`Eval` LIKE '%_[db_prefix]_albums_add%' OR `Eval` LIKE '%_[db_prefix]_albums_my%' OR `Eval` LIKE '%_[db_prefix]_home%' OR `Eval` LIKE '%_sys_upload%'); 

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '_[db_prefix]_action_view_original', 'download-alt', '', 'window.open(''{moduleUrl}get_image/original/{fileKey}.{fileExt}'')', '', 0),
('[db_prefix]', '_[db_prefix]_action_share', 'share', '', 'showPopupAnyHtml(''{moduleUrl}share/{fileUri}'')', '', 1),
('[db_prefix]', '{evalResult}', 'exclamation-sign', '', 'showPopupAnyHtml(''{moduleUrl}report/{fileUri}'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}'', false, ''post''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{evalResult}', 'edit', '', 'showPopupAnyHtml(''{moduleUrl}edit/{ID}'')', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''photos'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'', false, ''post'', true); return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''photos'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]', '{featuredCpt}', 'star-empty', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}feature/{ID}/{featured}'', false, ''post''); return false;', '', 7),
('[db_prefix]_title', '{evalResult}', 'plus', '', 'showPopupAnyHtml(''{BaseUri}upload'');', 'return (getLoggedId() && BxDolModule::getInstance(''BxPhotosModule'')->isAllowedAdd()) ? _t(''_sys_upload'') : '''';', 8),
('[db_prefix]_title', '{evalResult}', 'picture', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 9);

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'paper-clip', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_bx_photos_share', 't_bx_photos_report', 't_sbs_bx_photos_comments', 't_sbs_bx_photos_rates');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_bx_photos_share', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <a href="<MediaUrl>"><MediaType></a> with you!</p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Photo sharing', 0),
('t_bx_photos_report', '<SenderNickName> Reported A Photo', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<MediaUrl>">Reported <MediaType></a></p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Photo report', 0),
('t_sbs_bx_photos_comments', 'New Comments To A Photo', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<br /><p>The <a href="<ViewLink>">photo you subscribed to got new comments!</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to photo', 0);


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'phs';

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('phs', '[db_prefix]', 'modules/?r=photos/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''approved''', 'modules/?r=photos/administration/disapproved', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''pending''', 'picture', @iStatSiteOrder);


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'picture' WHERE `name` = '[db_prefix]';


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
('[db_prefix]', '_bx_photos', '0.8', 'auto', 'BxPhotosSiteMapsPhotos', 'modules/boonex/photos/classes/BxPhotosSiteMapsPhotos.php', @iMaxOrderSiteMaps, 1),
('[db_prefix]_albums', '_bx_photos_albums', '0.8', 'auto', 'BxPhotosSiteMapsAlbums', 'modules/boonex/photos/classes/BxPhotosSiteMapsAlbums.php', @iMaxOrderSiteMaps + 1, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = '[db_prefix]';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('[db_prefix]', '_bx_photos', 'bx_photos_main', 'Date', '', '', 1, @iMaxOrderCharts);


-- objects: member info

DELETE FROM `sys_objects_member_info` WHERE `object` = 'bx_photos_thumb' OR `object` = 'bx_photos_icon';
INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('bx_photos_thumb', '_bx_photos_member_info_profile_photo', 'thumb', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php'),
('bx_photos_icon', '_bx_photos_member_info_profile_photo_icon', 'thumb_icon', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php');



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_photos_albums_latest','_bx_photos_info','_bx_photos_sbs_main','_bx_photos_sbs_rates','_bx_photos_wall_photo');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_photos_albums_latest','_bx_photos_info','_bx_photos_sbs_main','_bx_photos_sbs_rates','_bx_photos_wall_photo');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'photos' AND `version` = '1.0.9';

