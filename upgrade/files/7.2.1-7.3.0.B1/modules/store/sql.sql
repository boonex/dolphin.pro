

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_store';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{repostCpt}', 'repeat', '', '{repostScript}', '', 9, 'bx_store');


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'store' AND `version` = '1.2.1';

