
-- ================ can be safely applied multiple times ================ 


UPDATE `sys_options` SET `AvailableValues` = 'PHP:bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->getPayments();' WHERE `Name` = 'sys_default_payment';


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.2.0.RC', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.2.0.RC';

