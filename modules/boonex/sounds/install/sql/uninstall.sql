DROP TABLE IF EXISTS `[db_prefix]_favorites`;
DROP TABLE IF EXISTS `[db_prefix]_cmts`;
DROP TABLE IF EXISTS `[db_prefix]_cmts_albums`;
DROP TABLE IF EXISTS `[db_prefix]_rating`;
DROP TABLE IF EXISTS `[db_prefix]_voting_track`;
DROP TABLE IF EXISTS `[db_prefix]_views_track`;

DELETE FROM `sys_albums_objects`, `sys_albums` USING `sys_albums_objects`, `sys_albums` WHERE `sys_albums_objects`.`id_album` = `sys_albums`.`ID` AND `sys_albums`.`Type` = 'bx_sounds';
DELETE FROM `sys_albums` WHERE `Type` = 'bx_sounds';

SET @iKatID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Sounds' LIMIT 1);

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

DELETE FROM `sys_options_cats` WHERE `ID` = @iKatID;

DELETE FROM `sys_page_compose_pages` WHERE `Name` LIKE '%bx_sounds%';
DELETE FROM `sys_page_compose` WHERE `Caption` LIKE '%bx_sounds%' OR `Page` LIKE 'bx_sounds%' ;

SET @iTMParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Sounds' AND `Parent` = 0 LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Name` IN('Sounds', 'SoundsUnit', 'SoundsAlbum') OR `Parent` = @iTMParentId;

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_sounds';

DELETE FROM `sys_objects_search` WHERE `ObjectName` = 'bx_sounds' LIMIT 1;

DELETE FROM `sys_permalinks` WHERE `check` = 'bx_sounds_permalinks' LIMIT 1;

DELETE FROM `sys_options` WHERE `Name` = 'bx_sounds_permalinks' LIMIT 1;

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` LIKE 'bx_sounds%';

DELETE FROM `sys_objects_vote` WHERE `ObjectName` = 'bx_sounds';

DELETE FROM `sys_objects_views` WHERE `name` = 'bx_sounds';

DELETE FROM `sys_categories` WHERE `Type` = 'bx_sounds';
DELETE FROM `sys_objects_categories` WHERE `ObjectName` = 'bx_sounds';

DELETE FROM `sys_tags` WHERE `Type` = 'bx_sounds';
DELETE FROM `sys_objects_tag` WHERE `ObjectName` = 'bx_sounds';

DELETE FROM `sys_email_templates` WHERE `Name` LIKE '%bx_sounds%';

DELETE FROM `sys_sbs_entries` USING `sys_sbs_types`, `sys_sbs_entries` WHERE `sys_sbs_types`.`id`=`sys_sbs_entries`.`subscription_id` AND `sys_sbs_types`.`unit`='bx_sounds';
DELETE FROM `sys_sbs_types` WHERE `unit`='bx_sounds';

DELETE FROM `sys_stat_member` WHERE `Type` = 'pmu';

DELETE FROM `sys_stat_site` WHERE `Name` = 'pmu';

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_sounds';

DELETE FROM `sys_objects_actions` WHERE `Type` IN ('bx_sounds', 'bx_sounds_title', 'bx_sounds_album');

DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` LIKE 'sounds%';
DELETE FROM `sys_acl_actions` WHERE `Name` LIKE 'sounds%';

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'sounds';

DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_sounds';

DELETE FROM `sys_cron_jobs` WHERE `name` = 'bx_sounds';

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_sounds_profile_delete' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

-- mobile
DELETE FROM `sys_menu_mobile` WHERE `type` = '[db_prefix]';

-- sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object` = '[db_prefix]' OR `object` = '[db_prefix]_albums';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = '[db_prefix]';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = '[db_prefix]';
