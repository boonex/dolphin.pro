
DELETE FROM `sys_account_custom_stat_elements` WHERE  `Label` = '_bx_sites';
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_sites', '__bx_sites__ (<a href="modules/?r=sites/browse/my/add">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'sites' AND `version` = '1.1.0';

