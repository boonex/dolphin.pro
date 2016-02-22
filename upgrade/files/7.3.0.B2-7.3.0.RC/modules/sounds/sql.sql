
SET @iKatID = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'bx_sounds_uploader_switcher');

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('category_auto_app_bx_sounds', 'on', @iKatID, 'Autoapprove categories of sounds', 'checkbox', '', '', 2, ''),
('bx_sounds_allowed_exts', 'mp3 wav', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('bx_sounds_max_file_size', '32', @iKatID, 'Maximum size of one file (in Megabytes)', 'digit', '', '', 4, ''),
('bx_sounds_profile_album_name', '{nickname}''s sounds', @iKatID, 'Default profile album name', 'digit', '', '', 5, ''),
('bx_sounds_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('bx_sounds_number_index', '9', @iKatID, 'How many sounds show on main index page', 'digit', '', '', 12, ''),
('bx_sounds_number_home', '12', @iKatID, 'How many sounds show on sounds home page', 'digit', '', '', 14, ''),
('bx_sounds_number_all', '12', @iKatID, 'How many sounds show on browse sounds page', 'digit', '', '', 16, ''),
('bx_sounds_number_top', '3', @iKatID, 'How many sounds show in featured, top, and similar sections', 'digit', '', '', 18, ''),
('bx_sounds_number_related', '3', @iKatID, 'Number of related sounds by user', 'digit', '', '', 20, ''),
('bx_sounds_number_previous_rated', '3', @iKatID, 'Number of previous rated sounds', 'digit', '', '', 22, ''),
('bx_sounds_number_albums_home', '3', @iKatID, 'How many albums show on sounds home page', 'digit', '', '', 24, ''),
('bx_sounds_number_albums_browse', '9', @iKatID, 'How many albums show on browse albums page', 'digit', '', '', 26, ''),
('bx_sounds_number_view_album', '3', @iKatID, 'How many sounds show on view album page', 'digit', '', '', 28, ''),
('bx_sounds_file_width', '600', @iKatID, 'Width of sound player (in pixels)', 'digit', '', '', 34, ''),
('bx_sounds_file_height', '600', @iKatID, 'Height of sound player (in pixels)', 'digit', '', '', 35, ''),
('bx_sounds_uploader_switcher', 'html5,record', @iKatID, 'Available uploaders', 'list', '', '', 38, 'html5,regular,record');



SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_album_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_album_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_home' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_albums_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_albums_my' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_albums_owner' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_albums_owner' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_rate' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_sounds_rate' AND `Column` != 0 AND @iFirstColumn = 0;

