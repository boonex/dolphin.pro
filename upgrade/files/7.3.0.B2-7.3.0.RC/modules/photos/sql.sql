
SET @iKatID = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'bx_photos_uploader_switcher');

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('bx_photos_activation', 'on', @iKatID, 'Enable auto-activation for photos', 'checkbox', '', '', 1, ''),
('category_auto_app_bx_photos', 'on', @iKatID, 'Autoapprove categories of photos', 'checkbox', '', '', 2, ''),
('bx_photos_allowed_exts', 'jpg jpeg png gif', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('bx_photos_profile_album_name', '{nickname}''s photos', @iKatID, 'Default profile album name', 'digit', '', '', 4, ''),
('bx_photos_profile_cover_album_name', '{nickname}''s cover photos', @iKatID, 'Default profile cover album name', 'digit', '', '', 5, ''),
('bx_photos_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('bx_photos_number_index', '9', @iKatID, 'How many photos show on main index page', 'digit', '', '', 12, ''),
('bx_photos_number_home', '12', @iKatID, 'How many photos show on photos home page', 'digit', '', '', 14, ''),
('bx_photos_number_all', '12', @iKatID, 'How many photos show on browse photos page', 'digit', '', '', 16, ''),
('bx_photos_number_top', '6', @iKatID, 'How many photos show in featured, top, and similar sections', 'digit', '', '', 18, ''),
('bx_photos_number_related', '3', @iKatID, 'Number of related photos by user', 'digit', '', '', 20, ''),
('bx_photos_number_previous_rated', '3', @iKatID, 'Number of previous rated photos', 'digit', '', '', 22, ''),
('bx_photos_number_albums_home', '3', @iKatID, 'How many albums show on photos home page', 'digit', '', '', 24, ''),
('bx_photos_number_albums_browse', '9', @iKatID, 'How many albums show on browse albums page', 'digit', '', '', 26, ''),
('bx_photos_number_albums_public_objects', '4', @iKatID, 'Minimum number of photos required to display album in Public Albums block', 'digit', '', '', 28, ''),
('bx_photos_number_view_album', '6', @iKatID, 'How many photos show on view album page', 'digit', '', '', 30, ''),
('bx_photos_file_width', '750', @iKatID, 'Width of main photo unit (in pixels)', 'digit', '', '', 34, ''),
('bx_photos_file_height', '750', @iKatID, 'Height of main photo unit (in pixels)', 'digit', '', '', 35, ''),
('bx_photos_client_width', '2048', @iKatID, 'Width for photo resizing in browser (in pixels)', 'digit', '', '', 38, ''),
('bx_photos_client_height', '2048', @iKatID, 'Height for photo resizing in browser (in pixels)', 'digit', '', '', 39, ''),
('bx_photos_flickr_photo_api', '', @iKatID, 'Flickr API key. You can get Flickr API keys here: http://www.flickr.com/services/api/keys/', 'digit', '', '', 50, ''),
('bx_photos_rss_feed_on', 'on', @iKatID, 'Enable RSS feed', 'checkbox', '', '', 52, ''),
('bx_photos_uploader_switcher', 'html5,record,embed', @iKatID, 'Available uploaders', 'list', '', '', 54, 'html5,regular,record,embed'),
('bx_photos_header_cache', '0', @iKatID, 'Header Cache time (in seconds, leave 0 to disable)', 'digit', '', '', 56, ''),
('bx_photos_cover_rows', '4', @iKatID, 'Number of rows in Photos Home page Cover', 'digit', '', '', 61, ''),
('bx_photos_cover_columns', '10', @iKatID, 'Number of columns in Photos Home page Cover', 'digit', '', '', 62, ''),
('bx_photos_cover_featured', '', @iKatID, 'Use featured photos for Photos Home page Cover', 'checkbox', '', '', 63, '');



SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_home' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_album_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_album_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_albums_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_albums_my' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_albums_owner' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_albums_owner' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_rate' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_rate' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_photos_crop' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_photos_crop' AND `Column` != 0 AND @iFirstColumn = 0;

