
DELETE FROM `sys_options` WHERE `Name` = 'bx_zip_geonames_username';

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'ZIP Code Search' LIMIT 1);

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_zip_geonames_username', '', @iCategId, 'Geonames username', 'digit', '', '', '16', '');

UPDATE `sys_options` SET `order_in_kateg` = '10' WHERE `Name` = 'bx_zip_permalinks';
UPDATE `sys_options` SET `order_in_kateg` = '12' WHERE `Name` = 'bx_zip_enabled';
UPDATE `sys_options` SET `order_in_kateg` = '14' WHERE `Name` = 'bx_zip_mode';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'zipcodesearch' AND `version` = '1.1.6';

