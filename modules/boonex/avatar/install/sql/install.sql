-- create tables
CREATE TABLE IF NOT EXISTS `[db_prefix]images` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- page compose pages
SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_avatar_main', 'Avatar', @iMaxOrder+2);

-- page compose blocks
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_avatar_main', '1140px', 'Tight block', '_bx_ava_block_tight', '2', '0', 'Tight', '', '1', '28.1', 'non,memb', '0'),
    ('bx_avatar_main', '1140px', 'Wide block', '_bx_ava_block_wide', '1', '0', 'Wide', '', '1', '71.9', 'non,memb', '0');

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
    ('pedit', '1140px', 'Manage Avatars', '_bx_ava_manage_avatars', 2, IFNULL(@iMaxOrder, 0), 'PHP', 'return BxDolService::call(''avatar'', ''manage_avatars'', array ((int)$_REQUEST[''ID'']));', 1, 28.1, 'memb', 0);

-- permalinkU
INSERT INTO `sys_permalinks` VALUES (NULL, 'modules/?r=avatar/', 'm/avatar/', 'bx_avatar_permalinks');

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Avatar', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_avatar_permalinks', 'on', 26, 'Enable friendly permalinks in avatars', 'checkbox', '', '', '0', ''),
('bx_avatar_quality', '90', @iCategId, 'JPEG quality of avatars (1-100)', 'digit', '', '', '0', ''),
('bx_avatar_site_avatars', 'on', @iCategId, 'Enable site avatars', 'checkbox', '', '', '0', '');

-- member menu
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES 
(118, 'Avatar', '_bx_ava_avatar', 'modules/?r=avatar/', 1, 'memb', '', '', '', 3, 1, 1, 1, 1, 'custom', '', '', 0, '');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_avatar', '_bx_ava_avatar', '{siteUrl}modules/?r=avatar/administration/', 'Avatar module by BoonEx', 'user', @iMax+1);

-- actions menu
SET @iOrderActions = (SELECT `Order` + 1 FROM `sys_objects_actions` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_photos', '{TitleAvatar}', 'user', '{evalResult}&make_avatar_from_shared_photo={ID}', '', 'bx_import(''BxDolPermalinks'');\r\n$o = new BxDolPermalinks();\r\nreturn $o->permalink(''modules/?r=avatar/'');', @iOrderActions);

-- membership actions
SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` VALUES (NULL, 'avatar upload', NULL);
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` VALUES (NULL, 'avatar edit any', NULL);
INSERT INTO `sys_acl_actions` VALUES (NULL, 'avatar delete any', NULL);

-- alert handlers
INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_avatar', 'BxAvaProfileDeleteResponse', 'modules/boonex/avatar/classes/BxAvaProfileDeleteResponse.php', '');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'profile', 'delete', @iHandler);

-- export
SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_avatar', '_bx_ava_avatar', 'BxAvaExport', 'modules/boonex/avatar/classes/BxAvaExport.php', @iMaxOrderExports, 1);

