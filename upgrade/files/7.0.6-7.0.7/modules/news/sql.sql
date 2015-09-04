
DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_news';

SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_news', 'homepage', '_news_bcaption_view_main', '{site_url}modules/boonex/news/templates/base/images/icons/mobile_icon.png', 100, '{site_url}modules/?r=news/mobile_latest_news/', '', '', @iMaxOrderHomepage, 1);


UPDATE `sys_modules` SET `version` = '1.0.7' WHERE `uri` = 'news' AND `version` = '1.0.6';

