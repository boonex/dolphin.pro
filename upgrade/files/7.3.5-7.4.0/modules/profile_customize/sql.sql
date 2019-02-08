

DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Icon` = 'magic' AND `Script` LIKE '%profile_customize_page%';

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_objects_actions` WHERE `Type` = 'Profile' ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES('{evalResult}', 'magic', '', '$(''#profile_customize_page'').fadeIn(''slow'');', 'return array(''evalResult'' => defined(''BX_PROFILE_PAGE'') && {ID} == {member_id} && getParam(''bx_profile_customize_enable'') == ''on'' ? _t( ''_Customize'' ) : null, ''evalResultCssClassWrapper'' => ''bx-phone-hide'');', @iMaxOrder, 'Profile');


DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_profile_customize';

SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_profile_customize', '_sys_module_profile_customize', 'BxProfileCustomizeExport', 'modules/boonex/profile_customize/classes/BxProfileCustomizeExport.php', @iMaxOrderExports, 1);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'profile_customize' AND `version` = '1.3.5';

