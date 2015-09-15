
UPDATE `sys_menu_top` SET `Link` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&ownerName={profileUsername}|blogs/posts/' WHERE `Name` = 'Profile Blog' AND `Parent` = 9 AND `Link` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&ownerID={profileID}|blogs/posts/';

UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_blogs';

UPDATE `sys_objects_cmts` SET `ClassName` = 'BxBlogsCmts', `ClassFile` = 'modules/boonex/blogs/classes/BxBlogsCmts.php' WHERE `ObjectName` = 'bx_blogs'; 

UPDATE `sys_permalinks` SET `standard` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&ownerName=', `permalink` = 'blogs/posts/' WHERE `standard` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&ownerID=' AND `permalink` = 'blogs/member_posts/';

UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_blogs';
UPDATE `sys_objects_actions` SET `Icon` = 'check-circle-o' WHERE `Icon` = 'ok-circle' AND `Type` = 'bx_blogs';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_blogs';
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_blogs';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'blogs' AND `version` = '1.1.6';

