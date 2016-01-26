
DELETE FROM `sys_options` WHERE `Name` IN('mbp_enable_standard_for_paid_join', 'mbp_enable_captcha_for_paid_join');
SET @iCategoryId = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'mbp_disable_free_join');
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('mbp_enable_standard_for_paid_join', 'on', @iCategoryId, 'Enable Standard membership level on "Pay Before Join" form', 'checkbox', '', '', 2, ''),
('mbp_enable_captcha_for_paid_join', 'on', @iCategoryId, 'Enable Captcha on "Pay Before Join" form', 'checkbox', '', '', 3, '');


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'membership' AND `version` = '1.2.1';

