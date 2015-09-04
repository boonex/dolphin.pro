
-- ================ can be safely applied multiple times ================ 

UPDATE `sys_menu_member` SET `Bubble` = '$isSkipItem = $aReplaced[$sPosition][$iKey][''linked_items''] ? false : true;\r\n$aRetEval = false;' WHERE `Name` = 'AddContent';

UPDATE `sys_page_compose` SET `DesignBox` = 11 WHERE `Page` = '' AND `Func` = 'Sample' AND `Content` = 'Echo';

UPDATE `sys_menu_top` SET `Check` = 'return isLogged() && getParam(\'enable_match\') == \'on\';' WHERE `Name` = 'Match' AND `Link` = 'search.php?show=match';

UPDATE `sys_options` SET `desc` = 'Delete profiles of members that didn\'t login for (days)' WHERE `Name` = 'db_clean_profiles';


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.0', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.0';

