

-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'check-empty' WHERE `Name` = 'Board';


-- objects: actions

UPDATE `sys_objects_actions` SET `Icon` = 'check-empty' WHERE `Type` = 'bx_photos' AND `Eval` LIKE '%_board_action_paint%';


-- chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_boards';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_boards', '_board_chart', 'RayBoardBoards', 'When', '', '', 1, @iMaxOrderCharts);



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'board' AND `version` = '1.0.9';

