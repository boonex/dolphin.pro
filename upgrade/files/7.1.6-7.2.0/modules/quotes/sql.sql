
ALTER TABLE `bx_quotes_units` CHANGE `ID`  `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'quotes' AND `version` = '1.1.6';

