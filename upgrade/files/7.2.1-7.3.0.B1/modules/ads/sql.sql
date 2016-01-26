

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_ads';
INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '{repostCpt}', 'repeat', '', '{repostScript}', '', 13, 'bx_ads', 0);


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'ads' AND `version` = '1.2.1';

