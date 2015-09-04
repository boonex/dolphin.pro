

DELETE FROM `sys_menu_mobile` WHERE `type` = '[db_prefix]';

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
SET @iMaxOrderProfile = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'profile');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('[db_prefix]', 'homepage', '_bx_videos', 'home_videos.png', 8, '', '', '',  @iMaxOrderHomepage, 1),
('[db_prefix]', 'profile', '_bx_videos', '', 8, '', 'return BxDolXMLRPCMedia::_getMediaCount(''video'', ''{profile_id}'', ''{member_id}'')', '', @iMaxOrderProfile, 1);


UPDATE `sys_modules` SET `version` = '1.0.7' WHERE `uri` = 'videos' AND `version` = '1.0.6';

