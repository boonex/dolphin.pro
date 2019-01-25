CREATE TABLE `[db_prefix]_main` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Categories` text NOT NULL default '',
  `Owner` int(10) unsigned default NULL,
  `Ext` varchar(4) default '',
  `Size` varchar(10) default '',
  `Title` varchar(255) default '',
  `Uri` varchar(255) NOT NULL default '',
  `Desc` text NOT NULL,
  `Tags` varchar(255) NOT NULL default '',
  `Date` int(11) NOT NULL default '0',
  `Views` int(11) default '0',
  `Rate` float NOT NULL default '0',
  `RateCount` int(11) NOT NULL default '0',
  `CommentsCount` int(11) NOT NULL default '0',
  `Featured` tinyint(4) NOT NULL default '0',
  `Status` enum('approved','disapproved','pending') NOT NULL default 'pending',
  `Hash` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Hash` (`Hash`),
  UNIQUE KEY `Uri` (`Uri`),
  KEY `Owner` (`Owner`),
  KEY `Date` (`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Favorites
CREATE TABLE `[db_prefix]_favorites` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `Date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`,`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- FULLTEXT search
ALTER TABLE `[db_prefix]_main` ADD FULLTEXT KEY `ftMain` (`Title`, `Tags`, `Desc`, `Categories`);
ALTER TABLE `[db_prefix]_main` ADD FULLTEXT KEY `ftTags` (`Tags`);
ALTER TABLE `[db_prefix]_main` ADD FULLTEXT KEY `ftCategories` (`Categories`);

-- Comments Tables
CREATE TABLE `[db_prefix]_cmts` (
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

CREATE TABLE `[db_prefix]_cmts_albums` (
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

-- main rating table
CREATE TABLE `[db_prefix]_rating` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_rating_count` int(11) NOT NULL default '0',
  `gal_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `med_id` (`gal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- rating vote track
CREATE TABLE `[db_prefix]_voting_track` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_ip` varchar(20) default NULL,
  `gal_date` datetime default NULL,
  KEY `med_ip` (`gal_ip`,`gal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- views system
CREATE TABLE IF NOT EXISTS `[db_prefix]_views_track` (
  `id` int(10) unsigned NOT NULL,
  `viewer` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  KEY `id` (`id`,`viewer`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_options_cats` SET `name` = 'Photos';
SET @iKatID = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('[db_prefix]_activation', 'on', @iKatID, 'Enable auto-activation for photos', 'checkbox', '', '', 1, ''),
('category_auto_app_[db_prefix]', 'on', @iKatID, 'Autoapprove categories of photos', 'checkbox', '', '', 2, ''),
('[db_prefix]_allowed_exts', 'jpg jpeg png gif', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('[db_prefix]_profile_album_name', '{nickname}''s photos', @iKatID, 'Default profile album name', 'digit', '', '', 4, ''),
('[db_prefix]_profile_cover_album_name', '{nickname}''s cover photos', @iKatID, 'Default profile cover album name', 'digit', '', '', 5, ''),
('[db_prefix]_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('[db_prefix]_number_index', '9', @iKatID, 'How many photos show on main index page', 'digit', '', '', 12, ''),
('[db_prefix]_number_home', '12', @iKatID, 'How many photos show on photos home page', 'digit', '', '', 14, ''),
('[db_prefix]_number_all', '12', @iKatID, 'How many photos show on browse photos page', 'digit', '', '', 16, ''),
('[db_prefix]_number_top', '6', @iKatID, 'How many photos show in featured, top, and similar sections', 'digit', '', '', 18, ''),
('[db_prefix]_number_related', '3', @iKatID, 'Number of related photos by user', 'digit', '', '', 20, ''),
('[db_prefix]_number_previous_rated', '3', @iKatID, 'Number of previous rated photos', 'digit', '', '', 22, ''),
('[db_prefix]_number_albums_home', '3', @iKatID, 'How many albums show on photos home page', 'digit', '', '', 24, ''),
('[db_prefix]_number_albums_browse', '9', @iKatID, 'How many albums show on browse albums page', 'digit', '', '', 26, ''),
('[db_prefix]_number_albums_public_objects', '4', @iKatID, 'Minimum number of photos required to display album in Public Albums block', 'digit', '', '', 28, ''),
('[db_prefix]_number_view_album', '6', @iKatID, 'How many photos show on view album page', 'digit', '', '', 30, ''),
('[db_prefix]_file_width', '750', @iKatID, 'Width of main photo unit (in pixels)', 'digit', '', '', 34, ''),
('[db_prefix]_file_height', '750', @iKatID, 'Height of main photo unit (in pixels)', 'digit', '', '', 35, ''),
('[db_prefix]_client_width', '2048', @iKatID, 'Width for photo resizing in browser (in pixels)', 'digit', '', '', 38, ''),
('[db_prefix]_client_height', '2048', @iKatID, 'Height for photo resizing in browser (in pixels)', 'digit', '', '', 39, ''),
('[db_prefix]_flickr_photo_api', '', @iKatID, 'Flickr API key. You can get Flickr API keys here: https://www.flickr.com/services/api/keys/', 'digit', '', '', 50, ''),
('[db_prefix]_rss_feed_on', 'on', @iKatID, 'Enable RSS feed', 'checkbox', '', '', 52, ''),
('[db_prefix]_uploader_switcher', 'html5,record,embed', @iKatID, 'Available uploaders', 'list', '', '', 54, 'html5,regular,record,embed'),
('[db_prefix]_header_cache', '0', @iKatID, 'Header Cache time (in seconds, leave 0 to disable)', 'digit', '', '', 56, ''),
('[db_prefix]_cover_rows', '4', @iKatID, 'Number of rows in Photos Home page Cover', 'digit', '', '', 61, ''),
('[db_prefix]_cover_columns', '10', @iKatID, 'Number of columns in Photos Home page Cover', 'digit', '', '', 62, ''),
('[db_prefix]_cover_featured', '', @iKatID, 'Use featured photos for Photos Home page Cover', 'checkbox', '', '', 63, '');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('[db_prefix]_view', 'Photos View Photo', @iPCPOrder+1),
('[db_prefix]_home', 'Photos Home', @iPCPOrder+2),
('[db_prefix]_rate', 'Photos Rate', @iPCPOrder+3),
('[db_prefix]_album_view', 'Photos View Album', @iPCPOrder+4), 
('[db_prefix]_albums_owner', 'Photos Profile Albums', @iPCPOrder+5),
('[db_prefix]_crop', 'Photos Crop Photo', @iPCPOrder+6);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '1140px', '', '_[db_prefix]_view', 1, 1, 'ViewFile', '', 1, 71.9, 'non,memb', 380),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_author', 2, 1, 'FileAuthor', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info_main', 2, 2, 'MainFileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_actions', 2, 3, 'ActionList', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_album_photos_rest', 2, 5, 'ViewAlbum', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_related', 0, 0, 'RelatedFiles', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '1140px', '', '_[db_prefix]_cover', 1, 1, 'Cover', '', 0, 100, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_albums', 3, 1, 'Albums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_special', 0, 0, 'Special', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_latest_file', 2, 0, 'LatestFile', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_public', 2, 2, 'All', '', 1, 71.9, 'non,memb', 380);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_home', '1140px', '', '', 3, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_calendar', 'Calendar'),
('[db_prefix]_home', '1140px', '', '', 3, 2, 1, 28.1, 0, 'non,memb', '_[db_prefix]_top_menu_tags', 'Tags');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_album_view', '1140px', '', '', 1, 0, 0, 71.9, 0, 'non,memb', '', 'Objects'),
('[db_prefix]_album_view', '1140px', '', '', 1, 1, 1, 71.9, 0, 'non,memb', '_[db_prefix]_comments', 'Comments'),
('[db_prefix]_album_view', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_author', 'Author'),
('[db_prefix]_album_view', '1140px', '', '', 2, 1, 1, 28.1, 0, 'non,memb', '_[db_prefix]_info_album', 'Info'),
('[db_prefix]_album_view', '1140px', '', '', 2, 2, 1, 28.1, 0, 'memb', '_[db_prefix]_actions', 'Actions');

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
('[db_prefix]_crop', '1140px', '', '_[db_prefix]_crop', 1, 0, 'Crop', '', 1, 100, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Public Photos', '_[db_prefix]_public', 1, 10, 'PHP', 'require_once(BX_DIRECTORY_PATH_MODULES . ''boonex/photos/classes/BxPhotosSearch.php'');\r\n $oMedia = new BxPhotosSearch();\r\n $aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0)\r\n $aVisible[] = BX_DOL_PG_MEMBERS;\r\n $aCode = $oMedia->getBrowseBlock(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting'' => getParam(''[db_prefix]_mode_index''), ''per_page''=>(int)getParam(''[db_prefix]_number_index'')));\r\n return array($aCode[''code''], $aCode[''menu_top''], $aCode[''menu_bottom''], $aCode[''wrapper'']);', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Profile Photo Block', '_[db_prefix]_photo_block', 0, 0, 'PHP', 'return BxDolService::call(''photos'', ''profile_photo_block'', array(array(''PID'' => $this->oProfileGen->_iProfileID)), ''Search'');', 1, 28.1, 'non,memb', 263),
('profile', '1140px', 'Profile Photo Album Block', '_[db_prefix]_photo_album_block', 2, 1, 'PHP', 'return BxDolService::call(''photos'', ''get_profile_album_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0),
('profile', '1140px', 'Photo Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''photos'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0),
('profile', '1140px', 'Profile Photo Switcher Block', '_[db_prefix]_photo_switcher_block', 0, 0, 'PHP', 'return BxDolService::call(''photos'', ''profile_photo_switcher_block'', array(array(''PID'' => $this->oProfileGen->_iProfileID)), ''Search'');', 1, 28.1, 'non,memb', 263),
('member', '1140px', 'Profile Photos Block', '_[db_prefix]_photo_block', 0, 0, 'PHP', '$iPID = $this->iMember;\r\n if ($iPID > 0) {\r\n $aParams = array();\r\n $aParams[''PID''] = $iPID;\r\n $aParams[''Limit''] = 10;\r\n $aParams[''DisplayPagination''] = 1;\r\n	 $sRet = BxDolService::call(''photos'', ''profile_photo_block'', array($aParams), ''Search'');\r\n return $sRet;\r\n }', 1, 28.1, 'non,memb', 270);

INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('[db_prefix]', '[db_prefix]_cmts', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '[db_prefix]_main', 'ID', 'CommentsCount', 'BxPhotosCmts', 'modules/boonex/photos/classes/BxPhotosCmts.php'),
('[db_prefix]_albums', '[db_prefix]_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxPhotosCmtsAlbums', 'modules/boonex/photos/classes/BxPhotosCmtsAlbums.php');

INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`) 
VALUES ('[db_prefix]', '[db_prefix]_rating', '[db_prefix]_voting_track', 'gal_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, 'BxPhotosRate', 'modules/boonex/photos/classes/BxPhotosRate.php', '[db_prefix]_main', 'Rate', 'RateCount', 'ID');

INSERT INTO `sys_objects_views` (`name`, `table_track`, `period`, `trigger_table`, `trigger_field_id`, `trigger_field_views`, `is_on`)
VALUES ('[db_prefix]', '[db_prefix]_views_track', 86400, '[db_prefix]_main', 'ID', 'Views', 1);

SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(0, 'Photos', '_[db_prefix]_top_menu_item', 'modules/?r=photos/home/|modules/?r=photos/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'picture-o', 'picture-o', 1, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'PhotosHome', '_[db_prefix]_top_menu_home', 'modules/?r=photos/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosAlbums', '_[db_prefix]_top_menu_albums', 'modules/?r=photos/albums/browse/all', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosAll', '_[db_prefix]_top_menu_all', 'modules/?r=photos/browse/all', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosTop', '_[db_prefix]_top_menu_top', 'modules/?r=photos/browse/top', 15, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosPopular', '_[db_prefix]_top_menu_popular', 'modules/?r=photos/browse/popular', 20, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosFeatured', '_[db_prefix]_top_menu_featured', 'modules/?r=photos/browse/featured', 25, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosTags', '_[db_prefix]_top_menu_tags', 'modules/?r=photos/tags', 30, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosCategories', '_[db_prefix]_top_menu_categories', 'modules/?r=photos/categories', 35, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosRate', '_[db_prefix]_top_menu_rate', 'modules/?r=photos/rate', 36, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosCalendar', '_[db_prefix]_top_menu_calendar', 'modules/?r=photos/calendar|modules/?r=photos/browse/calendar/', 40, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'PhotosSearch', '_[db_prefix]_top_menu_search', 'searchKeyword.php?type=bx_photos', 45, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(9, 'Photos', '_[db_prefix]_menu_profile', 'modules/?r=photos/albums/browse/owner/{profileUsername}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(4, 'Photos', '_[db_prefix]_menu_profile', 'modules/?r=photos/albums/my/main/|modules/?r=photos/albums/my/add/|modules/?r=photos/albums/my/manage/|modules/?r=photos/albums/my/disapproved/', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'PhotosUnit', '_[db_prefix]_top_menu_item', 'modules/?r=photos/view/|modules/?r=photos/crop/|photo/gallery/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'picture-o', 0, ''),
(0, 'PhotosAlbum',  '_[db_prefix]_top_menu_item', 'modules/?r=photos/browse/album/|modules/?r=photos/albums/my/edit/|modules/?r=photos/albums/my/organize/|modules/?r=photos/albums/my/add_objects/|modules/?r=photos/albums/my/manage_objects', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'picture-o', 0, '');

SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_photos', `Eval` = 'return BxDolService::call(''photos'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=photos/', 'm/photos/', '[db_prefix]_permalinks');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`)
VALUES ('[db_prefix]_permalinks', 'on', 26, 'Enable friendly photos permalink', 'checkbox', '', '', 0);

INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`)
VALUES ('[db_prefix]', '_[db_prefix]', 'BxPhotosSearch', 'modules/boonex/photos/classes/BxPhotosSearch.php');

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) 
VALUES ('Profile photos', 0, '[db_prefix]', 0, 'active'),
('Family', 0, '[db_prefix]', 0, 'active'),
('Fun', 0, '[db_prefix]', 0, 'active'),
('My portfolio', 0, '[db_prefix]', 0, 'active'),
('Our cars', 0, '[db_prefix]', 0, 'active'),
('Other', 0, '[db_prefix]', 0, 'active');

INSERT INTO `sys_objects_categories` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) 
VALUES ('[db_prefix]', 'SELECT `Categories` FROM `[db_prefix]_main` WHERE `ID`  = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/photos/browse/category/{tag}', 'modules/?r=photos/browse/category/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`)
VALUES ('[db_prefix]', 'SELECT `Tags` FROM `[db_prefix]_main` WHERE `ID` = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/photos/browse/tag/{tag}', 'modules/?r=photos/browse/tag/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '_[db_prefix]_action_view_original', 'download', '', 'window.open(''{moduleUrl}get_image/original/{fileKey}.{fileExt}'')', '', 0),
('[db_prefix]', '{shareCpt}', 'share-square-o', '', 'showPopupAnyHtml(''{moduleUrl}share/{fileUri}'')', '', 1),
('[db_prefix]', '{evalResult}', 'exclamation-circle', '', 'showPopupAnyHtml(''{moduleUrl}report/{fileUri}'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}'', false, ''post''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{evalResult}', 'edit', '', 'oBxDolFiles.edit({ID})', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''photos'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'', false, ''post'', true); return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''photos'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]', '{featuredCpt}', 'star-o', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}feature/{ID}/{featured}'', false, ''post''); return false;', '', 7),
('[db_prefix]', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 8),
('[db_prefix]', '{cropCpt}', 'crop', '{moduleUrl}crop/{ID}', '', '', 9),
('[db_prefix]', '{SetAvatarCpt}', 'user', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}set_avatar/{ID}'', false, ''post''); return false;', '', 10),
('[db_prefix]', '{repostCpt}', 'repeat', '', '{repostScript}', '', 11),
('[db_prefix]_title', '{evalResult}', 'plus', '', 'showPopupAnyHtml(''{BaseUri}upload'');', 'return (getLoggedId() && BxDolModule::getInstance(''BxPhotosModule'')->isAllowedAdd()) ? _t(''_sys_upload'') : '''';', 20),
('[db_prefix]_title', '{evalResult}', 'picture-o', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 21),
('[db_prefix]_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxPhotosModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);


INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_bx_photos_share', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <a href="<MediaUrl>"><MediaType></a> with you!</p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Photo sharing', 0),
('t_bx_photos_report', '<SenderNickName> Reported A Photo', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<MediaUrl>">Reported <MediaType></a></p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Photo report', 0),
('t_sbs_bx_photos_comments', 'New Comments To A Photo', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<br /><p>The <a href="<ViewLink>">photo you subscribed to got new comments!</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to photo', 0);


INSERT INTO `sys_stat_member` (`Type`, `SQL`) VALUES
('phs', 'SELECT COUNT(*) FROM `[db_prefix]_main` WHERE `Owner` = ''__member_id__'' AND `Status` = ''approved''');

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('phs', '[db_prefix]', 'modules/?r=photos/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''approved''', 'modules/?r=photos/administration/home/pending', 'SELECT COUNT(*) FROM `[db_prefix]_main` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''pending'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'picture-o', @iStatSiteOrder);

INSERT INTO `sys_account_custom_stat_elements` VALUES
(NULL, '_bx_photos', '__phs__ (<a href="modules/?r=photos/albums/my/main/">__l_add__</a>)');

SET @iLevelNonMember := 1;
SET @iLevelStandard  := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('photos view');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('photos add');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES
('photos delete'), ('photos approve'), ('photos edit');

INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('photos', 'album_view', '_[db_prefix]_album_view', '3');

SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, '[db_prefix]', '_[db_prefix]', '{siteUrl}modules/?r=photos/administration', 'Photo module by BoonEx', 'picture-o', @iMax+1);

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'paperclip', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('[db_prefix]', '', '', 'return BxDolService::call(''photos'', ''get_subscription_params'', array($arg2, $arg3));'),
('[db_prefix]', 'commentPost', 't_sbs_[db_prefix]_comments', 'return BxDolService::call(''photos'', ''get_subscription_params'', array($arg2, $arg3));');


INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('[db_prefix]_profile_delete', 'BxDolService::call(''photos'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);

-- mobile

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('[db_prefix]', 'homepage', '_bx_photos', 'home_images.png', 7, '', '', '', @iMaxOrderHomepage, 1),
('[db_prefix]', 'profile', '_bx_photos', '', 7, '', 'return BxDolXMLRPCMedia::_getMediaCount(''photo'', ''{profile_id}'', ''{member_id}'');', '', @iMaxOrderProfile, 1);

-- sitemap

SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_photos', '0.8', 'auto', 'BxPhotosSiteMapsPhotos', 'modules/boonex/photos/classes/BxPhotosSiteMapsPhotos.php', @iMaxOrderSiteMaps, 1),
('[db_prefix]_albums', '_bx_photos_albums', '0.8', 'auto', 'BxPhotosSiteMapsAlbums', 'modules/boonex/photos/classes/BxPhotosSiteMapsAlbums.php', @iMaxOrderSiteMaps + 1, 1);

-- chart

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('[db_prefix]', '_bx_photos', 'bx_photos_main', 'Date', '', '', 1, @iMaxOrderCharts);

-- export

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_photos', 'BxPhotosExport', 'modules/boonex/photos/classes/BxPhotosExport.php', @iMaxOrderExports, 1);

-- member info

INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('bx_photos_thumb', '_bx_photos_member_info_profile_photo', 'thumb', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php'),
('bx_photos_thumb_2x', '_bx_photos_member_info_profile_photo_2x', 'thumb_2x', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php'),
('bx_photos_icon', '_bx_photos_member_info_profile_photo_icon', 'thumb_icon', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php'),
('bx_photos_icon_2x', '_bx_photos_member_info_profile_photo_icon_2x', 'thumb_icon_2x', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php');

UPDATE `sys_options` SET `VALUE` = 'bx_photos_thumb' WHERE `Name` = 'sys_member_info_thumb' AND `VALUE` = 'sys_avatar';
UPDATE `sys_options` SET `VALUE` = 'bx_photos_icon' WHERE `Name` = 'sys_member_info_thumb_icon' AND `VALUE` = 'sys_avatar_icon';

