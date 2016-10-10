
UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p>I thought you''d be interested: <a href="<Link>"><Link></a><br />\r\n---<br />\r\n<a href="<SenderLink>"><SenderName></a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_TellFriend' AND `LangID` = 0;
UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n\r\n\r\n<p>Check out this profile: <a href="<Link>"><Link></a><br />\r\n---<br />\r\n<a href="<SenderLink>"><SenderName></a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_TellFriendProfile' AND `LangID` = 0;

INSERT IGNORE INTO `sys_options` VALUES ('sys_ftp_host', '', @iCatGeneral, 'FTP host', 'digit', '', '', 1, '');
UPDATE `sys_options` SET `desc` = 'FTP login', `order_in_kateg` = 2 WHERE `Name` = 'sys_ftp_login';
UPDATE `sys_options` SET `desc` = 'FTP password', `order_in_kateg` = 3 WHERE `Name` = 'sys_ftp_password';
UPDATE `sys_options` SET `order_in_kateg` = 4 WHERE `Name` = 'sys_ftp_dir';

-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.3.2' WHERE `Name` = 'sys_tmp_version';

