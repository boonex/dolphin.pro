

DELETE FROM `sys_options` WHERE 'bx_spy_allowed_rows';
SET @iKatId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Spy');
INSERT IGNORE INTO `sys_options` SET `Name` = 'bx_spy_keep_rows_days', `VALUE` = '30', `kateg` = @iKatId, `desc` = 'Number of days to keep records', `Type` = 'digit';


UPDATE `sys_modules` SET `version` = '1.0.7' WHERE `uri` = 'spy' AND `version` = '1.0.6';

