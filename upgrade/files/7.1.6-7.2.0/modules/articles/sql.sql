
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Type` = 'bx_articles' AND `Icon` = 'share';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'articles' AND `version` = '1.1.6';

