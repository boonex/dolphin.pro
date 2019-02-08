

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'SMTP Mailer' LIMIT 1);

DELETE FROM `sys_options` WHERE `Name` = 'bx_smtp_allow_selfsigned';

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_smtp_allow_selfsigned', '', @iCategId, 'Allow self-signed certificates', 'checkbox', '', '', '0', '');


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'smtpmailer' AND `version` = '1.3.5';

