
DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'bx_chat_plus';
SET @iMaxOrder = (SELECT `Order` FROM `sys_page_compose_pages` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES ('bx_chat_plus', 'Chat+', @iMaxOrder+1);

DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_chat_plus';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('bx_chat_plus', '1140px', 'Chat+', '_bx_chat_plus_chat', '1', '0', 'PHP', 'return BxDolService::call(''chat_plus'', ''chat_block'', array());', 1, 100, 'non,memb', 0);



DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_chat_plus';
SET @iMaxOrderHomepage = (SELECT MAX(`order`)+1 FROM `sys_menu_mobile` WHERE `page` = 'homepage');
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('bx_chat_plus', 'homepage', '_bx_chat_plus_chat', '{site_url}modules/boonex/chat_plus/templates/base/images/icons/mobile_icon.png', 101, '{xmlrpc_url}r.php?url=modules%3Fr%3Dchat_plus%2Fredirect&user={member_username}&pwd={member_password}', '', '', @iMaxOrderHomepage, 1);

