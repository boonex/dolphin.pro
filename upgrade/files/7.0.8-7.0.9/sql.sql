
-- ================ can be safely applied multiple times ================ 

DELETE FROM `sys_options` WHERE `Name` = 'sys_ps_group_1_title' OR `Name` = 'sys_ps_group_2_title' OR `Name` = 'sys_ps_group_3_title' OR `Name` = 'sys_ps_group_4_title' OR `Name` = 'sys_ps_group_5_title' OR `Name` = 'sys_ps_group_6_title' OR `Name` = 'sys_ps_group_7_title';


DELETE FROM  `sys_shared_sites` WHERE `Name` = 'facebook' OR `Name` = 'twitter';
INSERT INTO `sys_shared_sites` (`Name`, `URL`, `Icon`) VALUES
('facebook', 'http://www.facebook.com/sharer/sharer.php?u=', 'facebook.png'),
('twitter', 'https://twitter.com/share?url=', 'twitter.png');


UPDATE `sys_menu_mobile` SET `action` = 5 WHERE `type` = 'system' AND `page` = 'profile' AND `title` = '_sys_mobile_profile_info' AND `action` = 8;


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.0.9', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.0.9';

