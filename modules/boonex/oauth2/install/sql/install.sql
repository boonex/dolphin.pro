
CREATE TABLE IF NOT EXISTS `bx_oauth_access_tokens` (
  `access_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`access_token`)
);

CREATE TABLE IF NOT EXISTS `bx_oauth_authorization_codes` (
  `authorization_code` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `redirect_uri` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
);

CREATE TABLE IF NOT EXISTS `bx_oauth_clients` (
  `title` varchar(255) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `client_secret` varchar(80) DEFAULT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  `grant_types` varchar(80) DEFAULT NULL,
  `scope` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`client_id`)
);

CREATE TABLE IF NOT EXISTS `bx_oauth_refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
);

CREATE TABLE IF NOT EXISTS `bx_oauth_scopes` (
  `scope` varchar(255)  DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT NULL
);

INSERT INTO `bx_oauth_scopes` (`scope`, `is_default`) VALUES
('basic', 1),
('service', 0);

-- permalink
INSERT IGNORE  INTO `sys_permalinks` (`id`, `standard`, `permalink`, `check`) VALUES 
(NULL, 'modules/?r=oauth2/', 'm/oauth2/', 'bx_oauth2_permalinks');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_oauth2', '_bx_oauth', '{siteUrl}modules/?r=oauth2/administration/', 'OAuth2 Server', 'globe', @iMax+1);

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('OAuth2 Server', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_oauth2_permalinks', 'on', 26, 'Enable friendly permalinks in OAuth2 Server', 'checkbox', '', '', '0', '');
-- ('bx_oauth2_on', '', @iCategId, 'Enable OAuth2 Server', 'checkbox', '', '', '0', '');
