
UPDATE `sys_page_compose_pages` SET `Title` = 'Videos View Video' WHERE `Name` = '[db_prefix]_view' AND `Title` = 'View Video';
UPDATE `sys_page_compose_pages` SET `Title` = 'Videos View Album' WHERE `Name` = '[db_prefix]_album_view' AND `Title` = 'Video Album';
UPDATE `sys_page_compose_pages` SET `Title` = 'Videos Profile Albums' WHERE `Name` = '[db_prefix]_albums_owner' AND `Title` = 'Profile Albums';

UPDATE `sys_objects_actions` SET `Script` = 'oBxDolFiles.edit({ID})' WHERE `Type` = '[db_prefix]' AND `Eval` LIKE '%_Edit%';

DELETE FROM `sys_stat_site` WHERE `Name` = 'pvi';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('pvi', '[db_prefix]', 'modules/?r=videos/browse/all', 'SELECT COUNT(`ID`) FROM `RayVideoFiles` WHERE `Status`=''approved''', 'modules/?r=videos/administration/disapproved', 'SELECT COUNT(*) FROM `RayVideoFiles` as a left JOIN `sys_albums_objects` as b ON b.`id_object`=a.`ID` left JOIN `sys_albums` as c ON c.`ID`=b.`id_album` WHERE a.`Status` =''disapproved'' AND c.`AllowAlbumView` NOT IN(8) AND c.`Type`=''[db_prefix]''', 'film', @iStatSiteOrder);

