
UPDATE `bx_pmt_providers_options` SET `extra` = '1|_payment_pp_cnt_type_ssl,2|_payment_pp_cnt_type_http' WHERE `name` = 'pp_cnt_type' AND `extra` = '1|_payment_pp_cnt_type_ssl,2|_payment_pp_cnt_type_html';

-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_payment_pp_cnt_type_html');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_payment_pp_cnt_type_html');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.3' WHERE `uri` = 'payment' AND `version` = '1.1.2';

