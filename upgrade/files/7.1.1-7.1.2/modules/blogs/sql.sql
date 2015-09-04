
UPDATE `sys_objects_actions` SET `Caption` = '{TitleShare}' WHERE `Type` = 'bx_blogs' AND `Caption` = '_Share';

-- update module version

UPDATE `sys_modules` SET `version` = '1.1.2' WHERE `uri` = 'blogs' AND `version` = '1.1.1';

