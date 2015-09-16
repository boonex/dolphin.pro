
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `bx_store_products` ADD `allow_view_forum_to` varchar(16) NOT NULL AFTER `allow_post_in_forum_to`;

-- ================ can be safely applied multiple times ================ 

UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE  `Icon` = 'share' AND `Type` = 'bx_store';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE  `Icon` = 'star-empty' AND `Type` = 'bx_store';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE  `Icon` = 'paper-clip' AND `Type` = 'bx_store';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_store' AND `Caption` = '{TitleActivate}';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{TitleActivate}', 'check-circle-o', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxStoreModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''activate/{ID}'';', '8', 'bx_store');


UPDATE `sys_menu_member` SET `Position` = 'top_extra' WHERE `Name` = 'bx_store';


DELETE FROM `sys_privacy_actions` WHERE `module_uri` = 'store' AND `name` = 'view_forum';
INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('store', 'view_forum', '_bx_store_privacy_view_forum_product', 'c');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'store' AND `version` = '1.1.6';

