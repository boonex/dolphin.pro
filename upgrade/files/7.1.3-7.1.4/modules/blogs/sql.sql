

UPDATE `sys_menu_mobile` SET `action_data` = '{xmlrpc_url}r.php?url=modules%2Fboonex%2Fblogs%2Fblogs.php%3Faction%3Dmobile%26mode%3Dlast&user={member_username}&pwd={member_password}' WHERE `type` = 'bx_blogs' AND `page` = 'homepage' AND `title` = '_bx_blog_Blogs';
UPDATE `sys_menu_mobile` SET `action_data` = '{xmlrpc_url}r.php?url=modules%2Fboonex%2Fblogs%2Fblogs.php%3Faction%3Dmobile%26mode%3Duser%26id%3D{profile_id}&user={member_username}&pwd={member_password}' WHERE `type` = 'bx_blogs' AND `page` = 'profile' AND `title` = '_bx_blog_Blog';


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'blogs' AND `version` = '1.1.3';

