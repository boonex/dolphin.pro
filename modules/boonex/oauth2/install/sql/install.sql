
-- permalink
INSERT IGNORE  INTO `sys_permalinks` (`id`, `standard`, `permalink`, `check`) VALUES 
(NULL, 'modules/?r=oauth2/', 'm/oauth2/', 'bx_oauth2_permalinks');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_oauth2', '_bx_oauth', '{siteUrl}modules/?r=oauth2/administration/', 'OAuth2 Server', 'envelope-o', @iMax+1);

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('OAuth2 Server', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_oauth2_permalinks', 'on', 26, 'Enable friendly permalinks in OAuth2 Server', 'checkbox', '', '', '0', '');
-- ('bx_oauth2_on', '', @iCategId, 'Enable OAuth2 Server', 'checkbox', '', '', '0', '');
