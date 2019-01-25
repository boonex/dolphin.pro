DROP TABLE IF EXISTS `[db_prefix]_favorites`;
DROP TABLE IF EXISTS `[db_prefix]_cmts`;
DROP TABLE IF EXISTS `[db_prefix]_cmts_albums`;
DROP TABLE IF EXISTS `[db_prefix]_rating`;
DROP TABLE IF EXISTS `[db_prefix]_voting_track`;
DROP TABLE IF EXISTS `[db_prefix]_views_track`;

DELETE FROM `sys_albums_objects`, `sys_albums` USING `sys_albums_objects`, `sys_albums` WHERE `sys_albums_objects`.`id_album` = `sys_albums`.`ID` AND `sys_albums`.`Type` = 'bx_videos';
DELETE FROM `sys_albums` WHERE `Type` = 'bx_videos';

SET @iKatID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Videos' LIMIT 1);

DELETE FROM `sys_options` WHERE `kateg` = @iKatID;

DELETE FROM `sys_options_cats` WHERE `ID` = @iKatID;

DELETE FROM `sys_page_compose_pages` WHERE `Name` LIKE '%bx_videos%';
DELETE FROM `sys_page_compose` WHERE `Caption` LIKE '%bx_videos%' OR `Page` LIKE 'bx_videos%';

SET @iTMParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Videos' AND `Parent` = 0 LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Name` IN('Videos', 'VideosUnit', 'VideosAlbum') OR `Parent` = @iTMParentId;

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_videos';

DELETE FROM `sys_objects_search` WHERE `ObjectName` = 'bx_videos' LIMIT 1;

DELETE FROM `sys_permalinks` WHERE `check` = 'bx_videos_permalinks' LIMIT 1;

DELETE FROM `sys_options` WHERE `Name` = 'bx_videos_permalinks' LIMIT 1;

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` LIKE 'bx_videos%';

DELETE FROM `sys_objects_vote` WHERE `ObjectName` = 'bx_videos';

DELETE FROM `sys_objects_views` WHERE `name` = 'bx_videos';

DELETE FROM `sys_categories` WHERE `Type` = 'bx_videos';
DELETE FROM `sys_objects_categories` WHERE `ObjectName` = 'bx_videos';

DELETE FROM `sys_tags` WHERE `Type` = 'bx_videos';
DELETE FROM `sys_objects_tag` WHERE `ObjectName` = 'bx_videos';

DELETE FROM `sys_email_templates` WHERE `Name` LIKE '%bx_videos%';

DELETE FROM `sys_sbs_entries` USING `sys_sbs_types`, `sys_sbs_entries` WHERE `sys_sbs_types`.`id`=`sys_sbs_entries`.`subscription_id` AND `sys_sbs_types`.`unit`='bx_videos';
DELETE FROM `sys_sbs_types` WHERE `unit`='bx_videos';

DELETE FROM `sys_stat_member` WHERE `Type` = 'pvi';

DELETE FROM `sys_stat_site` WHERE `Name` = 'pvi';

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_videos';

DELETE FROM `sys_objects_actions` WHERE `Type` IN ('bx_videos', 'bx_videos_title', 'bx_videos_album');

DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` LIKE 'videos%';
DELETE FROM `sys_acl_actions` WHERE `Name` LIKE 'videos%';

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'videos';

DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_videos';

DELETE FROM `sys_cron_jobs` WHERE `name` = 'bx_videos';

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_videos_profile_delete' LIMIT 1);
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
