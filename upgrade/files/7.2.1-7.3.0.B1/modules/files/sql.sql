

UPDATE `sys_options` SET `desc` = 'Maximum size of one file (in Megabytes)' WHERE `Name` = 'bx_files_max_file_size';


DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_files' AND `Caption` = '{repostCpt}';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_files', '{repostCpt}', 'repeat', '', '{repostScript}', '', 9);


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'files' AND `version` = '1.2.1';

