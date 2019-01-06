
-- permalink
INSERT IGNORE  INTO `sys_permalinks` (`id`, `standard`, `permalink`, `check`) VALUES 
(NULL, 'modules/?r=smtpmailer/', 'm/smtpmailer/', 'bx_smtp_permalinks');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_smtp', '_bx_smtp', '{siteUrl}modules/?r=smtpmailer/administration/', 'SMTP Mailer', 'envelope-o', @iMax+1);

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('SMTP Mailer', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_smtp_permalinks', 'on', 26, 'Enable friendly permalinks in SMTP Mailer', 'checkbox', '', '', '0', ''),
('bx_smtp_on', '', @iCategId, 'Enable SMTP mailer', 'checkbox', '', '', '0', ''),
('bx_smtp_auth', '', @iCategId, 'SMTP authentication (Is your SMTP server requires username and password?)', 'checkbox', '', '', '0', ''),
('bx_smtp_username', '', @iCategId, 'SMTP username (only if SMTP authentication is enabled)', 'digit', '', '', '0', ''),
('bx_smtp_password', '', @iCategId, 'SMTP password (only if SMTP authentication is enabled)', 'digit', '', '', '0', ''),
('bx_smtp_host', '', @iCategId, 'SMTP server name or IP address', 'digit', '', '', '0', ''),
('bx_smtp_port', '25', @iCategId, 'SMTP server port number (25 - default, 465 - for secure ssl connection, 587 - for secure tls connection)', 'digit', '', '', '0', ''),
('bx_smtp_secure', 'Not Secure', @iCategId, 'Is your SMTP server requires secure connection', 'select', '', '', '0', 'Not Secure,SSL,TLS'), 
('bx_smtp_from_name', '', @iCategId, '''From'' name of the message', 'digit', '', '', '0', ''),
('bx_smtp_from_email', '', @iCategId, 'Override default sender email address', 'digit', '', '', '0', ''),
('bx_smtp_send_attachments', '', @iCategId, 'Attach every outgoing email all files from ''modules/boonex/smtpmailer/data/attach/'' folder', 'checkbox', '', '', '0', ''),
('bx_smtp_allow_selfsigned', '', @iCategId, 'Allow self-signed certificates', 'checkbox', '', '', '0', '');

