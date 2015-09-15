
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_feedback';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'feedback' AND `version` = '1.1.6';

