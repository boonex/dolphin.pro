

-- ================ can be safely applied multiple times ================ 


-- new email template was added
DELETE FROM `sys_email_templates` WHERE `Name`='t_MemChanged';
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_MemChanged', 'Your membership level was changed', '<html><head></head><body style="font: 12px Verdana; color:#000000">\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Your membership level was changed to: <b><MembershipLevel></b></p>\r\n\r\n<p>Please refer to membership page for more details: <a href="<Domain>modules/?r=membership/index/">My Membership</a></p>\r\n\r\n<p>--</p>\r\n<p style="font: bold 10px Verdana; color:red"><SiteName> mail delivery system!!!\r\n<br />Auto-generated e-mail, please, do not reply!!!</p></body></html>', 'The letter is sent to members whose membership level was changed.', 0);


-- unused option was deleted
DELETE FROM `sys_options` WHERE `Name` = 'db_clean_views';


-- wrong link in prfile view of mobile app was corrected
UPDATE `sys_menu_mobile` SET `action` = 5 WHERE `action` = 8 AND `type` = 'system' AND `page` = 'profile' AND `title` = '_sys_mobile_profile_info';


-- delete unused language keys 
DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_adm_txt_langs_looks_for', '_adm_txt_langs_apply', '_adm_txt_mp_last_login');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_adm_txt_langs_looks_for', '_adm_txt_langs_apply', '_adm_txt_mp_last_login');


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.0.8', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.0.8';

