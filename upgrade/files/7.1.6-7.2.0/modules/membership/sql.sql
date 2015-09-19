
SET @sModuleName = 'Membership';

DELETE FROM `sys_page_compose` WHERE `Page` = 'join' AND `Caption` = '_membership_bcaption_purchase_level';
SET @iMaxOrder = (SELECT MAX(`Order`) + 1 FROM `sys_page_compose` WHERE `Page` = 'join' AND `Column` = 2);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('join', '1140px', 'Select Level', '_membership_bcaption_purchase_level', 2, IFNULL(@iMaxOrder, 0), 'PHP', 'return BxDolService::call(\'membership\', \'select_level_block\');', 1, 100, 'non', 413);

UPDATE `sys_page_compose` SET `Caption` = '_membership_bcaption_select_level' WHERE `Page` = 'bx_mbp_my_membership' AND `Desc` = 'Available Levels' AND `Caption` = '_membership_bcaption_levels';

DELETE FROM `sys_cron_jobs` WHERE `name` = @sModuleName;
INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
(@sModuleName, '* * * * *', 'BxMbpCron', 'modules/boonex/membership/classes/BxMbpCron.php', '');

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'membership' AND `version` = '1.1.6';

