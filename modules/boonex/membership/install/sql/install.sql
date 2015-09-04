SET @sModuleName = 'Membership';

-- options
SET @iCategoryOrder = (SELECT MAX(`menu_order`) FROM `sys_options_cats`) + 1;
INSERT INTO `sys_options_cats` (`name` , `menu_order` ) VALUES (@sModuleName, @iCategoryOrder);
SET @iCategoryId = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('mbp_type', '', @iCategoryId, 'Membership type', 'text', '', '', 0, ''),
('permalinks_module_membership', 'on', 26, 'Enable friendly membership permalink', 'checkbox', '', '', 0, '');

-- menus
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=membership/', 'm/membership/', 'permalinks_module_membership');

INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(118, 'My Membership', '_membership_tmenu_item_my_membership', 'modules/?r=membership/index', 4, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

-- pages and blocks
SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('bx_mbp_my_membership', 'My Membership', @iPCPOrder + 1);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('join', '1140px', 'Select Level', '_membership_bcaption_purchase_level', 2, 1, 'PHP', 'return BxDolService::call(\'membership\', \'select_level_block\');', 1, 100, 'non', 413),
('bx_mbp_my_membership', '1140px', 'My Level', '_membership_bcaption_my_status', 2, 0, 'Current', '', 1, 28.1, 'memb', 0),
('bx_mbp_my_membership', '1140px', 'Available Levels', '_membership_bcaption_select_level', 3, 0, 'Available', '', 1, 71.9, 'memb', 0);

-- cron
INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
(@sModuleName, '* * * * *', 'BxMbpCron', 'modules/boonex/membership/classes/BxMbpCron.php', '');
