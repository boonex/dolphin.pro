
SET @sModuleName = 'bx_payflow';


DELETE FROM `sys_objects_exports` WHERE `object` = @sModuleName;

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
(@sModuleName, '_bx_pfw', 'BxPfwExport', 'modules/boonex/paypal_payflow/classes/BxPfwExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'payflow' AND `version` = '1.3.5';

