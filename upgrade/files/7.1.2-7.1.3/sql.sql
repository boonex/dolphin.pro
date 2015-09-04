
-- ================ can be safely applied multiple times ================ 

UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your profile was reviewed and activated!</p>\r\n\r\n<p>Your Account: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<p>Member ID: <b><recipientID></b></p>\r\n\r\n<p>Your E-mail: <span style="color:#FF6633"><Email></span></p>\r\n\r\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_Activation' AND `LangID` = 0 AND `Body` = '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your profile was reviewed and activated !</p>\r\n\r\n<p>Your Account: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<p>Member ID: <b><recipientID></b></p>\r\n\r\n<p>Your E-mail: <span style="color:#FF6633"><Email></span></p>\r\n\r\n<bx_include_auto:_email_footer.html />';


DELETE FROM `sys_options` WHERE `Name` IN('sys_dnsbl_behaviour', 'sys_stopforumspam_enable', 'sys_stopforumspam_api_key', 'sys_antispam_add_nofollow');
SET @iCatAntispam = 23;
INSERT INTO `sys_options` VALUES
('sys_dnsbl_behaviour', 'approval', @iCatAntispam, 'User join behaviour if listed in DNS Block Lists', 'select', '', '', 11, 'block,approval'),
('sys_stopforumspam_enable', 'on', @iCatAntispam, 'Enable "Stop Forum Spam"', 'checkbox', '', '', 45, ''),
('sys_stopforumspam_api_key', '', @iCatAntispam, '"Stop Forum Spam" API Key', 'digit', '', '', 46, ''),
('sys_antispam_add_nofollow', 'on', @iCatAntispam, 'Add "nofollow" attribute for external links', 'checkbox', '', '', 80, '');


DELETE FROM `sys_dnsbl_rules` WHERE `chain` = 'spammers' AND `zonedomain` IN('dnsbl.dronebl.org.', 'opm.tornevall.org.');
INSERT INTO `sys_dnsbl_rules` (`chain`, `zonedomain`, `postvresp`, `url`, `recheck`, `comment`, `added`, `active`) VALUES
('spammers', 'dnsbl.dronebl.org.', '127.0.0.5', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Bottler', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.6', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Unknown spambot or drone', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.7', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'DDOS Drone', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.8', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'SOCKS Proxy', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.9', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'HTTP Proxy', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.10', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'ProxyChain', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.14', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Open Wingate Proxy', 1368854835, 1),
('spammers', 'dnsbl.dronebl.org.', '127.0.0.15', 'http://www.dronebl.org/', 'http://www.dronebl.org/lookup?ip=%s', 'Compromised router / gateway', 1368854835, 1),
('spammers', 'opm.tornevall.org.', '230', 'http://dnsbl.tornevall.org/', '', 'Block anonymous/elite proxies and abuse IPs', 1369274751, 1);



-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.3', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.3';

