
DELETE FROM `sys_account_custom_stat_elements`  WHERE `Label` = '_bx_groups';
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_groups', '__bx_groups__ (<a href="modules/?r=groups/browse/my&bx_groups_filter=add_group">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'groups' AND `version` = '1.1.0';

