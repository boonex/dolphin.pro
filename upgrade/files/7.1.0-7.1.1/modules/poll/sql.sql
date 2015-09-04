
UPDATE `sys_menu_top` SET `Caption` = '_bx_poll_featured_polls' WHERE `Name` = 'Featured' AND `Caption` = '_bx_poll_featured';

DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_polls';
INSERT INTO 
    `sys_account_custom_stat_elements` 
SET
    `Label` = '_bx_polls', 
    `Value` = '__spo__ (<a href="modules/?r=poll&action=my">__l_add__</a>)';


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'poll' AND `version` = '1.1.0';

