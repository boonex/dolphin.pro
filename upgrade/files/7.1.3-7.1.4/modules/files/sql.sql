

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_files' AND `Icon` = 'download-alt';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_files', '{downloadCpt}', 'download-alt', '{moduleUrl}get_file/{ID}', '', '', 4);


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'files' AND `version` = '1.1.3';

