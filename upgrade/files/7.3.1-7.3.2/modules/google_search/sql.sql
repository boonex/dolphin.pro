
DELETE FROM `sys_options` WHERE `Name` IN('bx_gsearch_block_tabbed', 'bx_gsearch_block_images', 'bx_gsearch_separate_tabbed', 'bx_gsearch_separate_images');

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Google Search' LIMIT 1);
INSERT IGNORE INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_gsearch_id', '', @iCategId, 'Search engine ID', 'digit', '', '', '10', '');

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'google_search' AND `version` = '1.3.1';

