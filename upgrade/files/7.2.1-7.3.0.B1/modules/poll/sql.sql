

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_poll';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{repostCpt}', 'repeat', '', '{repostScript}', '', 6, 'bx_poll', 0);


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'poll' AND `version` = '1.2.1';

