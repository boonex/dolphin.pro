

DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Func` = 'ForumFeed';
SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Column` = '1' ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('bx_events_view', '1140px', 'Event''s forum feed', '_sys_block_title_forum_feed', '1', @iMaxOrder, 'ForumFeed', '', '1', 71.9, 'non,memb', '0');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.2' WHERE `uri` = 'events' AND `version` = '1.1.1';

