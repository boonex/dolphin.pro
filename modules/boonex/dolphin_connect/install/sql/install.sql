
CREATE TABLE `bx_dolphcon_accounts` (
  `local_profile` int(10) unsigned NOT NULL,
  `remote_profile` int(10) unsigned NOT NULL,
  PRIMARY KEY (`local_profile`),
  KEY `remote_profile` (`remote_profile`)
) ENGINE=MyISAM;


-- Email template

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('t_bx_dolphcon_password_generated', 'New Password Generated', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <NickName></b>, \r\n\r\n<p>\r\nYour new password - <b><NewPassword></b></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Dolphin Connect password generated', 0);

-- Auth objects

INSERT INTO `sys_objects_auths` (`Name`, `Title`, `Link`, `Icon`) VALUES
('bx_dolphcon', '_bx_dolphcon_auth_title', 'modules/?r=dolphcon/start', 'sign-in');

-- Alerts

INSERT INTO `sys_alerts_handlers` SET `name`  = 'bx_dolphcon', `class` = 'BxDolphConAlerts', `file`  = 'modules/boonex/dolphin_connect/classes/BxDolphConAlerts.php';

SET @iHandlerId := (SELECT `id` FROM `sys_alerts_handlers`  WHERE `name`  =  'bx_dolphcon');

INSERT INTO `sys_alerts` SET `unit` = 'profile', `action` = 'logout', `handler_id` = @iHandlerId;
INSERT INTO `sys_alerts` SET `unit` = 'profile', `action` = 'join', `handler_id` = @iHandlerId;
INSERT INTO `sys_alerts` SET `unit` = 'profile', `action` = 'delete', `handler_id` = @iHandlerId;
INSERT INTO `sys_alerts` SET `unit` = 'system', `action` = 'join_after_payment', `handler_id` = @iHandlerId;

-- Options

SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Dolphin connect', @iMaxOrder);
SET @iKategId = (SELECT LAST_INSERT_ID());


INSERT INTO  `sys_options` SET `Name` = 'bx_dolphcon_api_key', `kateg` = @iKategId, `desc` = 'Dolphin Connect Key', `Type` = 'digit', `VALUE` = '', `order_in_kateg` = 10;

INSERT INTO  `sys_options` SET `Name` = 'bx_dolphcon_connect_secret', `kateg` = @iKategId, `desc` = 'Dolphin Connect Secret', `Type` = 'digit', `VALUE` = '', `order_in_kateg` = 20;

INSERT INTO  `sys_options` SET `Name` = 'bx_dolphcon_connect_url', `kateg` = @iKategId, `desc` = 'Dolphin Connect URL', `Type` = 'digit', `VALUE` = '', `order_in_kateg` = 30;

INSERT INTO  `sys_options` SET `Name` = 'bx_dolphcon_connect_url_rewrite', `kateg` = @iKategId, `desc` = 'Dolphin Connect URL Rewrite', `Type` = 'checkbox', `VALUE` = 'on', `order_in_kateg` = 40;

INSERT INTO  `sys_options` SET `Name` = 'bx_dolphcon_connect_redirect_page', `kateg` = @iKategId, `desc` = 'Redirect page after first sign in', `Type` = 'select', `VALUE` = 'member', `AvailableValues` = 'join,pedit,avatar,member,index', `order_in_kateg` = 50;

INSERT INTO  `sys_options`  (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('bx_dolphcon_permalinks', 'on', 26, 'Enable friendly permalinks in Dolphin connect', 'checkbox', '', '', '0', '');

-- Menu Admin

SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');

INSERT INTO  `sys_menu_admin`  SET `name` = 'Dolphin connect', `title` = '_bx_dolphcon', `url` = '{siteUrl}modules/?r=dolphcon/administration/',  `description` = 'Managing the \'Dolphin connect\' settings', `icon` = 'sign-in', `parent_id` = 2, `order` = @iOrder+1;

-- Permalinks

INSERT INTO  `sys_permalinks` SET `standard` = 'modules/?r=dolphcon/', `permalink` = 'm/dolphcon/', `check` = 'bx_dolphcon_permalinks';
    

-- Chart

SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `column_date`, `column_count`, `type`, `options`, `query`, `active`, `order`) VALUES
('bx_dolphcon', '_bx_dolphcon_chart', '', '', 'DateReg', 0, 1, '', '', 'SELECT {field_date_formatted} AS `period`, COUNT(*) AS {object} FROM `Profiles` INNER JOIN `bx_dolphcon_accounts` ON (`local_profile` = `ID`) WHERE {field_date} >= ''{from}'' AND {field_date} <= ''{to}'' GROUP BY `period` ORDER BY {field_date} ASC', 1, @iMaxOrderCharts);

-- Export

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_dolphcon', '_sys_module_dolphcon', 'BxDolphConExport', 'modules/boonex/dolphin_connect/classes/BxDolphConExport.php', @iMaxOrderExports, 1);

