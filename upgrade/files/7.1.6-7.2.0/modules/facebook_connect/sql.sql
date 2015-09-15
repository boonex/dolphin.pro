
DELETE FROM `sys_objects_auths` WHERE `Link` = 'modules/?r=facebook_connect/login_form';
INSERT INTO `sys_objects_auths` (`Name`, `Title`, `Link`, `Icon`) VALUES
('facebook', '_bx_facebook_auth_title', 'modules/?r=facebook_connect/login_form', 'facebook-square');

SET @iHandlerId := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_facebook_connect');
DELETE FROM `sys_alerts` WHERE `unit` = 'system' AND `action` = 'join_after_payment' AND `handler_id` = @iHandlerId;
INSERT INTO `sys_alerts` SET `unit` = 'system', `action` = 'join_after_payment', `handler_id` = @iHandlerId;

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_facebook_error_email');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_facebook_error_email');

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'facebook_connect' AND `version` = '1.1.6';

