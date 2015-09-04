

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_store';
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_store', '__bx_store__ (<a href="modules/?r=store/browse/my&bx_store_filter=add_product">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'store' AND `version` = '1.1.0';

