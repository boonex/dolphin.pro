SET @iTMParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name`='Board' LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Name`='Board' OR `Parent`=@iTMParentId;

DELETE FROM `sys_acl_actions` WHERE `Name`='use board' LIMIT 1;

DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_board';

DELETE FROM `sys_options` WHERE `Name`='permalinks_module_board';

DELETE FROM `sys_categories` WHERE `Category`='Board' AND `Type`='bx_photos' AND `Owner`=0;

DELETE FROM `sys_objects_actions` WHERE `Script`='window.open(''m/board/index/{ID}'',''_self'')';

-- chart
DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_boards';

