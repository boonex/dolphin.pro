

UPDATE `sys_menu_member` SET `Position`='top_extra' WHERE `Name` = 'bx_sites';


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_sites';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Icon` = 'star-empty' AND `Type` = 'bx_sites';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_sites';


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'sites' AND `version` = '1.1.6';

