
DELETE FROM `sys_page_compose` WHERE `Page` = 'news_single' AND `Func` = 'Info';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('news_single', '1140px', 'News info', '_news_bcaption_view_info', 2, 0, 'Info', '', 1, 28.1, 'non,memb', 0);

UPDATE `sys_page_compose` SET `Order` = 1 WHERE `Order` = 0 AND `Page` = 'news_single' AND `Func` = 'Action' AND `Column` = 2;
UPDATE `sys_page_compose` SET `Order` = 2 WHERE `Order` = 1 AND `Page` = 'news_single' AND `Func` = 'Vote' AND `Column` = 2;
UPDATE `sys_page_compose` SET `Order` = 3 WHERE `Order` = 2 AND `Page` = 'news_single' AND `Func` = 'SocialSharing' AND `Column` = 2;

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.5' WHERE `uri` = 'news' AND `version` = '1.3.4';

