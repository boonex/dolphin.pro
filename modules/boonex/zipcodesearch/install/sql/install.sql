
-- create table
CREATE TABLE IF NOT EXISTS `bx_zip_countries_geonames` (
  `ISO2` varchar(2) NOT NULL default '',
  PRIMARY KEY  (`ISO2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bx_zip_countries_google` (
  `ISO2` varchar(2) NOT NULL default '',
  PRIMARY KEY  (`ISO2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `bx_zip_countries_google` (`ISO2`) VALUES
('AR'), ('AT'), ('AU'), ('BB'), ('BE'), ('BR'),
('CA'), ('CH'), ('CL'), ('CN'), ('CZ'), ('DE'),
('DK'), ('EE'), ('EG'), ('ES'), ('FI'), ('FR'),
('GB'), ('HK'), ('HR'), ('HU'), ('IL'), ('IN'),
('IT'), ('JP'), ('KR'), ('LI'), ('LT'), ('LU'),
('LV'), ('MO'), ('MY'), ('NL'), ('NO'), ('NZ'),
('PL'), ('PT'), ('RU'), ('SE'), ('SG'), ('SI'),
('SK'), ('TW'), ('US'); 

-- permalink
INSERT IGNORE  INTO `sys_permalinks` (`id`, `standard`, `permalink`, `check`) VALUES 
(NULL, 'modules/?r=zipcodesearch/', 'm/zipcodesearch/', 'bx_zip_permalinks');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_zip', '_bx_zip_admin_menu', '{siteUrl}modules/?r=zipcodesearch/administration/', 'ZIP Code Search', 'search', @iMax+1);

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('ZIP Code Search', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_zip_permalinks', 'on', 26, 'Enable friendly permalinks in ZIP Code Search', 'checkbox', '', '', '10', ''),
('bx_zip_enabled', '', @iCategId, 'Enable ZIP code search', 'checkbox', '', '', '12', ''),
('bx_zip_mode', 'Geonames', @iCategId, 'Geocoding (select Google if BoonEx ''World Map'' module is installed)', 'select', '', '', '14', 'Google,Geonames'),
('bx_zip_geonames_username', '', @iCategId, 'Geonames username', 'digit', '', '', '16', '');

