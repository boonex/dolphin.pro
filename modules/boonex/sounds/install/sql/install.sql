-- Favorites
CREATE TABLE `[db_prefix]_favorites` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `Date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`,`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

INSERT INTO `sys_options_cats` SET `name` = 'Sounds';
SET @iKatID = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('category_auto_app_[db_prefix]', 'on', @iKatID, 'Autoapprove categories of sounds', 'checkbox', '', '', 2, ''),
('[db_prefix]_allowed_exts', 'mp3 wav', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('[db_prefix]_max_file_size', '32', @iKatID, 'Maximum size of one file (in Megabytes)', 'digit', '', '', 4, ''),
('[db_prefix]_profile_album_name', '{nickname}''s sounds', @iKatID, 'Default profile album name', 'digit', '', '', 5, ''),
('[db_prefix]_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('[db_prefix]_number_index', '9', @iKatID, 'How many sounds show on main index page', 'digit', '', '', 12, ''),
('[db_prefix]_number_home', '12', @iKatID, 'How many sounds show on sounds home page', 'digit', '', '', 14, ''),
('[db_prefix]_number_all', '12', @iKatID, 'How many sounds show on browse sounds page', 'digit', '', '', 16, ''),
('[db_prefix]_number_top', '3', @iKatID, 'How many sounds show in featured, top, and similar sections', 'digit', '', '', 18, ''),
('[db_prefix]_number_related', '3', @iKatID, 'Number of related sounds by user', 'digit', '', '', 20, ''),
('[db_prefix]_number_previous_rated', '3', @iKatID, 'Number of previous rated sounds', 'digit', '', '', 22, ''),
('[db_prefix]_number_albums_home', '3', @iKatID, 'How many albums show on sounds home page', 'digit', '', '', 24, ''),
('[db_prefix]_number_albums_browse', '9', @iKatID, 'How many albums show on browse albums page', 'digit', '', '', 26, ''),
('[db_prefix]_number_view_album', '3', @iKatID, 'How many sounds show on view album page', 'digit', '', '', 28, ''),
('[db_prefix]_file_width', '600', @iKatID, 'Width of sound player (in pixels)', 'digit', '', '', 34, ''),
('[db_prefix]_file_height', '600', @iKatID, 'Height of sound player (in pixels)', 'digit', '', '', 35, ''),
('[db_prefix]_uploader_switcher', 'html5,record', @iKatID, 'Available uploaders', 'list', '', '', 38, 'html5,regular,record');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('[db_prefix]_view', 'Sounds Listen Sound', @iPCPOrder+1),
('[db_prefix]_home', 'Sounds Home', @iPCPOrder+2),
('[db_prefix]_rate', 'Sounds Rate', @iPCPOrder+3),
('[db_prefix]_album_view', 'Sounds View Album', @iPCPOrder+4),
('[db_prefix]_albums_owner', 'Sounds Profile Albums', @iPCPOrder+5);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('[db_prefix]_album_view', '1140px', '', '', 1, 0, 0, 71.9, 0, 'non,memb', '', 'Objects'),
('[db_prefix]_album_view', '1140px', '', '', 1, 1, 1, 71.9, 0, 'non,memb', '_[db_prefix]_comments', 'Comments'),
('[db_prefix]_album_view', '1140px', '', '', 2, 0, 1, 28.1, 0, 'non,memb', '_[db_prefix]_author', 'Author'),
('[db_prefix]_album_view', '1140px', '', '', 2, 1, 1, 28.1, 0, 'non,memb', '_[db_prefix]_info_album', 'Info'),
('[db_prefix]_album_view', '1140px', '', '', 2, 2, 1, 28.1, 0, 'memb', '_[db_prefix]_actions', 'Actions');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '1140px', '', '_[db_prefix]_view', 1, 1, 'ViewFile', '', 1, 71.9, 'non,memb', 380),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_author', 2, 1, 'FileAuthor', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_info_main', 2, 2, 'MainFileInfo', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_actions', 2, 3, 'ActionList', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_album_sounds_rest', 2, 5, 'ViewAlbum', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_view', '1140px', '', '_[db_prefix]_related', 0, 0, 'RelatedFiles', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '1140px', '', '_[db_prefix]_albums', 2, 1, 'Albums', '', 1, 28.1, 'non,memb', 0),
('[db_prefix]_home', '1140px', '', '_[db_prefix]_special', 1, 1, 'Special', '', 1, 71.9, 'non,memb', 0),
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
('index', '1140px', 'Public Sounds', '_[db_prefix]_public', 0, 0, 'PHP', 'require_once(BX_DIRECTORY_PATH_MODULES . ''boonex/sounds/classes/BxSoundsSearch.php'');\r\n $oMedia = new BxSoundsSearch();\r\n $aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0)\r\n $aVisible[] = BX_DOL_PG_MEMBERS;\r\n $aCode = $oMedia->getBrowseBlock(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting'' => getParam(''[db_prefix]_mode_index''), ''per_page''=>(int)getParam(''[db_prefix]_number_index'')));\r\n return array($aCode[''code''], $aCode[''menu_top''], $aCode[''menu_bottom''], $aCode[''wrapper'']);', 1, 71.9, 'non,memb', 0),
('member', '1140px', 'Sound Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''sounds'', ''get_profile_albums_block'', array($this->iMember, BX_DOL_URL_ROOT . ''member.php''), ''Search'');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Sound Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''sounds'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 28.1, 'non,memb', 0),
('profile', '1140px', 'Profile Sounds', '_[db_prefix]_profile_sounds', 0, 0, 'PHP', 'return BxDolService::call(''sounds'', ''profile_sound_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 71.9, 'non,memb', 0);


SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_sounds', `Eval` = 'return BxDolService::call(''sounds'', ''get_member_menu_item_add_content'');', `Position`='top_extra', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('[db_prefix]', '[db_prefix]_cmts', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', 'RayMp3Files', 'ID', 'CommentsCount', 'BxSoundsCmts', 'modules/boonex/sounds/classes/BxSoundsCmts.php'),
('[db_prefix]_albums', '[db_prefix]_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxSoundsCmtsAlbums', 'modules/boonex/sounds/classes/BxSoundsCmtsAlbums.php');

INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`) 
VALUES ('[db_prefix]', '[db_prefix]_rating', '[db_prefix]_voting_track', 'gal_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, 'BxSoundsRate', 'modules/boonex/sounds/classes/BxSoundsRate.php', 'RayMp3Files', 'Rate', 'RateCount', 'ID');

INSERT INTO `sys_objects_views` (`name`, `table_track`, `period`, `trigger_table`, `trigger_field_id`, `trigger_field_views`, `is_on`)
VALUES ('[db_prefix]', '[db_prefix]_views_track', 86400, 'RayMp3Files', 'ID', 'Listens', 1);

SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(0, 'Sounds', '_[db_prefix]_top_menu_item', 'modules/?r=sounds/home/|modules/?r=sounds/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'music', 'music', 1, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'SoundsHome', '_[db_prefix]_top_menu_home', 'modules/?r=sounds/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsAlbums', '_[db_prefix]_top_menu_albums', 'modules/?r=sounds/albums/browse/all', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsAll', '_[db_prefix]_top_menu_all', 'modules/?r=sounds/browse/all', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsTop', '_[db_prefix]_top_menu_top', 'modules/?r=sounds/browse/top', 15, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsPopular', '_[db_prefix]_top_menu_popular', 'modules/?r=sounds/browse/popular', 20, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsFeatured', '_[db_prefix]_top_menu_featured', 'modules/?r=sounds/browse/featured', 25, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsTags', '_[db_prefix]_top_menu_tags', 'modules/?r=sounds/tags', 34, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsCategories', '_[db_prefix]_top_menu_categories', 'modules/?r=sounds/categories', 35, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsRate', '_[db_prefix]_top_menu_rate', 'modules/?r=sounds/rate', 36, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsCalendar', '_[db_prefix]_top_menu_calendar', 'modules/?r=sounds/calendar|modules/?r=sounds/browse/calendar/', 40, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'SoundsSearch', '_[db_prefix]_top_menu_search', 'searchKeyword.php?type=bx_sounds', 45, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(9, 'Sounds', '_[db_prefix]_menu_profile', 'modules/?r=sounds/albums/browse/owner/{profileUsername}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(4, 'Sounds', '_[db_prefix]_menu_profile', 'modules/?r=sounds/albums/my/main/|modules/?r=sounds/albums/my/add/|modules/?r=sounds/albums/my/manage/|modules/?r=sounds/albums/my/disapproved/', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'SoundsUnit', '_[db_prefix]_top_menu_item', 'modules/?r=sounds/view/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'music', 0, ''),
(0, 'SoundsAlbum',  '_[db_prefix]_top_menu_item', 'modules/?r=sounds/browse/album/|modules/?r=sounds/albums/my/edit/|modules/?r=sounds/albums/my/organize/|modules/?r=sounds/albums/my/add_objects/|modules/?r=sounds/albums/my/manage_objects', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'music', 0, '');

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=sounds/', 'm/sounds/', '[db_prefix]_permalinks');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`)
VALUES ('[db_prefix]_permalinks', 'on', 26, 'Enable friendly sounds permalink', 'checkbox', '', '', 0);

INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`)
VALUES ('[db_prefix]', '_[db_prefix]', 'BxSoundsSearch', 'modules/boonex/sounds/classes/BxSoundsSearch.php');

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES
('Profile music', 0, 'bx_sounds', 0, 'active'),
('Classical', 0, 'bx_sounds', 0, 'active'),
('POP', 0, 'bx_sounds', 0, 'active'),
('I am fond of this...', 0, 'bx_sounds', 0, 'active'),
('Good meaning', 0, 'bx_sounds', 0, 'active'),
('Christmas songs', 0, 'bx_sounds', 0, 'active');

INSERT INTO `sys_objects_categories` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) 
VALUES ('[db_prefix]', 'SELECT `Categories` FROM `RayMp3Files` WHERE `ID`  = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/sounds/browse/category/{tag}', 'modules/?r=sounds/browse/category/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`)
VALUES ('[db_prefix]', 'SELECT `Tags` FROM `RayMp3Files` WHERE `ID` = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/sounds/browse/tag/{tag}', 'modules/?r=sounds/browse/tag/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '{shareCpt}', 'share-square-o', '', 'showPopupAnyHtml(''{moduleUrl}share/{fileUri}'')', '', 1),
('[db_prefix]', '{evalResult}', 'exclamation-circle', '', 'showPopupAnyHtml(''{moduleUrl}report/{fileUri}'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}'', false, ''post''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{evalResult}', 'edit', '', 'oBxDolFiles.edit({ID})', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''sounds'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'', false, ''post'', true); return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''sounds'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]', '{featuredCpt}', 'star-o', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}feature/{ID}/{featured}'', false, ''post''); return false;', '', 7),
('[db_prefix]', '{downloadCpt}', 'download', '{moduleUrl}get_file/{ID}', '', '', 8),
('[db_prefix]', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 9),
('[db_prefix]', '{repostCpt}', 'repeat', '', '{repostScript}', '', 10),
('[db_prefix]_title', '{evalResult}', 'plus', '', 'showPopupAnyHtml(''{BaseUri}upload'');', 'return (getLoggedId() && BxDolModule::getInstance(''BxSoundsModule'')->isAllowedAdd()) ? _t(''_sys_upload'') : '''';', 1),
('[db_prefix]_title', '{evalResult}', 'music', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 2),
('[db_prefix]_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxSoundsModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);


INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_bx_sounds_share', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <a href="<MediaUrl>"><MediaType></a> with you!</p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Sounds sharing', 0),
('t_bx_sounds_report', '<SenderNickName> Reported A Sound', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<MediaUrl>">Reported <MediaType></a></p>\r\n\r\n<hr>\r\n\r\n<UserExplanation>\r\n\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Sound report', 0),
('t_sbs_bx_sounds_comments', 'New Comments To A Sound', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<br /><p>The <a href="<ViewLink>">sound you subscribed to got new comments!</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to sound', 0);


INSERT INTO `sys_stat_member` (`Type`, `SQL`) VALUES
('pmu', 'SELECT COUNT(*) FROM `RayMp3Files` WHERE `Owner` = ''__member_id__'' AND `Status` = ''approved''');

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('pmu', '[db_prefix]', 'modules/?r=sounds/browse/all', 'SELECT COUNT(`ID`) FROM `RayMp3Files` WHERE `Status`=''approved''', 'modules/?r=sounds/administration/home/disapproved', 'SELECT COUNT(*) FROM `RayMp3Files` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''disapproved'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'music', @iStatSiteOrder);

INSERT INTO `sys_account_custom_stat_elements` VALUES
(NULL, '_bx_sounds', '__pmu__ (<a href="modules/?r=sounds/albums/my/main/">__l_add__</a>)');

SET @iLevelNonMember := 1;
SET @iLevelStandard  := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('sounds view');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('sounds add');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES
('sounds delete'), ('sounds approve'), ('sounds edit');

INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('sounds', 'album_view', '_[db_prefix]_album_view', '3');

SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, '[db_prefix]', '_[db_prefix]', '{siteUrl}modules/?r=sounds/administration', 'Sounds module by BoonEx', 'music', @iMax+1);

INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
('[db_prefix]', '*/5', 'BxDolCronMp3', 'flash/modules/mp3/inc/classes/BxDolCronMp3.php', '');

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'paperclip', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('[db_prefix]', '', '', 'return BxDolService::call(''sounds'', ''get_subscription_params'', array($arg2, $arg3));'),
('[db_prefix]', 'commentPost', 't_sbs_[db_prefix]_comments', 'return BxDolService::call(''sounds'', ''get_subscription_params'', array($arg2, $arg3));');

INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('[db_prefix]_profile_delete', 'BxDolService::call(''sounds'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);

-- mobile

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('[db_prefix]', 'homepage', '_bx_sounds', 'home_sounds.png', 9, '', '', '', @iMaxOrderHomepage, 1),
('[db_prefix]', 'profile', '_bx_sounds', '', 9, '', 'return BxDolXMLRPCMedia::_getMediaCount(''music'', ''{profile_id}'', ''{member_id}'');', '', @iMaxOrderProfile, 1);

-- sitemap

SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_sounds', '0.8', 'auto', 'BxSoundsSiteMapsSounds', 'modules/boonex/sounds/classes/BxSoundsSiteMapsSounds.php', @iMaxOrderSiteMaps, 1),
('[db_prefix]_albums', '_bx_sounds_albums', '0.8', 'auto', 'BxSoundsSiteMapsAlbums', 'modules/boonex/sounds/classes/BxSoundsSiteMapsAlbums.php', @iMaxOrderSiteMaps + 1, 1);

-- chart

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('[db_prefix]', '_bx_sounds', 'RayMp3Files', 'Date', '', '', 1, @iMaxOrderCharts);

-- export

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('[db_prefix]', '_bx_sounds', 'BxSoundsExport', 'modules/boonex/sounds/classes/BxSoundsExport.php', @iMaxOrderExports, 1);
