
-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'envelope-alt' WHERE `name` = 'bx_smtp';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'smtpmailer' AND `version` = '1.0.9';

