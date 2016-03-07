
SET @iKatID = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'bx_files_uploader_switcher');

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('bx_files_activation', 'on', @iKatID, 'Enable auto-activation for files', 'checkbox', '', '', 1, ''),
('category_auto_app_bx_files', 'on', @iKatID, 'Autoapprove categories of files', 'checkbox', '', '', 2, ''),
('bx_files_allowed_exts', '', @iKatID, 'Allowed extensions (leave blank to have all types)', 'digit', '', '', 3, ''),
('bx_files_max_file_size', '32', @iKatID, 'Maximum size of one file (in Megabytes)', 'digit', '', '', 4, ''),
('bx_files_profile_album_name', '{nickname}''s files', @iKatID, 'Default profile folder name', 'digit', '', '', 5, ''),
('bx_files_mode_index', 'last', @iKatID, 'Default sort on main index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,popular'),
('bx_files_number_index', '2', @iKatID, 'How many files show on main index page', 'digit', '', '', 12, ''),
('bx_files_number_home', '10', @iKatID, 'How many files show on files home page', 'digit', '', '', 14, ''),
('bx_files_number_all', '10', @iKatID, 'How many files show on browse files page', 'digit', '', '', 16, ''),
('bx_files_number_featured', '4', @iKatID, 'How many files show in featured section', 'digit', '', '', 18, ''),
('bx_files_number_top', '4', @iKatID, 'How many files show in top section', 'digit', '', '', 19, ''),
('bx_files_number_related', '4', @iKatID, 'Number of related files by user', 'digit', '', '', 20, ''),
('bx_files_number_albums_home', '4', @iKatID, 'How many folders show on files home page', 'digit', '', '', 22, ''),
('bx_files_number_albums_browse', '10', @iKatID, 'How many folders show on browse folders page', 'digit', '', '', 24, ''),
('bx_files_number_view_album', '4', @iKatID, 'How many files show on view folder page', 'digit', '', '', 26, ''),
('bx_files_thumb_width', '64', @iKatID, 'Thumbnail width of file', 'digit', '', '', 30, ''),
('bx_files_uploader_switcher', 'html5', @iKatID, 'Available uploaders', 'list', '', '', 40, 'html5,regular');



SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_files_album_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_files_album_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_files_view' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_files_view' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_files_home' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_files_home' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_files_albums_my' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_files_albums_my' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_files_albums_owner' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_files_albums_owner' AND `Column` != 0 AND @iFirstColumn = 0;



DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'files' AND  `name` = 'album_view';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('files', 'album_view', '_bx_files_album_view', '3');

