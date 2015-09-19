

ALTER TABLE  `bx_simple_messenger_messages` CHANGE  `Message`  `Message` BLOB NOT NULL;


DELETE FROM `sys_options` WHERE `Name` = 'simple_messenger_procces_smiles';


UPDATE `sys_menu_admin` SET `icon` = 'comments-o' WHERE `icon` = 'comments-alt' AND `name` = 'Simple messenger';


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'simple_messenger' AND `version` = '1.1.6';

