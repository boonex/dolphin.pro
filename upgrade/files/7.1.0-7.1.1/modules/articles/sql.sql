
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_articles' AND `Caption` = '{share_articles_title}';
INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{share_articles_title}', 'share', '', '{share_articles_script}', '', 3, 'bx_articles', 0);


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'articles' AND `version` = '1.1.0';

