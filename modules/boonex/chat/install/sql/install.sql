SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'Chat', '_chat_top_menu_item', 'modules/?r=chat/home/|modules/?r=chat/|search.php?show=moderators', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'comments-o', 0, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'ChatHome', '_chat_home_top_menu_sitem', 'modules/?r=chat/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'ChatRules', '_chat_rules_top_menu_sitem', 'modules/?r=chat/rules/', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'ChatModerators', '_chat_moderators_top_menu_sitem', 'search.php?show=moderators', 2, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;
INSERT INTO `sys_acl_actions` SET `Name`='use chat';
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=chat/', 'm/chat/', 'permalinks_module_chat');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES('permalinks_module_chat', 'on', 26, 'Enable friendly chat permalink', 'checkbox', '', '', 0);
