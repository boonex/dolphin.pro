
-- create tables

CREATE TABLE `[db_prefix]parts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `part` varchar(16) NOT NULL,
  `title` varchar(64) NOT NULL,
  `title_singular` varchar(64) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `icon_site` varchar(255) NOT NULL,
  `join_table` varchar(64) NOT NULL,
  `join_where` varchar(255) NOT NULL,
  `join_field_id` varchar(64) NOT NULL,
  `join_field_country` varchar(64) NOT NULL,
  `join_field_city` varchar(64) NOT NULL,
  `join_field_state` varchar(64) NOT NULL,
  `join_field_zip` varchar(64) NOT NULL,
  `join_field_address` varchar(64) NOT NULL,
  `join_field_latitude` varchar(64) NOT NULL,
  `join_field_longitude` varchar(64) NOT NULL,
  `join_field_title` varchar(64) NOT NULL,
  `join_field_uri` varchar(64) NOT NULL,
  `join_field_author` varchar(64) NOT NULL,
  `join_field_privacy` varchar(64) NOT NULL,
  `permalink` varchar(64) NOT NULL,
  `enabled` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `part` (`part`)
) DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `[db_prefix]locations` (
  `id` int(10) unsigned NOT NULL,
  `part` varchar(16) NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `zoom` tinyint(4) NOT NULL default '-1',
  `type` char(16) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `privacy` varchar(64) NOT NULL,
  `failed` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`,`part`),
  KEY `lat` (`lat`),
  KEY `lng` (`lng`)
) DEFAULT CHARSET=utf8;

-- page compose 
SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_wmap', 'World Map', @iMaxOrder);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_wmap_edit', 'World Map Edit', @iMaxOrder + 1);

SET @iMaxOrderProfile = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('bx_wmap', '1140px', 'Map', '_bx_wmap_block_title_block_map', '1', '0', 'Map', '', '1', 100, 'non,memb', '0'),
('bx_wmap_edit', '1140px', 'Map', '_bx_wmap_block_title_block_map_edit', '1', '0', 'MapEdit', '', '1', 100, 'non,memb', '0'),
('index', '1140px', 'Map', '_bx_wmap_block_title_block_map_homepage', 0, 0, 'PHP', 'return BxDolService::call(''wmap'', ''homepage_block'');', 1, 71.9, 'non,memb', 0),
('profile', '1140px', 'Location', '_Location', 2, IFNULL(@iMaxOrderProfile, 0), 'PHP', 'return BxDolService::call(''wmap'', ''location_block'', array(''profiles'', $this->oProfileGen->_iProfileID));', 1, 28.1, 'non,memb', 0);

-- permalinks
INSERT INTO `sys_permalinks` VALUES (NULL, 'modules/?r=wmap/', 'm/wmap/', 'bx_wmap_permalinks');

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('World Map General', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
SET @sMapKey = (SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'bx_map_key' LIMIT 1);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_wmap_key', @sMapKey, @iCategId, 'Google Maps API key', 'digit', '', '', '0', ''),
('bx_wmap_permalinks', 'on', 26, 'Enable friendly permalinks in World Map module', 'checkbox', '', '', '0', '');

-- settings default map locations

INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('World Map Hidden', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES

('bx_wmap_homepage_lat', '20', @iCategId, 'Homepage map latitude', 'digit', '', '', '0', ''),
('bx_wmap_homepage_lng', '70', @iCategId, 'Homepage map longitude', 'digit', '', '', '0', ''),
('bx_wmap_homepage_zoom', '1', @iCategId, 'Homepage map zoom', 'digit', '', '', '0', ''),
('bx_wmap_homepage_map_type', 'normal', @iCategId, 'Homepage map type', 'digit', '', '', '0', ''),

('bx_wmap_separate_lat', '20', @iCategId, 'Separate page map latitude', 'digit', '', '', '0', ''),
('bx_wmap_separate_lng', '35', @iCategId, 'Separate page map longitude', 'digit', '', '', '0', ''),
('bx_wmap_separate_zoom', '2', @iCategId, 'Separate page map zoom', 'digit', '', '', '0', ''),
('bx_wmap_separate_map_type', 'normal', @iCategId, 'Separate page map type', 'digit', '', '', '0', '');


-- settings default map controls

INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('World Map Homepage', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_wmap_homepage_control_type', 'small', @iCategId, 'Map control type', 'select', '', '', '0', 'none,small,large'),
('bx_wmap_homepage_is_type_control', 'on', @iCategId, 'Display map type controls', 'checkbox', '', '', '0', ''),
('bx_wmap_homepage_is_scale_control', '', @iCategId, 'Display map scale control', 'checkbox', '', '', '0', ''),
('bx_wmap_homepage_is_overview_control', '', @iCategId, 'Display map overview control', 'checkbox', '', '', '0', ''),
('bx_wmap_homepage_is_map_dragable', 'on', @iCategId, 'Is map dragable?', 'checkbox', '', '', '0', '');

INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('World Map Separate', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_wmap_separate_control_type', 'large', @iCategId, 'Map control type', 'select', '', '', '0', 'none,small,large'),
('bx_wmap_separate_is_type_control', 'on', @iCategId, 'Display map type controls', 'checkbox', '', '', '0', ''),
('bx_wmap_separate_is_scale_control', 'on', @iCategId, 'Display map scale control', 'checkbox', '', '', '0', ''),
('bx_wmap_separate_is_overview_control', 'on', @iCategId, 'Display map overview control', 'checkbox', '', '', '0', ''),
('bx_wmap_separate_is_map_dragable', 'on', @iCategId, 'Is map dragable?', 'checkbox', '', '', '0', '');


-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_wmap', '_bx_wmap', '{siteUrl}modules/?r=wmap/administration/', 'World Map module by BoonEx', 'map-marker', @iMax+1);

-- top menu
SET @iCatOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 138 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES
(138, 'World Map', '_bx_wmap_search_submenu', 'modules/?r=wmap/home', IFNULL(@iCatOrder, 0), 'non,memb', '', '', '', 1, 1, 1, 'custom', '', '', 0, '');

-- mobile
SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
SET @iMaxOrderSearch = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'search');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_wmap', 'homepage', '_bx_wmap_location', 'home_location.png', 2, '', '', '', @iMaxOrderHomepage, 1),
('bx_wmap', 'profile', '_bx_wmap_location', '', 2, '', '', '', @iMaxOrderProfile, 1),
('bx_wmap', 'search', '_bx_wmap_search_near_me', '', 32, '', '', '', @iMaxOrderSearch, 1);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_wmap', '_sys_module_wmap', 'BxWmapExport', 'modules/boonex/world_map/classes/BxWmapExport.php', @iMaxOrderExports, 1);

