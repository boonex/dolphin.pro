DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Eval` = 'return BxDolService::call(''messenger'', ''get_action_link'', array({member_id}, {ID}));';

DELETE FROM `sys_injections` WHERE `name`='messenger_invitation';

DELETE FROM `sys_acl_actions` WHERE `Name`='use messenger';
