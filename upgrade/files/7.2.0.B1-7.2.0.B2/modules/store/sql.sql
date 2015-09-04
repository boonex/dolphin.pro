
ALTER TABLE `bx_store_products` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`; 

UPDATE `bx_store_products` SET `allow_view_forum_to` = `allow_post_in_forum_to`;

DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'store' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES ('store', 'view_forum', '_bx_store_privacy_view_forum_product', 'c');

