

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_photos' AND `Caption` = '{repostCpt}';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_photos', '{repostCpt}', 'repeat', '', '{repostScript}', '', 11);


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'photos' AND `version` = '1.2.1';

