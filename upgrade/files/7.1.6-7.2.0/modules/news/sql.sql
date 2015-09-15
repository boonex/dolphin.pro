
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_news';
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_news';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'news' AND `version` = '1.1.6';

