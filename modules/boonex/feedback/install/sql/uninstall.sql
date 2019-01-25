DROP TABLE IF EXISTS `[db_prefix]entries`;
DROP TABLE IF EXISTS `[db_prefix]comments`;
DROP TABLE IF EXISTS `[db_prefix]comments_track`;
DROP TABLE IF EXISTS `[db_prefix]voting`;
DROP TABLE IF EXISTS `[db_prefix]voting_track`;

DELETE FROM `sys_menu_top` WHERE `Name`='Feedback' OR `Name`='FeedbackView';
DELETE FROM `sys_menu_admin` WHERE `name`='bx_feedback';

DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_feedback';

SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name`='Feedback' LIMIT 1);
DELETE FROM `sys_options_cats` WHERE `name`='Feedback' LIMIT 1;
DELETE FROM `sys_options` WHERE `kateg`=@iCategoryId OR `Name`='permalinks_module_feedback';

DELETE FROM `sys_objects_cmts` WHERE `ObjectName`='bx_feedback' LIMIT 1;
DELETE FROM `sys_objects_vote` WHERE `ObjectName`='bx_feedback' LIMIT 1;
DELETE FROM `sys_objects_tag` WHERE `ObjectName`='bx_feedback' LIMIT 1;
DELETE FROM `sys_objects_search` WHERE `ObjectName`='bx_feedback' LIMIT 1;

DELETE FROM `sys_page_compose_pages` WHERE `Name`='feedback';
DELETE FROM `sys_page_compose` WHERE `Caption` IN ('_feedback_bcaption_index') OR `Page`='feedback';

DELETE FROM `sys_privacy_actions` WHERE `module_uri`='feedback';

DELETE FROM `sys_objects_actions` WHERE `Type`='bx_feedback';

DELETE FROM `sys_sbs_entries` USING `sys_sbs_types`, `sys_sbs_entries` WHERE `sys_sbs_types`.`id`=`sys_sbs_entries`.`subscription_id` AND `sys_sbs_types`.`unit`='bx_feedback';
DELETE FROM `sys_sbs_types` WHERE `unit`='bx_feedback';

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsFeedbackComments');

DELETE FROM `sys_acl_actions` WHERE `Name` IN ('Feedback Delete');

-- site stats
DELETE FROM `sys_stat_site` WHERE `Name`='fdb';

-- sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object`='bx_feedback';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_feedback';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_feedback';
