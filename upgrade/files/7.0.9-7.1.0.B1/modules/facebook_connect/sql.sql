
-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` = 't_fb_connect_password_generated';

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('t_fb_connect_password_generated', 'New Password Generated', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <NickName></b>, \r\n\r\n<p>\r\nYour new password - <b><NewPassword></b></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Facebook connect password generated', 0);


-- menu admin

SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
UPDATE `sys_menu_admin` SET `icon` = 'facebook', `order` = @iOrder+1 WHERE `name` = 'Facebook connect';


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_facebook';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `column_date`, `column_count`, `type`, `options`, `query`, `active`, `order`) VALUES
('bx_facebook', '_bx_facebook_chart', '', '', 'DateReg', 0, 1, '', '', 'SELECT {field_date_formatted} AS `period`, COUNT(*) AS {object} FROM `Profiles` INNER JOIN `bx_facebook_accounts` ON (`id_profile` = `ID`) WHERE {field_date} >= ''{from}'' AND {field_date} <= ''{to}'' GROUP BY `period` ORDER BY {field_date} ASC', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_facebook_error_occured','_bx_facebook_profile_exist','_bx_facebook_profile_not_defined');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_facebook_error_occured','_bx_facebook_profile_exist','_bx_facebook_profile_not_defined');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'facebook_connect' AND `version` = '1.0.9';

