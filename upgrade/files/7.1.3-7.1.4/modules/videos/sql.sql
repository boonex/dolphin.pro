

UPDATE `sys_options` SET `VALUE` = 'avi flv mpg wmv mp4 m4v mov divx xvid mpeg 3gp' WHERE `Name` = 'bx_videos_allowed_exts';


DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_videos' AND `Caption` = '{downloadCpt}';
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_videos', '{downloadCpt}', 'download-alt', '{moduleUrl}get_file/{ID}', '', '', 8);

UPDATE `sys_objects_actions` SET `Order` = 1 WHERE `Type` = 'bx_videos_title' AND `Icon` = 'plus';
UPDATE `sys_objects_actions` SET `Order` = 2 WHERE `Type` = 'bx_videos_title' AND `Icon` = 'film';



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'videos' AND `version` = '1.1.3';

