
-- main
CREATE TABLE IF NOT EXISTS `[db_prefix]main` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `css` text NOT NULL default '',
  `tmp` text NOT NULL default '',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- units
CREATE TABLE IF NOT EXISTS `[db_prefix]units` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `caption` varchar(100) NOT NULL,
  `css_name` text NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `[db_prefix]units` (`name`, `caption`, `css_name`, `type`) VALUES
('bgbody', 'Page', 'body', 'background'),
('bgheader', 'Header', 'body div.sys_main_logo', 'background'),
('bgmenu', 'Top menu', 'body div.sys_main_menu', 'background'),
('bgbox', 'Box', '.disignBoxFirst', 'background'),
('fontbody', 'Page', 'body, .bx-def-font, .sys_main_logo, .sys_main_menu, .sys_sub_menu, .sys_main_content, .sys_breadcrumb, .sys_copyright', 'font'),
('fontheader', 'Header', 'body div.sys_main_logo, body div.sys_main_logo a', 'font'),
('fontmenu', 'Top menu', 'body div.sys_main_menu, body div.sys_main_menu a, body div.sys_sub_menu, body div.sys_sub_menu a', 'font'),
('fontlink', 'Link', 'a, .sys_main_logo a, .sys_main_menu a, .sys_main_menu table.topMenu a, .sys_sub_menu a, .sys_sub_menu div.subMenu a.sublinks, .sys_main_content a, .sys_breadcrumb a, .sys_copyright a', 'font'),
('fontbox', 'Box', '.disignBoxFirst, .boxFirstHeader, .disignBoxFirst a, .bx-def-font-grayed', 'font'),
('borderbody', 'Page', 'body .bx-def-border', 'border'),
('borderbox', 'Box', '.disignBoxFirst, .sys_main_content .disignBoxFirst, .popup_form_wrapper .disignBoxFirst', 'border'),
('borderform', 'Form', '.form_advanced_wrapper .form_advanced_table, .form_advanced_wrapper .form_advanced_table td', 'border');

-- themes
CREATE TABLE IF NOT EXISTS `[db_prefix]themes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `ownerid` int(10) NOT NULL,
  `css` text NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- images
CREATE TABLE IF NOT EXISTS `[db_prefix]images` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ext` varchar(4) NOT NULL,
  `count` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- permalinks
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=site_customize/', 'm/site_customize/', 'bx_sctr_permalinks');

-- settings
SET @iMaxOrder = (SELECT `order_in_kateg` + 1 FROM `sys_options` WHERE `kateg` = 1 ORDER BY `order_in_kateg` DESC LIMIT 1);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_sctr_enable', 'on', 1, 'Enable site customization', 'checkbox', '', '', @iMaxOrder, '');

SET @iMaxOrder = (SELECT `order_in_kateg` + 1 FROM `sys_options` WHERE `kateg` = 26 ORDER BY `order_in_kateg` DESC LIMIT 1);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_sctr_permalinks', 'on', 26, 'Enable friendly permalinks in site customizer', 'checkbox', '', '', @iMaxOrder, '');

-- member menu
SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_menu_member` WHERE `Position` = 'top_extra' ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_member` (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Order`, `Active`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Parent`, `Bubble`, `Description`) VALUES
('_bx_sctr', 'SiteCustomizer', 'magic', '', '{evalResult}', 'return BxDolService::call(''site_customize'', ''get_customize_button'');', '', @iMaxOrder, 1, 0, 0, '', 'top_extra', 'link', 0, '', '_bx_sctr_mmenu_item_description');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_sctr', '_bx_sctr', '{siteUrl}modules/?r=site_customize/administration', 'Site customizer module by BoonEx', 'magic', @iMax+1);

-- injection
INSERT INTO `sys_injections`(`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES
('bx_sctr_style', 0, 'injection_head', 'php', 'return BxDolService::call(''site_customize'', ''get_site_style'');', '0', '1'),
('bx_sctr_block', 0, 'injection_between_top_menu_content', 'php', 'return BxDolService::call(''site_customize'', ''get_customize_block'');', '0', '1');
