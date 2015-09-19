
UPDATE `sys_menu_admin` SET `icon` = 'envelope-o' WHERE `name` = 'bx_smtp';

UPDATE `sys_options` SET `AvailableValues` = 'Not Secure,SSL,TLS' WHERE `Name` = 'bx_smtp_secure';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'smtpmailer' AND `version` = '1.1.6';

