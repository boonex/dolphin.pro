
DELETE FROM `sys_options` WHERE `Name` = 'sys_cron_time';

SET @iCatHidden = 0;
INSERT INTO `sys_options` VALUES
('sys_cron_time', '', @iCatHidden, 'Last cron execution time', 'digit', '', '', 54, '');

-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.3.5' WHERE `Name` = 'sys_tmp_version';

