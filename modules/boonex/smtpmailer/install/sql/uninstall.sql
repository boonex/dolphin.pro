
-- permalink
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=smtpmailer/';

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'bx_smtp';

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'SMTP Mailer' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_smtp_permalinks';

