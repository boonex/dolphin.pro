

ALTER TABLE `bx_events_main` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`; 

ALTER TABLE  `bx_events_shoutbox` CHANGE  `Message`  `Message` BLOB NOT NULL;


UPDATE `bx_events_main` SET `allow_view_forum_to` = 3;

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'events' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES ('events', 'view_forum', '_bx_events_privacy_view_forum', 'p');


