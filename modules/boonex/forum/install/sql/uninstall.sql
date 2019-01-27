
DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` IN('forum public read', 'forum public post', 'forum private read', 'forum private post', 'forum search', 'forum edit all', 'forum delete all', 'forum make sticky', 'forum del topics', 'forum move topics', 'forum hide topics', 'forum unhide topics', 'forum hide posts', 'forum unhide posts', 'forum files download');
DELETE FROM `sys_acl_actions` WHERE `Name` IN('forum public read', 'forum public post', 'forum private read', 'forum private post', 'forum search', 'forum edit all', 'forum delete all', 'forum make sticky', 'forum del topics', 'forum move topics', 'forum hide topics', 'forum unhide topics', 'forum hide posts', 'forum unhide posts', 'forum files download');

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_forums';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_forum_title';

DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_forum' LIMIT 1;

SET @iId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Forums' AND `Parent` = 0 LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Parent` = @iId;
DELETE FROM `sys_menu_top` WHERE `ID` = @iId;

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_forum';

DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Forum Posts' LIMIT 1;
DELETE FROM `sys_page_compose` WHERE `Page` = 'member' AND `Desc` = 'Forum Posts' LIMIT 1;
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Last posts of a member in the forum' LIMIT 1;
DELETE FROM `sys_page_compose` WHERE `Page` = 'forums_index';
DELETE FROM `sys_page_compose` WHERE `Page` = 'forums_home';

DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'forums_index';
DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'forums_home';

DELETE FROM `sys_stat_site` WHERE `Name` = 'tps' LIMIT 1;

DELETE FROM `sys_stat_member` WHERE `sys_stat_member`.`Type`  = 'mot' LIMIT 1;
DELETE FROM `sys_stat_member` WHERE `sys_stat_member`.`Type`  = 'mop' LIMIT 1;

DROP TABLE IF EXISTS `bx_forum`, `bx_forum_cat`, `bx_forum_flag`, `bx_forum_post`, `bx_forum_topic`, `bx_forum_user`, `bx_forum_user_activity`, `bx_forum_user_stat`, `bx_forum_vote`, `bx_forum_actions_log`, `bx_forum_attachments`, `bx_forum_signatures`;

-- alerts
SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_forum_profile' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

-- email templates
DELETE FROM `sys_email_templates` WHERE `Name` = 'bx_forum_notifier';

-- sitemap
DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_forum';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_forum';

-- export
DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_forum';

