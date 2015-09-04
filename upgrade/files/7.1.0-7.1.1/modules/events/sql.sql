
DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_events';
INSERT INTO `sys_account_custom_stat_elements` VALUES(NULL, '_bx_events', '__bx_events__ (<a href="modules/?r=events/browse/my&bx_events_filter=add_event">__l_add__</a>)');


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'events' AND `version` = '1.1.0';

