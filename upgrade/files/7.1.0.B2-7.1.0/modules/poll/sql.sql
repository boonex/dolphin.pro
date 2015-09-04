
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_poll' AND `Caption` = '_bx_poll_delete';
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_poll' AND `Caption` = '{del_poll_title}';

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{del_poll_title}', 'remove', '{del_poll_url}', '{del_poll_script}', '', 2, 'bx_poll', 0);

