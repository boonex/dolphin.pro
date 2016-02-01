

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_events';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{repostCpt}', 'repeat', '', '{repostScript}', '', 14, 'bx_events');


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'events' AND `version` = '1.2.1';

