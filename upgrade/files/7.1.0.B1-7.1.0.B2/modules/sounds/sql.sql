
UPDATE `sys_page_compose_pages` SET `Title` = 'Sounds Listen Sound' WHERE `Name` = '[db_prefix]_view' AND `Title` = 'Listen Sound';
UPDATE `sys_page_compose_pages` SET `Title` = 'Sounds View Album' WHERE `Name` = '[db_prefix]_album_view' AND `Title` = 'Sound Album';
UPDATE `sys_page_compose_pages` SET `Title` = 'Sounds Profile Albums' WHERE `Name` = '[db_prefix]_albums_owner' AND `Title` = 'Profile Albums';

UPDATE `sys_objects_actions` SET `Script` = 'oBxDolFiles.edit({ID})' WHERE `Type` = '[db_prefix]' AND `Eval` LIKE '%_Edit%';

DELETE FROM `sys_stat_site` WHERE `Name` = 'pmu';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('pmu', '[db_prefix]', 'modules/?r=sounds/browse/all', 'SELECT COUNT(`ID`) FROM `RayMp3Files` WHERE `Status`=''approved''', 'modules/?r=sounds/administration/disapproved', 'SELECT COUNT(*) FROM `RayMp3Files` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''disapproved'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'music', @iStatSiteOrder);

