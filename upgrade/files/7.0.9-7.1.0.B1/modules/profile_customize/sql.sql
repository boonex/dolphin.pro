
-- CSS rules

TRUNCATE TABLE `bx_profile_custom_units`;

INSERT INTO `bx_profile_custom_units` (`name`, `caption`, `css_name`, `type`) VALUES
	('body', 'Page background', 'body', 'background'),
    ('boxtext', 'Font for boxes', '#divUnderCustomization .disignBoxFirst, #divUnderCustomization .boxFirstHeader, #divUnderCustomization .disignBoxFirst a, #divUnderCustomization .bx-def-font-grayed', 'font'),
    ('boxborder', 'Border for boxes', '#divUnderCustomization .disignBoxFirst', 'border');


-- objects: actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Eval` LIKE '%bx_profile_customize%';

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_objects_actions` WHERE `Type` = 'Profile' ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES
('{evalResult}', 'magic', '', '$(''#profile_customize_page'').fadeIn(''slow'', function() {dbTopMenuLoad(''profile_customizer'');});', 'if (defined(''BX_PROFILE_PAGE'') && {ID} == {member_id} && getParam(''bx_profile_customize_enable'') == ''on'') return _t( ''_Customize'' ); else return null;', @iMaxOrder, 'Profile');


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'magic' WHERE `name` = 'bx_profile_customize';



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_profile_customize_btn_customize','_bx_profile_customize_btn_submit','_bx_profile_customize_settings');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_profile_customize_btn_customize','_bx_profile_customize_btn_submit','_bx_profile_customize_settings');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'profile_customize' AND `version` = '1.0.9';

