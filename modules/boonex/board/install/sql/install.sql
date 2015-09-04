SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'Board', '_board_top_menu_item', 'modules/?r=board/home/|modules/?r=board/|modules/?r=photos/browse/category/Board|m/photos/browse/category/Board', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'square-o', 0, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'BoardHome', '_board_home_top_menu_sitem', 'modules/?r=board/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'BoardRules', '_board_rules_top_menu_sitem', 'modules/?r=board/rules/', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'BoardSaved', '_board_saved_top_menu_sitem', 'modules/?r=photos/browse/category/Board', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;
INSERT INTO `sys_acl_actions` SET `Name`='use board';
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=board/', 'm/board/', 'permalinks_module_board');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES('permalinks_module_board', 'on', 26, 'Enable friendly board permalink', 'checkbox', '', '', 0);

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES('Board', 0, 'bx_photos', 0, 'active');

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Script`, `Eval`, `Order`, `Type`) VALUES('{evalResult}', 'square-o', 'window.open(''m/board/index/{ID}'',''_self'')', 'if ({Owner} == {iViewer} AND strtolower(''{Tags}'') == ''board'')\r\nreturn _t( ''_board_action_paint'' );\r\nelse\r\nreturn null;', 4, 'bx_photos');

-- chart
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_boards', '_board_chart', 'RayBoardBoards', 'When', '', '', 1, @iMaxOrderCharts);

