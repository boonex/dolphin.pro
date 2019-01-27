
DROP TABLE IF EXISTS `bx_dolphcon_accounts`;

-- Email template

DELETE FROM `sys_email_templates` WHERE `Name` = 't_bx_dolphcon_password_generated';

-- Auth objects

DELETE FROM `sys_objects_auths` WHERE `Name` = 'bx_dolphcon';

-- Alerts

SET @iHandlerId := (SELECT `id` FROM `sys_alerts_handlers`  WHERE `name`  =  'bx_dolphcon');

DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandlerId;
DELETE FROM `sys_alerts` WHERE `handler_id` =  @iHandlerId;

-- Options

SET @iKategId = (SELECT `id` FROM `sys_options_cats` WHERE `name` = 'Dolphin connect' LIMIT 1);

DELETE FROM `sys_options_cats` WHERE `id` = @iKategId;
DELETE FROM `sys_options` WHERE `kateg` = @iKategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_dolphcon_permalinks' AND `kateg` = 26;

-- Menu Admin

DELETE FROM `sys_menu_admin` WHERE `title` = '_bx_dolphcon';

-- Permalinks

DELETE FROM `sys_permalinks` WHERE `standard`  = 'modules/?r=dolphcon/';

-- Chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_dolphcon';

-- Export

DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_dolphcon';

