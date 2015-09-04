INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES ('{evalResult}', 'comments-o', '', 'window.open( ''modules/boonex/messenger/popup.php?rspId={ID}'' , ''Messenger'', ''width=550,height=500,toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=1'', 0);', 'return BxDolService::call(''messenger'', ''get_action_link'', array({member_id}, {ID}));', 12, 'Profile');

INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES ('messenger_invitation', '0', 'injection_header', 'php', 'return BxDolService::call(''messenger'', ''get_invitation'');', '0', '1');

SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;
INSERT INTO `sys_acl_actions` SET `Name`='use messenger';
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);
