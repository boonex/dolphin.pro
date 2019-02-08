

SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Events' LIMIT 1);

DELETE FROM `sys_options` WHERE `Name` = 'bx_events_only_upcoming_events_on_map';

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_events_only_upcoming_events_on_map', '', @iCategId, 'Display only upcoming and current events on the map', 'checkbox', '', '', '0', '');


SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_events_set_param' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_set_param', '', '', 'if (''bx_events_only_upcoming_events_on_map'' == $this->aExtras[''name'']) BxDolService::call(''events'', ''set_upcoming_events_on_map'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'system', 'set_param', @iHandler);


DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_events';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_events', '_bx_events', 'BxEventsExport', 'modules/boonex/events/classes/BxEventsExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'events' AND `version` = '1.3.5';

