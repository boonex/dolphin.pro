
DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_blog_Blog';
INSERT INTO `sys_account_custom_stat_elements` (`Label`, `Value`) VALUES
('_bx_blog_Blog', '__mbp__ (<a href="__site_url__modules/boonex/blogs/blogs.php?action=my_page&mode=add">__l_add__</a>)');

UPDATE `sys_menu_admin` SET `title` = '_sys_module_blogs' WHERE `parent_id` = 2 AND `name` = 'Blogs';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_blogs' AND `Caption` = '_Share';
INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '_Share', 'share', '', 'showPopupAnyHtml(''{base_url}blogs.php?action=share_post&post_id={post_id}'');', '', 9, 'bx_blogs', 0);
UPDATE `sys_objects_actions` SET `Order` = 10 WHERE `Type` = 'bx_blogs' AND `Caption` = '_bx_blog_Back_to_Blog' AND `Order` = 9;

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_blog_blog_posts_adm_stats');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_blog_blog_posts_adm_stats');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'blogs' AND `version` = '1.1.0';

