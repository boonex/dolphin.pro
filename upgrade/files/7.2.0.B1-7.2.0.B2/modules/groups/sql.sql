

ALTER TABLE `bx_groups_main` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`;

ALTER TABLE  `bx_groups_shoutbox` CHANGE  `Message`  `Message` BLOB NOT NULL;


UPDATE `bx_groups_main` SET `allow_view_forum_to` = `allow_post_in_forum_to`;

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'groups' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('groups', 'view_forum', '_bx_groups_privacy_view_forum', 'f');


UPDATE `sys_page_compose_pages` SET `Title` = 'Group View' WHERE `Name` = 'bx_groups_view';

UPDATE `sys_page_compose` SET `Desc` = 'Group''s info block' WHERE `Desc` = 'users''s info block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s actions block' WHERE `Desc` = 'users''s actions block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s rate block' WHERE `Desc` = 'users''s rate block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s social sharing block' WHERE `Desc` = 'users''s social sharing block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s fans block' WHERE `Desc` = 'users''s fans block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s unconfirmed fans block' WHERE `Desc` = 'users''s unconfirmed fans block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s Location' WHERE `Desc` = 'users''s Location' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s chat' WHERE `Desc` = 'users''s chat' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s description block' WHERE `Desc` = 'users''s description block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s photo block' WHERE `Desc` = 'users''s photo block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s videos block' WHERE `Desc` = 'users''s videos block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s sounds block' WHERE `Desc` = 'users''s sounds block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s files block' WHERE `Desc` = 'users''s files block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s comments block' WHERE `Desc` = 'users''s comments block' AND `Page` = 'bx_groups_view';
UPDATE `sys_page_compose` SET `Desc` = 'Group''s forum feed' WHERE `Desc` = 'users''s forum feed' AND `Page` = 'bx_groups_view';

UPDATE `sys_page_compose` SET `Desc` = 'Latest Featured Group' WHERE `Desc` = 'Latest Featured users' AND `Page` = 'bx_groups_main';


UPDATE `sys_options` SET `desc` = 'Allow group admin to edit and delete any comment' WHERE `Name` = 'bx_groups_author_comments_admin';
UPDATE `sys_options` SET `desc` = 'Number of fans to show on group view page' WHERE `Name` = 'bx_groups_perpage_view_fans';


