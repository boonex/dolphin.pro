
-- Options

SET @iKategId = (SELECT `id` FROM `sys_options_cats` WHERE `name` = 'Chat+' LIMIT 1);

DELETE FROM `sys_options_cats` WHERE `id` = @iKategId;
DELETE FROM `sys_options` WHERE `kateg` = @iKategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_chat_plus_permalinks' AND `kateg` = 26;

-- Menu Admin

DELETE FROM `sys_menu_admin` WHERE `title` = '_bx_chat_plus';

-- Permalinks

DELETE FROM `sys_permalinks` WHERE `standard`  = 'modules/?r=chat_plus/';

-- Main Menu

DELETE FROM `sys_menu_top` WHERE `Name` = 'Chat+';

-- Page

DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'bx_chat_plus';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_chat_plus';

-- Mobile App

DELETE FROM `sys_menu_mobile` WHERE `type` = 'bx_chat_plus';

-- Injections

DELETE FROM `sys_injections` WHERE `name` = 'bx_chat_plus';

