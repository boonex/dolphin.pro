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
  `DownloadsCount` int(11) NOT NULL default '0',
  `AllowDownload` int(11) NOT NULL default '3',
  `Featured` tinyint(4) NOT NULL default '0',
  `Status` enum('approved','disapproved','pending') NOT NULL default 'pending',
  `Type` varchar(30) NOT NULL default 'text/plain',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Uri` (`Uri`),
  KEY `Owner` (`Owner`)
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

-- Comments Table
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

-- File types
CREATE TABLE `[db_prefix]_types` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Type` varchar(64) NOT NULL,
  `Icon` varchar(20) NOT NULL default 'default.png',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Type` (`Type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `[db_prefix]_types` (`Icon`, `Type`) VALUES
('default.png', 'text/plain'),
('006.png', 'application/octet-stream'),
('007.png', 'image/x-photoshop'),
('009.png', 'image/x-eps'),
('010.png', 'application/cdr'),
('011.png', 'image/tiff'),
('012.png', 'image/jpeg'),
('013.png', 'image/x-png'),
('014.png', 'image/x-ms-bmp'),
('015.png', 'image/gif'),
('016.png', 'image/x-tga'),
('019.png', 'image/ico'),
('023.png', 'application/x-shockwave-flash'),
('025.png', 'video/quicktime'),
('026.png', 'video/x-msvideo'),
('027.png', 'video/mpeg'),
('028.png', 'video/x-matroska'),
('030.png', 'audio/x-ms-wmv'),
('032.png', 'application/x-pn-realaudio-plugin'),
('033.png', 'application/ogg'),
('034.png', 'audio/x-wav'),
('036.png', 'audio/x-ms-wma'),
('037.png', 'audio/mpeg'),
('038.png', 'application/x-ogg'),
('039.png', 'audio/x-flac'),
('040.png', 'x-music/x-midi'),
('041.png', 'audio/x-real'),
('042.png', 'application/pdf'),
('043.png', 'application/msword'),
('044.png', 'application/vnd.ms-powerpoint'),
('045.png', 'application/vnd.ms-xls'),
('046.png', 'application/xml'),
('047.png', 'text/html'),
('048.png', 'text/css'),
('050.png', 'application/rtf'),
('051.png', 'application/x-zip-compressed'),
('052.png', 'application/x-rar-compressed'),
('056.png', 'application/mshelp');

INSERT INTO `sys_options_cats` SET `name` = 'Files';
SET @iKatID = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('[db_prefix]_activation', 'on', @iKatID, 'Enable auto-activation for files', 'checkbox', '', '', 1, ''),
('category_auto_app_[db_prefix]', 'on', @iKatID, 'Autoapprove categories of files', 'checkbox', '', '', 2, ''),
('[db_prefix]_allowed_exts', '', @iKatID, 'Allowed extensions (leave blank to have all types)', 'digit', '', '', 3, ''),
('[db_prefix]_max_file_size', '32', @iKatID, 'Maximum size of one file (in Megabytes)', 'digit', '', '', 4, ''),
('[db_prefix]_profile_album_name', '{nickname}''s files', @iKatID, 'Default profile folder name', 'digit', '', '', 5, ''),
('[db_prefix]_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,popular'),
('[db_prefix]_number_index', '2', @iKatID, 'How many files show on main index page', 'digit', '', '', 12, ''),
('[db_prefix]_number_home', '10', @iKatID, 'How many files show on files home page', 'digit', '', '', 14, ''),
('[db_prefix]_number_all', '10', @iKatID, 'How many files show on browse files page', 'digit', '', '', 16, ''),
('[db_prefix]_number_featured', '4', @iKatID, 'How many files show in featured section', 'digit', '', '', 18, ''),
('[db_prefix]_number_top', '4', @iKatID, 'How many files show in top section', 'digit', '', '', 19, ''),
('[db_prefix]_number_related', '4', @iKatID, 'Number of related files by user', 'digit', '', '', 20, ''),
('[db_prefix]_number_albums_home', '4', @iKatID, 'How many folders show on files home page', 'digit', '', '', 22, ''),
('[db_prefix]_number_albums_browse', '10', @iKatID, 'How many folders show on browse folders page', 'digit', '', '', 24, ''),
('[db_prefix]_number_view_album', '4', @iKatID, 'How many files show on view folder page', 'digit', '', '', 26, ''),
('[db_prefix]_thumb_width', '64', @iKatID, 'Thumbnail width of file', 'digit', '', '', 30, ''),
('[db_prefix]_uploader_switcher', 'html5', @iKatID, 'Available uploaders', 'list', '', '', 40, 'html5,regular');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('[db_prefix]_view', 'Files View File', @iPCPOrder+1),
('[db_prefix]_home', 'Files Home', @iPCPOrder+2),
('[db_prefix]_album_view', 'Files View Folder', @iPCPOrder+3),
('[db_prefix]_albums_owner', 'Files Profile Folders', @iPCPOrder+4);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_album_view', '1140px', '', '', 1, 0, 0, 71.9, 0, 'non,memb', '', 'Objects'),
('[db_prefix]_album_view', '1140px', '', '', 1, 1, 1, 71.9, 0, 'non,memb', '_[db_prefix]_comments', 'Comments'),
('[db_prefix]_album_view', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_author', 'Author'),
('[db_prefix]_album_view', '1140px', '', '', 2, 1, 1, 28.1, 0, 'non,memb', '_[db_prefix]_info_album', 'Info'),
('[db_prefix]_album_view', '1140px', '', '', 2, 2, 1, 28.1, 0, 'memb', '_[db_prefix]_actions', 'Actions');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '1140px', '', '_[db_prefix]_view', 1, 0, 'ViewFile', '', 1, 71.9, 'non,memb', 380),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_comments', 1, 1, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info', 2, 0, 'FileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info_main', 2, 1, 'MainFileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_actions', 2, 2, 'ActionList', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_sys_block_title_social_sharing', 2, 3, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_albums_latest', 2, 4, 'LastAlbums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_related', 2, 5, 'RelatedFiles', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '1140px', '', '_[db_prefix]_featured', 1, 0, 'Featured', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_public', 1, 1, 'All', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_Tags', 2, 0, 'Tags', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_albums', 2, 1, 'Albums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_top', 0, 0, 'Top', '', 1, 28.1, 'non,memb', 0);

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
('[db_prefix]_albums_my', '1140px', '', '_[db_prefix]_albums_my', 1, 30, 'my', '', 1, 100, 'memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_albums_owner', 1, 1, 'Browse', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_albums_owner', '1140px', '', '_[db_prefix]_favorited', 2, 1, 'Favorited', '', 1, 28.1, 'non,memb', 0);


INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Public Files', '_[db_prefix]_public', 0, 0, 'PHP', '$aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0) \r\n$aVisible[] = BX_DOL_PG_MEMBERS;\r\n return BxDolService::call(''files'', ''get_files_block'', array(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting''=>getParam(''[db_prefix]_mode_index''), ''per_page''=>getParam(''[db_prefix]_number_index''))), ''Search'');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'File Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''files'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES 
('[db_prefix]', '[db_prefix]_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', '[db_prefix]_main', 'ID', 'CommentsCount', 'BxFilesCmts', 'modules/boonex/files/classes/BxFilesCmts.php'),
('[db_prefix]_albums', '[db_prefix]_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxFilesCmtsAlbums', 'modules/boonex/files/classes/BxFilesCmtsAlbums.php');

INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`) 
VALUES ('[db_prefix]', '[db_prefix]_rating', '[db_prefix]_voting_track', 'gal_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, '', '', '[db_prefix]_main', 'Rate', 'RateCount', 'ID');

INSERT INTO `sys_objects_views` (`name`, `table_track`, `period`, `trigger_table`, `trigger_field_id`, `trigger_field_views`, `is_on`)
VALUES ('[db_prefix]', '[db_prefix]_views_track', 86400, '[db_prefix]_main', 'ID', 'Views', 1);

SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(0, 'Files', '_[db_prefix]_top_menu_item', 'modules/?r=files/home/|modules/?r=files/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'save', 'save', 1, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'FilesHome', '_[db_prefix]_top_menu_home', 'modules/?r=files/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesAlbums', '_[db_prefix]_top_menu_albums', 'modules/?r=files/albums/browse/all', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesAll', '_[db_prefix]_top_menu_all', 'modules/?r=files/browse/all', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesTop', '_[db_prefix]_top_menu_top', 'modules/?r=files/browse/top', 15, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesPopular', '_[db_prefix]_top_menu_popular', 'modules/?r=files/browse/popular', 20, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesFeatured', '_[db_prefix]_top_menu_featured', 'modules/?r=files/browse/featured', 25, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesTags', '_[db_prefix]_top_menu_tags', 'modules/?r=files/tags', 30, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesCategories', '_[db_prefix]_top_menu_categories', 'modules/?r=files/categories', 35, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesCalendar', '_[db_prefix]_top_menu_calendar', 'modules/?r=files/calendar|modules/?r=files/browse/calendar/', 40, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'FilesSearch', '_[db_prefix]_top_menu_search', 'searchKeyword.php?type=bx_files', 45, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(9, 'Files', '_[db_prefix]_menu_profile', 'modules/?r=files/albums/browse/owner/{profileUsername}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(4, 'Files', '_[db_prefix]_menu_profile', 'modules/?r=files/albums/my/main/|modules/?r=files/albums/my/add/|modules/?r=files/albums/my/manage/|modules/?r=files/albums/my/disapproved/', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'FilesUnit', '_[db_prefix]_top_menu_item', 'modules/?r=files/view/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'save', 0, ''),
(0, 'FilesAlbum',  '_[db_prefix]_top_menu_item', 'modules/?r=files/browse/album/|modules/?r=files/albums/my/edit/|modules/?r=files/albums/my/organize/|modules/?r=files/albums/my/add_objects/|modules/?r=files/albums/my/manage_objects', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'save', 0, '');


SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_files', `Eval` = 'return BxDolService::call(''files'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=files/', 'm/files/', '[db_prefix]_permalinks');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`)
VALUES ('[db_prefix]_permalinks', 'on', 26, 'Enable friendly files permalink', 'checkbox', '', '', 0);

INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`)
VALUES ('[db_prefix]', '_[db_prefix]', 'BxFilesSearch', 'modules/boonex/files/classes/BxFilesSearch.php');

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) 
VALUES ('Other', '0', '[db_prefix]', '0', 'active');

INSERT INTO `sys_objects_categories` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) 
VALUES ('[db_prefix]', 'SELECT `Categories` FROM `[db_prefix]_main` WHERE `ID`  = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/files/browse/category/{tag}', 'modules/?r=files/files/category/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`)
VALUES ('[db_prefix]', 'SELECT `Tags` FROM `[db_prefix]_main` WHERE `ID` = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/files/browse/tag/{tag}', 'modules/?r=files/browse/tag/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '{shareCpt}', 'share-square-o', '', 'showPopupAnyHtml(''{moduleUrl}share/{fileUri}'')', '', 1),
('[db_prefix]', '{evalResult}', 'exclamation-circle', '', 'showPopupAnyHtml(''{moduleUrl}report/{fileUri}'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}'', false, ''post''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{downloadCpt}', 'download', '{moduleUrl}get_file/{ID}{extension}', '', '', 4),
('[db_prefix]', '{evalResult}', 'edit', '', 'oBxDolFiles.edit({ID})', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''files'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'', false, ''post'', true); return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''files'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]', '{featuredCpt}', 'star-o', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}feature/{ID}/{featured}'', false, ''post''); return false;', '', 7),
('[db_prefix]', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 8),
('[db_prefix]', '{repostCpt}', 'repeat', '', '{repostScript}', '', 9),
('[db_prefix]_title', '{evalResult}', 'plus', '', 'showPopupAnyHtml(''{BaseUri}upload'');', 'return getLoggedId() ? _t(''_sys_upload'') : '''';', 20),
('[db_prefix]_title', '{evalResult}', 'save', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 21),
('[db_prefix]_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxFilesModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);


INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_bx_files_share', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <a href="<MediaUrl>"><MediaType></a> with you!</p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'File sharing', 0),
('t_bx_files_report', '<SenderNickName> Reported A File', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<MediaUrl>">Reported <MediaType></a></p>\r\n\r\n<hr>\r\n<UserExplanation>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'File report', 0),
('t_sbs_bx_files_comments', 'New Comments To A File', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">file you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to file', 0);


INSERT INTO `sys_stat_member` (`Type`, `SQL`) VALUES
('shf', 'SELECT COUNT(*) FROM `[db_prefix]_main` WHERE `Owner` = ''__member_id__'' AND `Status` = ''approved''');

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('shf', '[db_prefix]', 'modules/?r=files/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''approved''','modules/?r=files/administration/home/pending', 'SELECT COUNT(*) FROM `[db_prefix]_main` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''pending'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'save', @iStatSiteOrder);

INSERT INTO `sys_account_custom_stat_elements` VALUES
(NULL, '_bx_files', '__shf__ (<a href="modules/?r=files/albums/my/main/">__l_add__</a>)');

SET @iLevelNonMember := 1;
SET @iLevelStandard  := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('files view');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('files add');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);
	
INSERT INTO `sys_acl_actions` (`Name`) VALUES ('files download');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES
('files delete'), ('files approve'), ('files edit');

INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('files', 'album_view', '_[db_prefix]_album_view', '3'),
('files', 'download', '_[db_prefix]_download', '3');

SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, '[db_prefix]', '_[db_prefix]', '{siteUrl}modules/?r=files/administration', 'Files module by BoonEx', 'save', @iMax+1);

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'paperclip', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('[db_prefix]', '', '', 'return BxDolService::call(''files'', ''get_subscription_params'', array($arg2, $arg3));'),
('[db_prefix]', 'commentPost', 't_sbs_[db_prefix]_comments', 'return BxDolService::call(''files'', ''get_subscription_params'', array($arg2, $arg3));');

INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('[db_prefix]_profile_delete', 'BxDolService::call(''files'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);

-- sitemap

SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_files', '0.8', 'auto', 'BxFilesSiteMapsFiles', 'modules/boonex/files/classes/BxFilesSiteMapsFiles.php', @iMaxOrderSiteMaps, 1),
('[db_prefix]_folders', '_bx_files_albums', '0.8', 'auto', 'BxFilesSiteMapsFolders', 'modules/boonex/files/classes/BxFilesSiteMapsFolders.php', @iMaxOrderSiteMaps + 1, 1);

-- chart

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('[db_prefix]', '_bx_files', 'bx_files_main', 'Date', '', '', 1, @iMaxOrderCharts);

-- export

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_files', 'BxFilesExport', 'modules/boonex/files/classes/BxFilesExport.php', @iMaxOrderExports, 1);
