
-- tables
DROP TABLE IF EXISTS `[db_prefix]main`;
DROP TABLE IF EXISTS `[db_prefix]fans`;
DROP TABLE IF EXISTS `[db_prefix]admins`;
DROP TABLE IF EXISTS `[db_prefix]images`;
DROP TABLE IF EXISTS `[db_prefix]videos`;
DROP TABLE IF EXISTS `[db_prefix]sounds`;
DROP TABLE IF EXISTS `[db_prefix]files`;
DROP TABLE IF EXISTS `[db_prefix]rating`;
DROP TABLE IF EXISTS `[db_prefix]rating_track`;
DROP TABLE IF EXISTS `[db_prefix]cmts`;
DROP TABLE IF EXISTS `[db_prefix]cmts_track`;
DROP TABLE IF EXISTS `[db_prefix]views_track`;
DROP TABLE IF EXISTS `[db_prefix]shoutbox`;

-- forum tables
DROP TABLE IF EXISTS `[db_prefix]forum`;
DROP TABLE IF EXISTS `[db_prefix]forum_cat`;
DROP TABLE IF EXISTS `[db_prefix]forum_cat`;
DROP TABLE IF EXISTS `[db_prefix]forum_flag`;
DROP TABLE IF EXISTS `[db_prefix]forum_post`;
DROP TABLE IF EXISTS `[db_prefix]forum_topic`;
DROP TABLE IF EXISTS `[db_prefix]forum_user`;
DROP TABLE IF EXISTS `[db_prefix]forum_user_activity`;
DROP TABLE IF EXISTS `[db_prefix]forum_user_stat`;
DROP TABLE IF EXISTS `[db_prefix]forum_vote`;
DROP TABLE IF EXISTS `[db_prefix]forum_actions_log`;
DROP TABLE IF EXISTS `[db_prefix]forum_attachments`;
DROP TABLE IF EXISTS `[db_prefix]forum_signatures`;

-- compose pages
DELETE FROM `sys_page_compose_pages` WHERE `Name` IN('bx_groups_view', 'bx_groups_celendar', 'bx_groups_main', 'bx_groups_my');
DELETE FROM `sys_page_compose` WHERE `Page` IN('bx_groups_view', 'bx_groups_celendar', 'bx_groups_main', 'bx_groups_my');
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Groups';
DELETE FROM `sys_page_compose` WHERE `Page` = 'member' AND `Desc` = 'Joined Groups';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'User Groups';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Joined Groups';

-- system objects
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=groups/';
DELETE FROM `sys_objects_vote` WHERE `ObjectName` = 'bx_groups';
DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_groups';
DELETE FROM `sys_objects_views` WHERE `name` = 'bx_groups';
DELETE FROM `sys_objects_categories` WHERE `ObjectName` = 'bx_groups';
DELETE FROM `sys_categories` WHERE `Type` = 'bx_groups';
DELETE FROM `sys_categories` WHERE `Type` = 'bx_photos' AND `Category` = 'Groups';
DELETE FROM `sys_objects_tag` WHERE `ObjectName` = 'bx_groups';
DELETE FROM `sys_tags` WHERE `Type` = 'bx_groups';
DELETE FROM `sys_objects_search` WHERE `ObjectName` = 'bx_groups';
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_groups' OR `Type` = 'bx_groups_title';
DELETE FROM `sys_stat_site` WHERE `Name` = 'bx_groups';
DELETE FROM `sys_stat_member` WHERE TYPE IN('bx_groups', 'bx_groupsp');
DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_groups';

-- email templates
DELETE FROM `sys_email_templates` WHERE `Name` = 'bx_groups_broadcast' OR `Name` = 'bx_groups_join_request' OR `Name` = 'bx_groups_join_reject' OR `Name` = 'bx_groups_join_confirm' OR `Name` = 'bx_groups_fan_remove' OR `Name` = 'bx_groups_fan_become_admin' OR `Name` = 'bx_groups_admin_become_fan' OR `Name` = 'bx_groups_sbs' OR `Name` = 'bx_groups_invitation';

-- top menu
SET @iCatRoot := (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Groups' AND `Parent` = 0 LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Parent` = @iCatRoot;
DELETE FROM `sys_menu_top` WHERE `ID` = @iCatRoot;

SET @iCatRoot := (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Groups' AND `Parent` = 0 LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Parent` = @iCatRoot;
DELETE FROM `sys_menu_top` WHERE `ID` = @iCatRoot;

DELETE FROM `sys_menu_top` WHERE `Parent` = 9 AND `Name` = 'Groups';
DELETE FROM `sys_menu_top` WHERE `Parent` = 4 AND `Name` = 'Groups';

-- member menu
DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_groups';

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_groups';

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Groups' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_groups_permalinks';

-- membership levels
DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` IN('groups view group', 'groups browse', 'groups search', 'groups add group', 'groups comments delete and edit', 'groups edit any group', 'groups delete any group', 'groups mark as featured', 'groups approve groups', 'groups broadcast message');
DELETE FROM `sys_acl_actions` WHERE `Name` IN('groups view group', 'groups browse', 'groups search', 'groups add group', 'groups comments delete and edit', 'groups edit any group', 'groups delete any group', 'groups mark as featured', 'groups approve groups', 'groups broadcast message');

-- alerts
SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_groups_profile_delete' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_groups_media_delete' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_groups_map_install' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

-- privacy
DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'groups';

-- subscriptions
DELETE FROM `sys_sbs_entries` USING `sys_sbs_types`, `sys_sbs_entries` WHERE `sys_sbs_types`.`id`=`sys_sbs_entries`.`subscription_id` AND `sys_sbs_types`.`unit`='bx_groups';
DELETE FROM `sys_sbs_types` WHERE `unit`='bx_groups';

-- sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_groups';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_groups';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_groups';

