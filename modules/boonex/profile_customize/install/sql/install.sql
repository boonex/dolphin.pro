
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
  `css_name` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `bx_profile_custom_units` (`name`, `caption`, `css_name`, `type`) VALUES
	('body', 'Page background', 'body', 'background'),
    ('boxtext', 'Font for boxes', '#divUnderCustomization .disignBoxFirst, #divUnderCustomization .boxFirstHeader, #divUnderCustomization .disignBoxFirst a, #divUnderCustomization .bx-def-font-grayed', 'font'),
    ('boxborder', 'Border for boxes', '#divUnderCustomization .disignBoxFirst', 'border');

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
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=profile_customize/', 'm/profile_customize/', 'bx_profile_customize_permalinks');

-- settings
SET @iMaxOrder = (SELECT `order_in_kateg` + 1 FROM `sys_options` WHERE `kateg` = 1 ORDER BY `order_in_kateg` DESC LIMIT 1);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_profile_customize_enable', 'on', 1, 'Enable profile customization', 'checkbox', '', '', @iMaxOrder, '');

SET @iMaxOrder = (SELECT `order_in_kateg` + 1 FROM `sys_options` WHERE `kateg` = 26 ORDER BY `order_in_kateg` DESC LIMIT 1);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_profile_customize_permalinks', 'on', 26, 'Enable friendly permalinks in profile customizer', 'checkbox', '', '', @iMaxOrder, '');

-- action
SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_objects_actions` WHERE `Type` = 'Profile' ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES('{evalResult}', 'magic', '', '$(''#profile_customize_page'').fadeIn(''slow'');', 'return array(''evalResult'' => defined(''BX_PROFILE_PAGE'') && {ID} == {member_id} && getParam(''bx_profile_customize_enable'') == ''on'' ? _t( ''_Customize'' ) : null, ''evalResultCssClassWrapper'' => ''bx-phone-hide'');', @iMaxOrder, 'Profile');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_profile_customize', '_bx_profile_customize', '{siteUrl}modules/?r=profile_customize/administration', 'Profile customizer module by BoonEx', 'magic', @iMax+1);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_profile_customize', '_sys_module_profile_customize', 'BxProfileCustomizeExport', 'modules/boonex/profile_customize/classes/BxProfileCustomizeExport.php', @iMaxOrderExports, 1);

