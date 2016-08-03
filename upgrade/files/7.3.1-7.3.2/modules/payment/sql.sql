
DELETE FROM `bx_pmt_providers_options` WHERE `name` = 'pp_cnt_type';

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_payment_pp_cnt_type_cpt','_payment_pp_cnt_type_dsc','_payment_pp_cnt_type_http','_payment_pp_cnt_type_ssl');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_payment_pp_cnt_type_cpt','_payment_pp_cnt_type_dsc','_payment_pp_cnt_type_http','_payment_pp_cnt_type_ssl');

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'payment' AND `version` = '1.3.1';

