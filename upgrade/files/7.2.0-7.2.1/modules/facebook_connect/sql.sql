
UPDATE `sys_options` SET `desc` = 'Facebook App ID' WHERE `Name` = 'bx_facebook_connect_api_key';

DELETE FROM `sys_options` WHERE `Name` = 'bx_facebook_connect_extended_info';

SET @iKategId = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'bx_facebook_connect_api_key');
INSERT INTO `sys_options` SET `Name` = 'bx_facebook_connect_extended_info', `kateg` = @iKategId, `desc` = 'Fetch extended profile info (facebook app review is required)', `Type` = 'checkbox', `VALUE` = '', `order_in_kateg` = 5;

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.1' WHERE `uri` = 'facebook_connect' AND `version` = '1.2.0';

