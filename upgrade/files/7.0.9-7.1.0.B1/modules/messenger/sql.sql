

-- objects: actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Script` LIKE '%/messenger/%';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{evalResult}', 'comments-alt', '', 'window.open( ''modules/boonex/messenger/popup.php?rspId={ID}'' , ''Messenger'', ''width=550,height=500,toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=1'', 0);', 'return BxDolService::call(''messenger'', ''get_action_link'', array({member_id}, {ID}));', 12, 'Profile');



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_messenger_box_caption');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_messenger_box_caption');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'messenger' AND `version` = '1.0.9';

