
UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>\r\nThere was a new update of the Timeline you subscribed to!\r\n</p>\r\n\r\n<p>\r\n<a href="<ViewLink>">View it now</a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_sbsWallUpdates' AND `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>\r\nThere was a new update of the Timeline you subsribed to!\r\n</p>\r\n\r\n<p>\r\n<a href="<ViewLink>">View it now</a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />';


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'wall' AND `version` = '1.1.0';

