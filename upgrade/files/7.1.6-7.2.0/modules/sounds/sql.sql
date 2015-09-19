

DELETE FROM `sys_options` WHERE `Name` IN('bx_sounds_browse_width', 'bx_sounds_browse_height');

UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_sounds_number_index';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_sounds_number_top';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_sounds_number_user';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_sounds_number_related';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_sounds_number_previous_rated';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_sounds_number_browse';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `VALUE` = '8' AND `Name` = 'bx_sounds_number_albums_browse';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `VALUE` = '4' AND `Name` = 'bx_sounds_number_albums_home';
UPDATE `sys_options` SET `VALUE` = 'html5,record', `AvailableValues` = 'html5,regular,record' WHERE `Name` = 'bx_sounds_uploader_switcher';


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_album_view' AND `Caption` = '_bx_sounds_actions' AND `Func` = 'Actions';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_album_view' AND `Column` = 3);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('bx_sounds_album_view', '1140px', '', '', 3, IFNULL(@iMaxOrder, 0), 1, 28.1, 0, 'memb', '_bx_sounds_actions', 'Actions');


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_sounds';


UPDATE `sys_objects_cmts` SET `ClassName` = 'BxSoundsCmts', `ClassFile` = 'modules/boonex/sounds/classes/BxSoundsCmts.php' WHERE `ObjectName` = 'bx_sounds';
UPDATE `sys_objects_cmts` SET `ClassName` = 'BxSoundsCmtsAlbums', `ClassFile` = 'modules/boonex/sounds/classes/BxSoundsCmtsAlbums.php' WHERE `ObjectName` = 'bx_sounds_albums';


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_sounds';
UPDATE `sys_objects_actions` SET `Icon` = 'exclamation-circle' WHERE `Icon` = 'exclamation-sign' AND `Type` = 'bx_sounds';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_sounds';
UPDATE `sys_objects_actions` SET `Icon` = 'download' WHERE `Icon` = 'download-alt' AND `Type` = 'bx_sounds';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_sounds';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_sounds' AND `Caption` = '{approvedCpt}';
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_sounds_album' AND `Script` LIKE '%album_delete%';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_sounds', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 9),
('bx_sounds_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxSoundsModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);




-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_sounds_size_error','_bx_sounds_upl_file_err');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_sounds_size_error','_bx_sounds_upl_file_err');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'sounds' AND `version` = '1.1.6';

