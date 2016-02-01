

UPDATE `sys_options` SET `VALUE` = '240' WHERE `Name` = 'bx_blogs_bigthumbsize' AND `VALUE` = '340';


UPDATE `sys_menu_top` SET `Link` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&blogOwnerName={profileUsername}|blogs/posts/' WHERE `Name` = 'Profile Blog' AND `Parent` = 9;


UPDATE `sys_permalinks` SET `standard` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&blogOwnerName=' WHERE `check` = 'permalinks_blogs' AND `standard` = 'modules/boonex/blogs/blogs.php?action=show_member_blog&ownerName=';


DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_blogs';
INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '{repostCpt}', 'repeat', '', '{repostScript}', '', 11, 'bx_blogs', 0);


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'blogs' AND `version` = '1.2.1';

