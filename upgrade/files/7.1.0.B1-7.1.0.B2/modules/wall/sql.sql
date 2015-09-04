
-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_mma_Wall_Post_Comment');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_mma_Wall_Post_Comment');

