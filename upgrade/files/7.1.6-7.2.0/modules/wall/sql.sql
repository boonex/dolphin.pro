

DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` IN ('Events Outline', 'Home Timeline: Post', 'Home Timeline: View');
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` IN ('Post event to a Timeline', 'View events on a Timeline', 'Profile Timeline: Post', 'Profile Timeline: View');
DELETE FROM `sys_page_compose` WHERE `Page` = 'member' AND `Desc` IN ('View events on a Timeline', 'Friends Timeline: View');

SET @iPCOrderIndex = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='index' AND `Column`='2');
SET @iPCOrderProfile = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='profile' AND `Column`='3');
SET @iPCOrderMember = (SELECT MAX(`Order`)+1 FROM `sys_page_compose` WHERE `Page`='member' AND `Column`='2');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Events Outline', '_wall_pc_view_index_ol', 2, IFNULL(@iPCOrderIndex, 0), 'PHP', 'return BxDolService::call(\'wall\', \'view_block_index_outline\');', 1, 71.9, 'non,memb', 0),
('index', '1140px', 'Home Timeline: Post', '_wall_pc_post_index_tl', 2, IFNULL(@iPCOrderIndex, 0)+1, 'PHP', 'return BxDolService::call(\'wall\', \'post_block_index_timeline\');', 1, 71.9, 'memb', 0),
('index', '1140px', 'Home Timeline: View', '_wall_pc_view_index_tl', 2, IFNULL(@iPCOrderIndex, 0)+2, 'PHP', 'return BxDolService::call(\'wall\', \'view_block_index_timeline\');', 1, 71.9, 'non,memb', 0),

('profile', '1140px', 'Profile Timeline: Post', '_wall_pc_post_profile_tl', 3, IFNULL(@iPCOrderProfile, 0), 'PHP', 'return BxDolService::call(\'wall\', \'post_block_profile_timeline\', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'memb', 0),
('profile', '1140px', 'Profile Timeline: View', '_wall_pc_view_profile_tl', 3, IFNULL(@iPCOrderProfile, 0)+1, 'PHP', 'return BxDolService::call(\'wall\', \'view_block_profile_timeline\', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),

('member', '1140px', 'Friends Timeline: View', '_wall_pc_view_account_tl', 2, IFNULL(@iPCOrderMember, 0), 'PHP', 'return BxDolService::call(\'wall\', \'view_block_account_timeline\', array($this->iMember));', 1, 71.9, 'memb', 0);


UPDATE `sys_menu_admin` SET `icon` = 'clock-o' WHERE `name` = 'bx_wall';


DELETE FROM `sys_options` WHERE `Name` IN ('wall_events_per_page_profile', 'wall_events_per_page_account', 'wall_events_per_page_index', 'wall_events_per_page_index_ol', 'wall_events_per_page_index_tl', 'wall_events_per_page_profile_tl', 'wall_events_per_page_account_tl', 'wall_events_chars_display_max');

SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Timeline' LIMIT 1);

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('wall_events_per_page_index_ol', '20', @iCategoryId, 'Number of events are displayed in Outline on Home page', 'digit', '', '', 3, ''),
('wall_events_per_page_index_tl', '10', @iCategoryId, 'Number of events are displayed in Timeline on Home page', 'digit', '', '', 4, ''),
('wall_events_per_page_profile_tl', '10', @iCategoryId, 'Number of events are displayed in Timeline on Profile page', 'digit', '', '', 5, ''),
('wall_events_per_page_account_tl', '20', @iCategoryId, 'Number of events are displayed in Timeline on Account page', 'digit', '', '', 6, ''),
('wall_events_chars_display_max', '500', @iCategoryId, 'Max number of displayed character in text post', 'digit', '', '', 7, '');

UPDATE `sys_options` SET `order_in_kateg` = 8 WHERE `Name` = 'wall_rss_length';
UPDATE `sys_options` SET `order_in_kateg` = 9 WHERE `Name` = 'wall_events_hide_timeline';
UPDATE `sys_options` SET `order_in_kateg` = 10 WHERE `Name` = 'wall_events_hide_outline';

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_wall_pc_view_account','_wall_pc_view_index');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_wall_pc_view_account','_wall_pc_view_index');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'wall' AND `version` = '1.1.6';

