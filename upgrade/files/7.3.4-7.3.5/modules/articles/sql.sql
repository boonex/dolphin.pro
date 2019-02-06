
DELETE FROM `sys_page_compose` WHERE `Page` = 'articles_single' AND `Func` = 'Info';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('articles_single', '1140px', 'Articles info', '_articles_bcaption_view_info', 2, 0, 'Info', '', 1, 28.1, 'non,memb', 0);

UPDATE `sys_page_compose` SET `Order` = 1 WHERE `Page` = 'articles_single' AND `Func` = 'Action' AND `Column` = 2 AND `Order` = 0;
UPDATE `sys_page_compose` SET `Order` = 2 WHERE `Page` = 'articles_single' AND `Func` = 'Vote' AND `Column` = 2 AND `Order` = 1;
UPDATE `sys_page_compose` SET `Order` = 3 WHERE `Page` = 'articles_single' AND `Func` = 'SocialSharing' AND `Column` = 2 AND `Order` = 2;

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.5' WHERE `uri` = 'articles' AND `version` = '1.3.4';

