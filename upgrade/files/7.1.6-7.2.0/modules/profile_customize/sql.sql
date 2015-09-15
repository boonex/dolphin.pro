
UPDATE `sys_objects_actions` SET `Script` = '$(''#profile_customize_page'').fadeIn(''slow'');' WHERE `Type` = 'Profile' AND `Eval` LIKE '%bx_profile_customize_enable%';

-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'profile_customize' AND `version` = '1.1.6';

