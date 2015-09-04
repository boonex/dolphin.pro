
SET @iGlCategID = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Blogs');

DELETE FROM `sys_options` WHERE `Name` = 'max_blogs_on_profile' OR `Name` = 'max_blogs_on_index';

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES
('max_blogs_on_profile', '10', @iGlCategID, 'Number of blog posts on profile homepage', 'digit', '', '', 4),
('max_blogs_on_index', '3', @iGlCategID, 'Number of blog posts on site''s homepage', 'digit', '', '', 5);

UPDATE `sys_options` SET `desc` = 'Number of blog posts on a page' WHERE `Name` = 'blog_step';
UPDATE `sys_options` SET `VALUE` = '10', `desc` = 'Number of blog posts on Blogs homepage' WHERE `Name` = 'max_blogs_on_home';

UPDATE `sys_options` SET `order_in_kateg` = 6 WHERE `Name` = 'max_blog_preview';
UPDATE `sys_options` SET `order_in_kateg` = 7 WHERE `Name` = 'bx_blogs_iconsize';
UPDATE `sys_options` SET `order_in_kateg` = 8 WHERE `Name` = 'bx_blogs_thumbsize';
UPDATE `sys_options` SET `order_in_kateg` = 9 WHERE `Name` = 'bx_blogs_bigthumbsize';
UPDATE `sys_options` SET `order_in_kateg` = 10 WHERE `Name` = 'bx_blogs_imagesize';
UPDATE `sys_options` SET `order_in_kateg` = 11 WHERE `Name` = 'category_auto_app_bx_blogs';

