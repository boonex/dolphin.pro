

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_sounds' AND `Caption` = '{downloadCpt}';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_sounds', '{downloadCpt}', 'download-alt', '{moduleUrl}get_file/{ID}', '', '', 8);

UPDATE `sys_objects_actions` SET `Order` = 1 WHERE `Type` = 'bx_sounds_title' AND `Icon` = 'plus';
UPDATE `sys_objects_actions` SET `Order` = 2 WHERE `Type` = 'bx_sounds_title' AND `Icon` = 'music';


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_sounds_action_download');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_sounds_action_download');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'sounds' AND `version` = '1.1.3';

