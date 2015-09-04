

-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'book' WHERE `name` = 'Blogs';


-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile') AND `Desc` IN ('Recently posted blogs', 'Blogs calendar', 'Member blog block');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('PostActions', 'PostRate', 'PostOverview', 'PostCategories', 'PostFeature', 'PostTags', 'PostView', 'PostComments', 'PostSocialSharing') AND `Page` = 'bx_blogs';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Top', 'Latest', 'Calendar') AND `Page` = 'bx_blogs_home';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'bx_blogs' OR `Page` = 'bx_blogs_home';

INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'index', '1140px', 'Recently posted blogs', '_bx_blog_Blogs', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_index_page'');', 1, 71.9, 'non,memb', 0),
(NULL, 'index', '1140px', 'Blogs calendar', '_bx_blog_Calendar', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_calendar_index_page'', array($iBlockID));', 0, 28.1, 'non,memb', 0),
(NULL, 'profile', '1140px', 'Member blog block', '_bx_blog_Blog', 0, 0, 'PHP', 'return BxDolService::call(''blogs'', ''blogs_profile_page'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'bx_blogs', '1140px', '', '_Title', 1, 0, 'PostView', '', 0, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Comments', 1, 1, 'PostComments', '', 1, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_post_info', 2, 0, 'PostOverview', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Rate', 2, 1, 'PostRate', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Actions', 2, 2, 'PostActions', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_sys_block_title_social_sharing', 2, 3, 'PostSocialSharing', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_Categories', 2, 4, 'PostCategories', '', 0, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_Tags', 2, 5, 'PostTags', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs', '1140px', '', '_bx_blog_Featured_Posts', 0, 0, 'PostFeature', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Latest_posts', 1, 1, 'Latest', '', 1, 71.9, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Top_blog', 2, 2, 'Top', '', 1, 28.1, 'non,memb', 0),
(NULL, 'bx_blogs_home', '1140px', '', '_bx_blog_Calendar', 2, 1, 'Calendar', '', 0, 28.1, 'non,memb', 0);


-- stat site

UPDATE `sys_stat_site` SET `Title` = 'bx_blog_stat', `AdminLink` = 'modules/boonex/blogs/post_mod_blog.php', `AdminQuery` = 'SELECT COUNT(*) FROM `[db_prefix]_posts` WHERE `PostStatus`=''disapproval''', `IconName` = 'book' WHERE `Name` = 'blg';


-- menu top

SET @iMenuBlogs = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Blogs');
UPDATE `sys_menu_top` SET `Picture` = 'book', `Icon` = 'book' WHERE `ID` = @iMenuBlogs;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuBlogs OR (`Name` = 'Profile Blog' AND (`Parent` = 4 OR `Parent` = 9));
UPDATE `sys_menu_top` SET `Name` = 'Blog Post' WHERE `Name` = 'bx_blogpost_view' AND `Parent` = 0;
UPDATE `sys_menu_top` SET `Caption` = '_sys_calendar' WHERE `Name` = 'Blog Calendar' AND `Caption` = '_bx_blog_Calendar';


-- menu member 

SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_blogs';
INSERT INTO `sys_menu_member` SET `Name` = 'bx_blogs', `Eval` = 'return BxDolService::call(''blogs'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_blogs' AND `Caption` IN ('_Add Post', '_bx_blog_Blogs_Home', '_Edit', '_bx_blog_Back_to_Blog', '{sbs_blogs_title}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_blogs' AND (`Eval` LIKE '%_bx_blog_My_blog%' OR `Eval` LIKE '%_Feature it%' OR `Eval` LIKE '%_Delete%' OR `Eval` LIKE '%sSACaption%');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_blogs_m' AND `Caption` IN ('_bx_blog_Back_to_Blog', '_Add Post');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_blogs_m' AND (`Eval` LIKE '%_bx_blog_RSS%' OR `Eval` LIKE '%_bx_blog_Edit_blog%' OR `Eval` LIKE '%_bx_blog_Delete_blog%');

INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '_Add Post', 'plus', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/add/'' : ''modules/boonex/blogs/blogs.php?action=my_page&mode=add'';\r\nelse\r\n    return null;', 1, 'bx_blogs', 1),
(NULL, '{evalResult}', 'book', '{blog_owner_link}', '', 'if ({only_menu} == 1)\r\nreturn _t(''_bx_blog_My_blog'');\r\nelse\r\nreturn null;', 2, 'bx_blogs', 1),
(NULL, '_bx_blog_Blogs_Home', 'book', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/home/'' : ''modules/boonex/blogs/blogs.php?action=home'';\r\nelse\r\n    return null;', 3, 'bx_blogs', 0),
(NULL, '{evalResult}', 'star-empty', '{post_entry_url}&do=cfs&id={post_id}', '', '$iPostFeature = (int)''{post_featured}'';\r\nif (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true) {\r\nreturn ($iPostFeature==1) ? _t(''_De-Feature it'') : _t(''_Feature it'');\r\n}\r\nelse\r\nreturn null;', 4, 'bx_blogs', 0),
(NULL, '_Edit', 'edit', '{evalResult}', '', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true || {edit_allowed}) {\r\n    return (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/edit/{post_id}'' : ''modules/boonex/blogs/blogs.php?action=edit_post&EditPostID={post_id}'';\r\n}\r\nelse\r\n    return null;', 5, 'bx_blogs', 0),
(NULL, '{evalResult}', 'remove', '', 'iDelPostID = {post_id}; sWorkUrl = ''{work_url}''; if (confirm(''{sure_label}'')) { window.open (sWorkUrl+''?action=delete_post&DeletePostID=''+iDelPostID,''_self''); }', '$oModule = BxDolModule::getInstance(''BxBlogsModule'');\r\n if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode} == true || $oModule->isAllowedPostDelete({owner_id})) {\r\nreturn _t(''_Delete'');\r\n}\r\nelse\r\nreturn null;', 6, 'bx_blogs', 0),
(NULL, '{evalResult}', 'ok-circle', '{post_inside_entry_url}&sa={sSAAction}', '', '$sButAct = ''{sSACaption}'';\r\nif ({admin_mode} == true || {allow_approve}) {\r\nreturn $sButAct;\r\n}\r\nelse\r\nreturn null;', 7, 'bx_blogs', 0),
(NULL, '{sbs_blogs_title}', 'paper-clip', '', '{sbs_blogs_script}', '', 8, 'bx_blogs', 0),
(NULL, '_bx_blog_Back_to_Blog', 'book', '{evalResult}', '', 'return ''{blog_owner_link}'';\r\n', 9, 'bx_blogs', 0);

INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '{evalResult}', 'rss', '{site_url}rss_factory.php?action=blogs&pid={owner_id}', '', 'return _t(''_bx_blog_RSS'');', 1, 'bx_blogs_m', 0),
(NULL, '_bx_blog_Back_to_Blog', 'book', '{blog_owner_link}', '', '', 2, 'bx_blogs_m', 0),
(NULL, '_Add Post', 'plus', '{evalResult}', '', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn (getParam(''permalinks_blogs'') == ''on'') ? ''blogs/my_page/add/'' : ''modules/boonex/blogs/blogs.php?action=my_page&mode=add'';\r\nelse\r\nreturn null;', 3, 'bx_blogs_m', 1),
(NULL, '{evalResult}', 'edit', '', 'PushEditAtBlogOverview(''{blog_id}'', ''{blog_description_js}'', ''{owner_id}'');', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn _t(''_bx_blog_Edit_blog'');\r\nelse\r\nreturn null;', 4, 'bx_blogs_m', 1),
(NULL, '{evalResult}', 'remove', '', 'if (confirm(''{sure_label}'')) window.open (''{work_url}?action=delete_blog&DeleteBlogID={blog_id}'',''_self'');', 'if (({visitor_id}=={owner_id} && {owner_id}>0) || {admin_mode}==true)\r\nreturn _t(''_bx_blog_Delete_blog'');\r\nelse\r\nreturn null;', 5, 'bx_blogs_m', 0);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_blogs' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_blogs' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsBlogpostsComments', 't_sbsBlogpostsRates');
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsBlogpostsComments', 'New Comments To A Blog Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">blog post you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to blog post', 0);


-- menu mobile

DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_blogs';
SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_blogs', 'homepage', '_bx_blog_Blogs', '{site_url}modules/boonex/blogs/templates/base/images/icons/mobile_icon.png', 100, '{site_url}modules/boonex/blogs/blogs.php?action=mobile&mode=last', '', '', @iMaxOrderHomepage, 1),
('bx_blogs', 'profile', '_bx_blog_Blog', '', 100, '{site_url}modules/boonex/blogs/blogs.php?action=mobile&mode=user&id={profile_id}', 'return BxDolService::call(''blogs'', ''get_posts_count_for_member'', array(''{profile_id}''));', '', @iMaxOrderProfile, 1);


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_blogs';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_blogs', '_bx_blog_blog_posts', '0.8', 'auto', 'BxBlogsSiteMapsPosts', 'modules/boonex/blogs/classes/BxBlogsSiteMapsPosts.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_blogs';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_blogs', '_bx_blog_chart', 'bx_blogs_posts', 'PostDate', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_blog_Articles','_bx_blog_Under_Development','_bx_blog_admin_blog','_bx_blog_sbs_main','_bx_blog_sbs_rates','_bx_blog_user_made_blog_post');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_blog_Articles','_bx_blog_Under_Development','_bx_blog_admin_blog','_bx_blog_sbs_main','_bx_blog_sbs_rates','_bx_blog_user_made_blog_post');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'blogs' AND `version` = '1.0.9';

