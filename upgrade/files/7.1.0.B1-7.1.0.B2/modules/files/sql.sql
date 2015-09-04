
UPDATE `sys_page_compose_pages` SET `Title` = 'Files View File' WHERE `Name` = '[db_prefix]_view' AND `Title` = 'View File';
UPDATE `sys_page_compose_pages` SET `Title` = 'Files View Folder' WHERE `Name` = '[db_prefix]_album_view' AND `Title` = 'File Folder';
UPDATE `sys_page_compose_pages` SET `Title` = 'Files Profile Folders' WHERE `Name` = '[db_prefix]_albums_owner' AND `Title` = 'Profile Folders';

UPDATE `sys_objects_actions` SET `Script` = 'oBxDolFiles.edit({ID})' WHERE `Type` = '[db_prefix]' AND `Eval` LIKE '%_Edit%';

DELETE FROM `sys_stat_site` WHERE `Name` = 'shf';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('shf', '[db_prefix]', 'modules/?r=files/browse/all', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''approved''','modules/?r=files/administration/disapproved', 'SELECT COUNT(*) FROM `[db_prefix]_main` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''pending'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'save', @iStatSiteOrder);

