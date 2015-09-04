
DELETE FROM `sys_stat_site` WHERE `Name` = 'shf';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('shf', 'bx_files', 'modules/?r=files/browse/all', 'SELECT COUNT(`ID`) FROM `bx_files_main` WHERE `Status`=''approved''','modules/?r=files/administration/home/pending', 'SELECT COUNT(*) FROM `bx_files_main` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''pending'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''bx_files''', 'save', @iStatSiteOrder);

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_files';
INSERT INTO `sys_account_custom_stat_elements` VALUES
(NULL, '_bx_files', '__shf__ (<a href="modules/?r=files/albums/my/main/">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'files' AND `version` = '1.1.0';

