
-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Profiler', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('bx_profiler_show_debug_panel', 'admins', @iCategId, 'Show debug panel below the page for', 'select', '', '', '0', 'none,admins,all'),

('bx_profiler_long_sql_queries_log', 'on', @iCategId, 'Log long sql queries', 'checkbox', '', '', '0', ''),
('bx_profiler_long_sql_queries_time', '2', @iCategId, 'Time in seconds of long sql query', 'digit', '', '', '0', ''),
('bx_profiler_long_sql_queries_debug', '', @iCategId, 'Log additionad debug info with each long sql query', 'checkbox', '', '', '0', ''),

('bx_profiler_long_module_query_log', 'on', @iCategId, 'Log long modules queries', 'checkbox', '', '', '0', ''),
('bx_profiler_long_module_query_time', '3', @iCategId, 'Time in seconds of long module query', 'digit', '', '', '0', ''),
('bx_profiler_long_module_query_debug', '', @iCategId, 'Log additionad debug info with each long module query', 'checkbox', '', '', '0', ''),

('bx_profiler_long_page_log', 'on', @iCategId, 'Log long page opens', 'checkbox', '', '', '0', ''),
('bx_profiler_long_page_time', '5', @iCategId, 'Time in seconds of long page open', 'digit', '', '', '0', ''),
('bx_profiler_long_page_debug', '', @iCategId, 'Log additionad debug info with each long page open', 'checkbox', '', '', '0', '');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'bx_profiler', '_bx_profiler', '{siteUrl}modules/?r=profiler/administration/', 'Profiler module by BoonEx', 'flask', @iMax+1);
