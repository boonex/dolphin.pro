
SET @iKatID = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'bx_videos_uploader_switcher');

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('category_auto_app_bx_videos', 'on', @iKatID, 'Autoapprove categories of videos', 'checkbox', '', '', 2, ''),
('bx_videos_allowed_exts', 'avi flv mpg wmv mp4 m4v mov divx xvid mpeg 3gp webm', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('bx_videos_max_file_size', '64', @iKatID, 'Maximum size of one file (in Megabytes)', 'digit', '', '', 4, ''),
('bx_videos_profile_album_name', '{nickname}''s videos', @iKatID, 'Default profile album name', 'digit', '', '', 5, ''),
('bx_videos_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('bx_videos_number_index', '9', @iKatID, 'How many videos show on main index page', 'digit', '', '', 12, ''),
('bx_videos_number_home', '12', @iKatID, 'How many videos show on videos home page', 'digit', '', '', 14, ''),
('bx_videos_number_all', '12', @iKatID, 'How many videos show on browse videos page', 'digit', '', '', 16, ''),
('bx_videos_number_top', '3', @iKatID, 'How many videos show in featured, top, and similar sections', 'digit', '', '', 18, ''),
('bx_videos_number_related', '3', @iKatID, 'Number of related videos by user', 'digit', '', '', 20, ''),
('bx_videos_number_previous_rated', '3', @iKatID, 'Number of previous rated videos', 'digit', '', '', 22, ''),
('bx_videos_number_albums_home', '3', @iKatID, 'How many albums show on videos home page', 'digit', '', '', 24, ''),
('bx_videos_number_albums_browse', '9', @iKatID, 'How many albums show on browse albums page', 'digit', '', '', 26, ''),
('bx_videos_number_view_album', '3', @iKatID, 'How many videos show on view album page', 'digit', '', '', 28, ''),
('bx_videos_file_width', '600', @iKatID, 'Width of video player (in pixels)', 'digit', '', '', 34, ''),
('bx_videos_file_height', '600', @iKatID, 'Height of video player (in pixels)', 'digit', '', '', 35, ''),
('bx_videos_uploader_switcher', 'html5,record,embed', @iKatID, 'Available uploaders', 'list', '', '', 38, 'html5,regular,record,embed');



SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_album_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_album_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_home' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_albums_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_albums_my' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_albums_owner' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_albums_owner' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_videos_rate' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_videos_rate' AND `Column` != 0 AND @iFirstColumn = 0;

