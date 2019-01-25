DROP TABLE IF EXISTS `[db_prefix]entries`;
DROP TABLE IF EXISTS `[db_prefix]comments`;
DROP TABLE IF EXISTS `[db_prefix]comments_track`;
DROP TABLE IF EXISTS `[db_prefix]voting`;
DROP TABLE IF EXISTS `[db_prefix]voting_track`;
DROP TABLE IF EXISTS `[db_prefix]views_track`;

SET @iTMParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name`='Articles' LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Name` IN ('Articles', 'ArticlesView') OR `Parent` = @iTMParentId;
DELETE FROM `sys_menu_admin` WHERE `name`='bx_articles';

DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_articles';

SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name`='Articles' LIMIT 1);
DELETE FROM `sys_options_cats` WHERE `name`='Articles' LIMIT 1;
DELETE FROM `sys_options` WHERE `kateg`=@iCategoryId OR `Name` IN ('permalinks_module_articles', 'category_auto_app_bx_articles');

DELETE FROM `sys_objects_cmts` WHERE `ObjectName`='bx_articles' LIMIT 1;
DELETE FROM `sys_objects_vote` WHERE `ObjectName`='bx_articles' LIMIT 1;
DELETE FROM `sys_objects_tag` WHERE `ObjectName`='bx_articles' LIMIT 1;
DELETE FROM `sys_objects_categories` WHERE `ObjectName`='bx_articles' LIMIT 1;
DELETE FROM `sys_categories` WHERE `Type` = 'bx_articles';
DELETE FROM `sys_objects_search` WHERE `ObjectName`='bx_articles' LIMIT 1;
DELETE FROM `sys_objects_views` WHERE `name`='bx_articles' LIMIT 1;

DELETE FROM `sys_page_compose_pages` WHERE `Name` IN ('articles_single', 'articles_home');
DELETE FROM `sys_page_compose` WHERE `Page` IN ('articles_single', 'articles_home') OR `Caption` IN ('_articles_bcaption_featured', '_articles_bcaption_latest', '_articles_bcaption_member');

DELETE FROM `sys_objects_actions` WHERE `Type`='bx_articles';

DELETE FROM `sys_sbs_entries` USING `sys_sbs_types`, `sys_sbs_entries` WHERE `sys_sbs_types`.`id`=`sys_sbs_entries`.`subscription_id` AND `sys_sbs_types`.`unit`='bx_articles';
DELETE FROM `sys_sbs_types` WHERE `unit`='bx_articles';

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsArticlesComments');

DELETE FROM `sys_acl_actions` WHERE `Name` IN ('Articles Delete');

DELETE FROM `sys_cron_jobs` WHERE `name`='bx_articles';

-- mobile
DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_articles';

-- site stats
DELETE FROM `sys_stat_site` WHERE `Name`='arl';

-- sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_articles';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_articles';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_articles';
