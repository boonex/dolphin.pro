

DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_album_view' AND `Func` = 'Info';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('bx_files_album_view', '1140px', '', '', 3, 1, 1, 28.1, 0, 'non,memb', '_bx_files_info_album', 'Info');

UPDATE `sys_page_compose` SET `Order` = 2 WHERE `Page` = 'bx_files_album_view' AND `Func` = 'Actions' AND `Order` = 1 AND `Column` = 3;

