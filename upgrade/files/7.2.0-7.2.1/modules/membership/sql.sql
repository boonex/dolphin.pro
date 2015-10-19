
SET @sModuleName = 'Membership';



DELETE FROM `sys_options` WHERE `Name` IN('mbp_type', 'mbp_disable_free_join');

SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = @sModuleName);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('mbp_disable_free_join', '', @iCategoryId, 'Disable free join', 'checkbox', '', '', 1, '');



DELETE FROM `sys_menu_admin` WHERE `name` = @sModuleName AND `title` = '_membership_admin_menu_sitem';

SET @iParent = 2;
SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`=@iParent);
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParent, @sModuleName, '_membership_admin_menu_sitem', '{siteUrl}modules/?r=membership/admin/', 'For managing Memberships', 'certificate', '', '', @iOrder+1);



DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'bx_mbp_join';

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES 
('bx_mbp_join', 'Membership Join', @iPCPOrder + 1);



DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_mbp_join' OR (`Page` = 'join' AND `Desc` = 'Select Level');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('bx_mbp_join', '1140px', 'Select Level', '_membership_bcaption_purchase_level', 2, 1, 'Select', '', 1, 100, 'non', 0);



SET @iHandlerId = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name`=@sModuleName LIMIT 1);
DELETE FROM `sys_alerts_handlers` WHERE `name`=@sModuleName LIMIT 1;
DELETE FROM `sys_alerts` WHERE `handler_id`=@iHandlerId;

INSERT INTO `sys_alerts_handlers`(`name`, `class`, `file`, `eval`) VALUES 
(@sModuleName, 'BxMbpResponse', 'modules/boonex/membership/classes/BxMbpResponse.php', '');
SET @iHandlerId = LAST_INSERT_ID();

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('system', 'page_output', @iHandlerId),
('profile', 'show_join_form', @iHandlerId);



-- update module version

UPDATE `sys_modules` SET `version` = '1.2.1' WHERE `uri` = 'membership' AND `version` = '1.2.0';

