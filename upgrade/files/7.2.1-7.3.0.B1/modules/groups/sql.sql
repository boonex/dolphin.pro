

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_groups';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{repostCpt}', 'repeat', '', '{repostScript}', '', 15, 'bx_groups');


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'groups' AND `version` = '1.2.1';

