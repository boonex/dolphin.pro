
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_poll' AND `Caption` = '{featured_cpt}'; 
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{featured_cpt}', 'star-o', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{base_url}set_option/{ID}/featured'', false, ''post''); return false;', '', 7, 'bx_poll', 0);

UPDATE `sys_objects_actions` SET `Script` = 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{base_url}set_option/{ID}'', false, ''post''); return false;' WHERE `Type` = 'bx_poll' AND `Caption` = '{approved_cpt}'; 

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.5' WHERE `uri` = 'poll' AND `version` = '1.3.4';

