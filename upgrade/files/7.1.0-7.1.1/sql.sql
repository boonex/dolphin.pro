

-- ================ can be safely applied multiple times ================ 

UPDATE `sys_menu_top` SET `Check` = 'return $GLOBALS[''profileID''] == $GLOBALS[''memberID''];' WHERE `ID` = 4;
UPDATE `sys_menu_top` SET `Check` = 'return $GLOBALS[''profileID''] != $GLOBALS[''memberID''];' WHERE `ID` = 9;


DELETE FROM `sys_objects_site_maps` WHERE `object` = 'pages';
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('pages', '_sys_sitemap_pages', '0.8', 'weekly', 'BxDolSiteMapsPages', '', 4, 1);

DELETE FROM `sys_stat_site` WHERE `Name` = 'all';
INSERT INTO `sys_stat_site`(`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('all', 'Members', 'browse.php', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Active'' AND (`Couple`=''0'' OR `Couple`>`ID`)', '{admin_url}profiles.php?action=browse&by=status&value=approval', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Approval'' AND (`Couple`=''0'' OR `Couple`>`ID`)', 'user', 1);

-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bottom_text');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bottom_text');



-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.1', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.1';

