

DELETE FROM `sys_options` WHERE `Name` IN('bx_videos_browse_width', 'bx_videos_browse_height');

UPDATE `sys_options` SET `VALUE` = 'avi flv mpg wmv mp4 m4v mov divx xvid mpeg 3gp webm' WHERE `VALUE` = 'avi flv mpg wmv mp4 m4v mov divx xvid mpeg 3gp' AND `Name` = 'bx_videos_allowed_exts';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_videos_number_index';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_videos_number_top';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_videos_number_user';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_videos_number_related';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_videos_number_previous_rated';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_videos_number_browse';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_videos_number_albums_browse';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_videos_number_albums_home';
UPDATE `sys_options` SET `VALUE` = 'html5,record,embed', `AvailableValues` = 'html5,regular,record,embed' WHERE `Name` = 'bx_videos_uploader_switcher';


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_videos_album_view' AND `Caption` = '_bx_videos_actions' AND `Func` = 'Actions';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_videos_album_view' AND `Column` = 3);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('bx_videos_album_view', '1140px', '', '', 3, IFNULL(@iMaxOrder, 0), 1, 28.1, 0, 'memb', '_bx_videos_actions', 'Actions');


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_videos';


UPDATE `sys_objects_cmts` SET `ClassName` = 'BxVideosCmts', `ClassFile` = 'modules/boonex/videos/classes/BxVideosCmts.php' WHERE `ObjectName` = 'bx_videos';
UPDATE `sys_objects_cmts` SET `ClassName` = 'BxVideosCmtsAlbums', `ClassFile` = 'modules/boonex/videos/classes/BxVideosCmtsAlbums.php' WHERE `ObjectName` = 'bx_videos_albums';


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_videos';
UPDATE `sys_objects_actions` SET `Icon` = 'exclamation-circle' WHERE `Icon` = 'exclamation-sign' AND `Type` = 'bx_videos';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_videos';
UPDATE `sys_objects_actions` SET `Icon` = 'download' WHERE `Icon` = 'download-alt' AND `Type` = 'bx_videos';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_videos';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_videos' AND `Caption` = '{approvedCpt}';
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_videos_album' AND `Script` LIKE '%album_delete%';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_videos', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 9),
('bx_videos_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxVideosModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_videos_size_error','_bx_videos_upl_file_err');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_videos_size_error','_bx_videos_upl_file_err');
        

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'videos' AND `version` = '1.1.6';

