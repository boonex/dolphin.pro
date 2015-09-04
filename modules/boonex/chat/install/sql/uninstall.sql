SET @iTMParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name`='Chat' LIMIT 1);
DELETE FROM `sys_menu_top` WHERE `Name`='Chat' OR `Parent`=@iTMParentId;

DELETE FROM `sys_acl_actions` WHERE `Name`='use chat' LIMIT 1;

DELETE FROM `sys_permalinks` WHERE `check`='permalinks_module_chat';

DELETE FROM `sys_options` WHERE `Name`='permalinks_module_chat';