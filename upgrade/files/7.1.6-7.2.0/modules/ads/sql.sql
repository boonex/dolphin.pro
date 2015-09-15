
UPDATE `bx_ads_category` SET `Picture` = 'eye' WHERE `Picture` = 'eye-open';

UPDATE `sys_objects_cmts` SET `ClassName` = 'BxAdsCmts', `ClassFile` = 'modules/boonex/ads/classes/BxAdsCmts.php' WHERE `ObjectName` = 'ads';

UPDATE  `sys_menu_member` SET `Position`='top_extra' WHERE `Name` = 'bx_ads';

UPDATE `sys_objects_actions` SET `Icon` = 'check-circle-o' WHERE `Type` = 'bx_ads' AND `Icon` = 'ok-circle';
UPDATE `sys_objects_actions` SET `Icon` = 'star-o' WHERE `Type` = 'bx_ads' AND `Icon` = 'star-empty';
UPDATE `sys_objects_actions` SET `Icon` = 'paper-clip' WHERE `Type` = 'bx_ads' AND `Icon` = 'paperclip';
UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Type` = 'bx_ads' AND `Icon` = 'share';
UPDATE `sys_objects_actions` SET `Script` = '$(''#ActivateAdvertisementID'').val(''{ads_id}''); $(''#ActType'').val(''{ads_act_type}''); document.forms.command_activate_advertisement.submit(); return false;', `Eval` = 'if (!isAdmin() && !isModerator())\r\nreturn null;\r\nif (''{ads_status}''!=''active'') {\r\nreturn _t(''_bx_ads_Activate'');\r\n}\r\nelse\r\nreturn _t(''_bx_ads_DeActivate'');' WHERE `Type` = 'bx_ads' AND `Icon` = 'check-circle-o' AND `Script` LIKE '%ActivateAdvertisementID%';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'ads' AND `version` = '1.1.6';

