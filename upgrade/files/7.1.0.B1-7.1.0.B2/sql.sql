
-- ================ can be safely applied multiple times ================ 

DELETE FROM `sys_menu_admin` WHERE `name` = 'admin_menu';
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'builders' AND `parent_id` = 0);
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'admin_menu', '_adm_mmi_admin_menu', '{siteAdminUrl}menu_compose_admin.php', 'For top admin''s menu items management', 'list col-red2', '', '', 5);

UPDATE `sys_menu_admin` SET `order` = 10 WHERE `order` = 5 AND `parent_id` = @iParentId AND `name` = 'profile_fields';
UPDATE `sys_menu_admin` SET `order` = 20 WHERE `order` = 6 AND `parent_id` = @iParentId AND `name` = 'pages_blocks';
UPDATE `sys_menu_admin` SET `order` = 21 WHERE `order` = 7 AND `parent_id` = @iParentId AND `name` = 'mobile_pages';
UPDATE `sys_menu_admin` SET `order` = 30 WHERE `order` = 8 AND `parent_id` = @iParentId AND `name` = 'predefined_values';

DELETE FROM `sys_menu_admin` WHERE `name` = 'templates';
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'settings' AND `parent_id` = 0);
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'templates', '_adm_mmi_templates', '{siteAdminUrl}templates.php', 'Templates management', 'eye-open col-blue2', '', '', 7);



UPDATE `sys_options` SET `kateg` = 3 WHERE `Name` = 'default_country';
UPDATE `sys_options` SET `kateg` = 13 WHERE `Name` = 'ext_nav_menu_enabled';
UPDATE `sys_options` SET `kateg` = 13 WHERE `Name` = 'ext_nav_menu_top_position';



-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.0.B2', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.0.B2';

