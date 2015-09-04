
DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_forums';
INSERT INTO `sys_account_custom_stat_elements` VALUES (NULL, '_bx_forums', '__mop__ (<a href="__site_url__forum/">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'forum' AND `version` = '1.1.0';

