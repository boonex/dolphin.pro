
-- Options

SET @iKategId = (SELECT `id` FROM `sys_options_cats` WHERE `name` = 'WebRTC Chat' LIMIT 1);

DELETE FROM `sys_options_cats` WHERE `id` = @iKategId;
DELETE FROM `sys_options` WHERE `kateg` = @iKategId;
DELETE FROM `sys_options` WHERE `Name` = 'bx_chat_webrtc_permalinks' AND `kateg` = 26;

-- Menu Admin

DELETE FROM `sys_menu_admin` WHERE `title` = '_bx_chat_webrtc';

-- Permalinks

DELETE FROM `sys_permalinks` WHERE `standard`  = 'modules/?r=chat_webrtc/';

-- Main Menu

DELETE FROM `sys_menu_top` WHERE `Name` = 'WebRTC Chat';

