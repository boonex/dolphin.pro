

SET @iProviderId = (SELECT `id` FROM `bx_pmt_providers` WHERE `name` = 'bitpay');
UPDATE `bx_pmt_providers_options` SET `check_error` = '_payment_bp_active_err', `check_type` = 'https' WHERE `provider_id` = @iProviderId AND `name` = 'bp_active';


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'payment' AND `version` = '1.2.1';

