

UPDATE `sys_menu_member` SET `Position`='top_extra' WHERE `Name` = 'bx_poll';


UPDATE `sys_objects_cmts` SET `ClassName` = 'BxPollCmts', `ClassFile` = 'modules/boonex/poll/classes/BxPollCmts.php' WHERE `ObjectName` = 'bx_poll';


UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Icon` = 'share' AND `Type` = 'bx_poll';
UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Icon` = 'paper-clip' AND `Type` = 'bx_poll';

UPDATE `sys_objects_actions` SET `Eval` = 'return isMember() ? BxDolService::call(''poll'', ''edit_action_button'', array({ViewerID}, {ID})) : null;' WHERE `Caption` = '_bx_poll_edit' AND `Type` = 'bx_poll';
UPDATE `sys_objects_actions` SET `Script` = 'showPopupAnyHtml (\'{BaseUri}share_popup/{ID}\');' WHERE `Caption` = '{TitleShare}' AND `Type` = 'bx_poll';

DELETE FROM `sys_objects_actions` WHERE `Caption` = '{approved_cpt}' AND `Type` = 'bx_poll';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{approved_cpt}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{base_url}approve/{ID}/{approved_act}'', false, ''post''); return false;', '', 5, 'bx_poll', 0);




-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'poll' AND `version` = '1.1.6';

