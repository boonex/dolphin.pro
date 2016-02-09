

-- ================ can be safely applied multiple times ================ 


UPDATE `sys_menu_admin_top` SET `url` = 'https://www.boonex.com/market' WHERE `url` = 'http://www.boonex.com/market' AND `name` = 'extensions';
UPDATE `sys_menu_admin_top` SET `url` = 'https://www.boonex.com/trac/dolphin/wiki' WHERE `url` = 'http://www.boonex.com/trac/dolphin/wiki/Dolphin7Docs' AND `name` = 'info';



DELETE FROM `sys_email_templates` WHERE `Name` IN('t_UserMemChanged', 't_UserUnregistered');
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_UserMemChanged', 'Member Membership Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p><RealName>''s membership level was changed to: <b><MembershipLevel></b></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about membership change', 0),
('t_UserUnregistered', 'Member Unregistered', '<bx_include_auto:_email_header.html />\r\n\r\n<p>User: <NickName></p> \r\n<p>Email: <Email></p> \r\n<p>was unregistered.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about unregistered member', 0);



SET @iCatModeration = 6;
INSERT IGNORE INTO `sys_options` VALUES
('unregisterusernotify', 'on', @iCatModeration, 'Enable notification about unregistered members', 'checkbox', '', '', 50, '');

UPDATE `sys_options` SET `order_in_kateg` = 60 WHERE `Name` = 'ban_duration';



UPDATE `sys_page_compose` SET `Content` = 'https://www.boonex.com/notes/featured_posts/?rss=1#4' WHERE `Content` = 'http://www.boonex.com/notes/featured_posts/?rss=1#4' AND `Caption` = '_BoonEx News';



UPDATE `sys_profile_fields` SET `Deletable` = 0 WHERE `Name` = 'DateOfBirth';
UPDATE `sys_profile_fields` SET `Extra` = '' WHERE `Name` = 'Couple' AND `Extra` = 'Country\nCity';



DELETE FROM `sys_shared_sites` WHERE `Name` IN('blinklist', 'slashdot', 'stumbleupon', 'technorati');



DELETE FROM `sys_injections` WHERE `name` IN('sys_head', 'sys_body');
INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('sys_head', 0, 'injection_head', 'text', '', 0, 1),
('sys_body', 0, 'injection_footer', 'text', '', 0, 1);



DELETE FROM `sys_alerts` WHERE `unit` = 'profile' AND `action` = 'delete' AND `handler_id` = 2;
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'delete', 2);



-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.3.0.B2' WHERE `Name` = 'sys_tmp_version';

