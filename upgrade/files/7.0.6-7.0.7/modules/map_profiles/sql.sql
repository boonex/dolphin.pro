
DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_map';

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_map', 'homepage', '_bx_map_location', 'home_location.png', 2, '', '', '', @iMaxOrderHomepage, 1),
('bx_map', 'profile', '_bx_map_location', '', 2, '', '', '', @iMaxOrderProfile, 1);


UPDATE `sys_modules` SET `version` = '1.0.7' WHERE `uri` = 'map_profiles' AND `version` = '1.0.6';

