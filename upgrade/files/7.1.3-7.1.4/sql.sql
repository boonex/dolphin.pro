
-- ================ can be safely applied multiple times ================ 

DELETE FROM `sys_email_templates` WHERE `Name` = 't_ModulesUpdates';
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_ModulesUpdates', '<SiteName> Automatic modules updates checker', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The following updates are available:</p>\r\n\r\n<p><MessageText></p>\r\n\r\n<p>If you want to install any of them you need to go to your site''s admin panel -> Modules -> Add & Manage and click Check For Updates button in Installed Modules block. It will load all available updates.</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message to admin about modules updates', 0);


SET @iCatGeneral = 3;
UPDATE `sys_options` SET `kateg` = @iCatGeneral, `order_in_kateg` = 1 WHERE `Name` = 'sys_ftp_login';
UPDATE `sys_options` SET `kateg` = @iCatGeneral, `order_in_kateg` = 2 WHERE `Name` = 'sys_ftp_password';
UPDATE `sys_options` SET `kateg` = @iCatGeneral, `order_in_kateg` = 3 WHERE `Name` = 'sys_ftp_dir';


DELETE FROM `sys_cron_jobs` WHERE `name` = 'modules' AND `class` = 'BxDolCronModules';
INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
('modules', '0 0 * * 0', 'BxDolCronModules', 'inc/classes/BxDolCronModules.php', '');



-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.4', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.4';

