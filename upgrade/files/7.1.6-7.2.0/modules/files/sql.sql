

UPDATE `sys_options` SET `VALUE` = 'html5' WHERE `Name` = 'bx_files_uploader_switcher';


DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_album_view' AND `Func` = 'Actions';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_files_album_view' AND `Column` = 3);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('bx_files_album_view', '1140px', '', '', 3, IFNULL(@iMaxOrder, 0), 1, 28.1, 0, 'memb', '_bx_files_actions', 'Actions');


UPDATE `sys_objects_cmts` SET `ClassName` = 'BxFilesCmts', `ClassFile` = 'modules/boonex/files/classes/BxFilesCmts.php' WHERE `ObjectName` = 'bx_files';
UPDATE `sys_objects_cmts` SET `ClassName` = 'BxFilesCmtsAlbums', `ClassFile` = 'modules/boonex/files/classes/BxFilesCmtsAlbums.php' WHERE `ObjectName` = 'bx_files_albums';


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_files';


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_files';
UPDATE `sys_objects_actions` SET `Icon` = 'exclamation-circle' WHERE `Icon` = 'exclamation-sign' AND `Type` = 'bx_files';
UPDATE `sys_objects_actions` SET `Icon` = 'download' WHERE `Icon` = 'download-alt' AND `Type` = 'bx_files';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_files';

UPDATE `sys_objects_actions` SET `Url` = '{moduleUrl}get_file/{ID}{extension}' WHERE `Url` = '{moduleUrl}get_file/{ID}' AND `Caption` = '{downloadCpt}' AND `Type` = 'bx_files';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_files' AND `Caption` = '{approvedCpt}';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_files', '{approvedCpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}approve/{ID}/{approvedAct}'', false, ''post''); return false;', '', 8);

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_files_album' AND `Script` LIKE '%album_delete%';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_files_album', '{evalResult}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}album_delete/{albumUri}'', false, ''post'', true); return false;', 'return (getLoggedId() && BxDolModule::getInstance(''BxFilesModule'')->isAllowedDeleteAlbum({ID})) ? _t(''_Delete'') : '''';', 1);

UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Caption` = '{sbs_bx_files_title}' AND `Type` = 'bx_files';


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_files_size_error','_bx_files_upl_file_err');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_files_size_error','_bx_files_upl_file_err');

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'files' AND `version` = '1.1.6';

