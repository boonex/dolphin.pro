

DELETE FROM `sys_options` WHERE `Name` IN('bx_photos_profile_cover_album_name', 'bx_photos_icon_width', 'bx_photos_icon_height', 'bx_photos_thumb_width', 'bx_photos_thumb_height', 'bx_photos_browse_width', 'bx_photos_browse_height', 'bx_photos_client_width', 'bx_photos_client_height', 'bx_photos_cover_rows', 'bx_photos_cover_columns', 'bx_photos_cover_featured');

SET @iKatID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Photos');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('bx_photos_profile_cover_album_name', '{nickname}''s cover photos', @iKatID, 'Default profile cover album name', 'digit', '', '', 5, ''),
('bx_photos_client_width', '2048', @iKatID, 'Width for photo resizing in browser (in pixels)', 'digit', '', '', 38, ''),
('bx_photos_client_height', '2048', @iKatID, 'Height for photo resizing in browser (in pixels)', 'digit', '', '', 39, ''),
('bx_photos_cover_rows', '4', @iKatID, 'Number of rows in Photos Home page Cover', 'digit', '', '', 61, ''),
('bx_photos_cover_columns', '10', @iKatID, 'Number of columns in Photos Home page Cover', 'digit', '', '', 62, ''),
('bx_photos_cover_featured', '', @iKatID, 'Use featured photos for Photos Home page Cover', 'checkbox', '', '', 63, '');

UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_photos_number_index';
UPDATE `sys_options` SET `VALUE` = '6' WHERE `VALUE` = '5' AND `Name` = 'bx_photos_number_top';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_photos_number_user';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_photos_number_related';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_photos_number_previous_rated';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_photos_number_browse';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_photos_number_albums_browse';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_photos_number_albums_home';
UPDATE `sys_options` SET `order_in_kateg` = '50' WHERE `order_in_kateg` = '38' AND `Name` = 'bx_photos_flickr_photo_api';
UPDATE `sys_options` SET `order_in_kateg` = '52' WHERE `order_in_kateg` = '39' AND `Name` = 'bx_photos_rss_feed_on';
UPDATE `sys_options` SET `order_in_kateg` = '54' WHERE `order_in_kateg` = '40' AND `Name` = 'bx_photos_uploader_switcher';
UPDATE `sys_options` SET `VALUE` = 'html5,record,embed', `AvailableValues` = 'html5,regular,record,embed' WHERE `Name` = 'bx_photos_uploader_switcher';
UPDATE `sys_options` SET `order_in_kateg` = '56' WHERE `order_in_kateg` = '41' AND `Name` = 'bx_photos_header_cache';


DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'bx_photos_crop';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('bx_photos_crop', 'Photos Crop Photo', IFNULL(@iMaxOrder, 0));


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_home' AND `Caption` = '_bx_photos_cover' AND `Func` = 'Cover';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('bx_photos_home', '1140px', '', '_bx_photos_cover', 1, 1, 'Cover', '', 0, 100, 'non,memb', 0);


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_album_view' AND `Caption` = '_bx_photos_actions' AND `Func` = 'Actions';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_photos_album_view' AND `Column` = 3);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('bx_photos_album_view', '1140px', '', '', 3, IFNULL(@iMaxOrder, 0), 1, 28.1, 0, 'memb', '_bx_photos_actions', 'Actions');


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_crop' AND `Caption` = '_bx_photos_crop' AND `Func` = 'Crop';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('bx_photos_crop', '1140px', '', '_bx_photos_crop', 2, 0, 'Crop', '', 1, 100, 'non,memb', 0);


UPDATE `sys_objects_cmts` SET `ClassName` = 'BxPhotosCmts', `ClassFile` = 'modules/boonex/photos/classes/BxPhotosCmts.php' WHERE `ObjectName` = 'bx_photos'; 
UPDATE `sys_objects_cmts` SET `ClassName` = 'BxPhotosCmtsAlbums', `ClassFile` = 'modules/boonex/photos/classes/BxPhotosCmtsAlbums.php' WHERE `ObjectName` = 'bx_photos_albums'; 


UPDATE `sys_menu_top` SET `Picture` = 'picture-o', `Icon` = 'picture-o' WHERE `Picture` = 'picture' AND `Icon` = 'picture' AND `Parent` = 0 AND `Name` = 'Photos';


UPDATE `sys_menu_top` SET `Picture` = 'picture-o', `Link` = 'modules/?r=photos/view/|modules/?r=photos/crop/|photo/gallery/' WHERE `Parent` = 0 AND `Name` = 'PhotosUnit';
UPDATE `sys_menu_top` SET `Picture` = 'picture-o' WHERE `Parent` = 0 AND `Name` = 'PhotosAlbum';


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_photos';


UPDATE `sys_objects_actions` SET `Icon` = 'download' WHERE `Icon` = 'download-alt' AND `Type` = 'bx_photos';
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_photos';
UPDATE `sys_objects_actions` SET `Icon` = 'exclamation-circle' WHERE `Icon` = 'exclamation-sign' AND `Type` = 'bx_photos';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_photos';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_photos' AND `Caption` IN('{approvedCpt}', '{cropCpt}', '{SetAvatarCpt}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_photos_album' AND `Script` LIKE '%album_delete%';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_photos', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 8),
('bx_photos', '{cropCpt}', 'crop', '{moduleUrl}crop/{ID}', '', '', 9),
('bx_photos', '{SetAvatarCpt}', 'user', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}set_avatar/{ID}'', false, ''post''); return false;', '', 10),
('bx_photos_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxPhotosModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);

UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Caption` = '{sbs_bx_photos_title}' AND `Type` = 'bx_photos';

UPDATE `sys_objects_actions` SET `Icon` = 'picture-o' WHERE `Icon` = 'picture' AND `Type` = 'bx_photos_title';


UPDATE `sys_stat_site` SET `IconName` = 'picture-o' WHERE `IconName` = 'picture' AND `Name` = 'phs';


UPDATE `sys_menu_admin` SET `icon` = 'picture-o' WHERE `name` = 'bx_photos';


DELETE FROM `sys_objects_member_info` WHERE `object` IN('bx_photos_thumb_2x', 'bx_photos_icon_2x');
INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('bx_photos_thumb_2x', '_bx_photos_member_info_profile_photo_2x', 'thumb_2x', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php'),
('bx_photos_icon_2x', '_bx_photos_member_info_profile_photo_icon_2x', 'thumb_icon_2x', 'BxPhotosMemberInfo', 'modules/boonex/photos/classes/BxPhotosMemberInfo.php');


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_photos_size_error','_bx_photos_upl_file_err');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_photos_size_error','_bx_photos_upl_file_err');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'photos' AND `version` = '1.1.6';

