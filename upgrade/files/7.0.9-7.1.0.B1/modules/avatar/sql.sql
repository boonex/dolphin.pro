
-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('pedit') AND `Desc` IN ('Manage Avatars');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Tight', 'Wide') AND `Page` = 'bx_avatar_main';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'bx_avatar_main';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_avatar_main', '1140px', 'Tight block', '_bx_ava_block_tight', '2', '0', 'Tight', '', '1', '28.1', 'non,memb', '0'),
    ('bx_avatar_main', '1140px', 'Wide block', '_bx_ava_block_wide', '1', '0', 'Wide', '', '1', '71.9', 'non,memb', '0');

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
    ('pedit', '1140px', 'Manage Avatars', '_bx_ava_manage_avatars', 2, @iMaxOrder, 'PHP', 'return BxDolService::call(''avatar'', ''manage_avatars'', array ((int)$_REQUEST[''ID'']));', 1, 28.1, 'memb', 0);


-- options

UPDATE `sys_options` SET `VALUE` = '90' WHERE `Name` = 'bx_avatar_quality' AND `VALUE` = '85';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'user' WHERE `name` = 'bx_avatar';


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_photos' AND `Caption` = '{TitleAvatar}';
SET @iOrderActions = (SELECT `Order` + 1 FROM `sys_objects_actions` ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('bx_photos', '{TitleAvatar}', 'user', '{evalResult}&make_avatar_from_shared_photo={ID}', '', 'bx_import(''BxDolPermalinks'');\r\n$o = new BxDolPermalinks();\r\nreturn $o->permalink(''modules/?r=avatar/'');', @iOrderActions);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_ava_wall_added_new');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_ava_wall_added_new');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'avatar' AND `version` = '1.0.9';

