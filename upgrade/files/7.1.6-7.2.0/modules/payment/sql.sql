
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `bx_pmt_providers` ADD `for_visitor` tinyint(4) NOT NULL default '0' AFTER `option_prefix`;
ALTER TABLE `bx_pmt_providers` ADD `class_file` varchar(255) NOT NULL default '' AFTER `class_name`;
  
ALTER TABLE `bx_pmt_transactions_pending` ADD `processed` tinyint(4) NOT NULL default '0' AFTER `date`;

-- ================ can be safely applied multiple times ================ 

SET @sModuleName = 'Payment';


ALTER TABLE `bx_pmt_transactions_pending` CHANGE  `provider`  `provider` VARCHAR(32) NOT NULL;


UPDATE `sys_page_compose` SET `Caption` = '_payment_bcpt_cart_featured' WHERE `Caption` = '_payment_bcaption_cart_featured' AND `Page` = 'bx_pmt_cart';
UPDATE `sys_page_compose` SET `Caption` = '_payment_bcpt_cart_common' WHERE `Caption` = '_payment_bcaption_cart_common' AND `Page` = 'bx_pmt_cart';
UPDATE `sys_page_compose` SET `Caption` = '_payment_bcpt_cart_history' WHERE `Caption` = '_payment_bcaption_cart_history' AND `Page` = 'bx_pmt_history';
UPDATE `sys_page_compose` SET `Caption` = '_payment_bcpt_processed_orders' WHERE `Caption` = '_payment_bcaption_processed_orders' AND `Page` = 'bx_pmt_orders';
UPDATE `sys_page_compose` SET `Caption` = '_payment_bcpt_details' WHERE `Caption` = '_payment_bcaption_details' AND `Page` = 'bx_pmt_details';


DELETE FROM `sys_menu_member` WHERE `Name` = 'Shopping Cart' AND `Caption` = '_payment_tbar_item_caption';


DELETE FROM `sys_menu_top` WHERE `Name` = 'Payments' AND `Caption` = '_payment_tmenu_payments';
DELETE FROM `sys_menu_top` WHERE `Name` = 'Cart' AND `Caption` = '_payment_tmenu_cart';


SET @iHandlerId = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_payment');
DELETE FROM `sys_alerts` WHERE `unit` = 'profile' AND `action` = 'join' AND `handler_id` = @iHandlerId;
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'join', @iHandlerId);


DELETE FROM `sys_email_templates` WHERE `Name` = 'bx_pmt_paid_need_join';
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('bx_pmt_paid_need_join', 'Payment was accepted', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>\r\nYour payment was accepted. If you did not fill in the join form yet, then you may do it using the following link. \r\n</p>\r\n\r\n<p>\r\n<a href="<JoinLink>">Join Now</a>\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Payment: Paid and need to join', 0);


DELETE FROM `sys_objects_payments` WHERE `object` = @sModuleName;
INSERT INTO `sys_objects_payments` (`object`, `title`, `uri`) VALUES
(@sModuleName, '_sys_module_payment', 'payment');


UPDATE `bx_pmt_providers` SET `caption` = '_payment_pp_cpt', `description` = '_payment_pp_dsc', `for_visitor` = 1 WHERE `name` = 'paypal';
SET @iProviderId = (SELECT `id` FROM `bx_pmt_providers` WHERE `name` = 'paypal' LIMIT 1);
DELETE FROM `bx_pmt_providers_options` WHERE `provider_id` = @iProviderId AND `name` = 'pp_return_url';
INSERT INTO `bx_pmt_providers_options`(`provider_id`, `name`, `type`, `caption`, `description`, `extra`, `check_type`, `check_params`, `check_error`, `order`) VALUES
(@iProviderId, 'pp_return_url', 'value', '_payment_details_return_url', '', '', '', '', '', 8);

UPDATE `bx_pmt_providers` SET `caption` = '_payment_2co_cpt', `description` = '_payment_2co_dsc', `for_visitor` = 1 WHERE `name` = '2checkout';
SET @iProviderId = (SELECT `id` FROM `bx_pmt_providers` WHERE `name` = '2checkout' LIMIT 1);
DELETE FROM `bx_pmt_providers_options` WHERE `provider_id` = @iProviderId AND `name` = '2co_return_url';
INSERT INTO `bx_pmt_providers_options`(`provider_id`, `name`, `type`, `caption`, `description`, `extra`, `check_type`, `check_params`, `check_error`, `order`) VALUES
(@iProviderId, '2co_return_url', 'value', '_payment_details_return_url', '', '', '', '', '', 6);

UPDATE `bx_pmt_providers_options` SET `extra` = 'CC|_payment_2co_payment_method_cc,PPI|_payment_2co_payment_method_ppi' WHERE `provider_id` = @iProviderId AND `name` = '2co_payment_method';


DELETE FROM `bx_pmt_providers` WHERE `name` = 'bitpay';
DELETE FROM `bx_pmt_providers_options` WHERE `name` LIKE 'bp_%';
INSERT INTO `bx_pmt_providers`(`name`, `caption`, `description`, `option_prefix`, `for_visitor`, `class_name`) VALUES
('bitpay', '_payment_bp_cpt', '_payment_bp_dsc', 'bp_', 0, 'BxPmtBitPay');
SET @iProviderId = LAST_INSERT_ID();

INSERT IGNORE INTO `bx_pmt_providers_options`(`provider_id`, `name`, `type`, `caption`, `description`, `extra`, `check_type`, `check_params`, `check_error`, `order`) VALUES
(@iProviderId, 'bp_active', 'checkbox', '_payment_bp_active_cpt', '_payment_bp_active_dsc', '', '', '', '', 1),
(@iProviderId, 'bp_api_key', 'text', '_payment_bp_api_key_cpt', '_payment_bp_api_key_dsc', '', '', '', '', 2),
(@iProviderId, 'bp_transaction_speed', 'select', '_payment_bp_transaction_speed_cpt', '_payment_bp_transaction_speed_dsc', 'high|_payment_bp_transaction_speed_high,medium|_payment_bp_transaction_speed_medium,low|_payment_bp_transaction_speed_low', '', '', '', 3),
(@iProviderId, 'bp_full_notifications', 'checkbox', '_payment_bp_full_notifications_cpt', '_payment_bp_full_notifications_dsc', '', '', '', '', 4),
(@iProviderId, 'bp_notification_email', 'text', '_payment_bp_notification_email_cpt', '_payment_bp_notification_email_dsc', '', '', '', '', 5);


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_payment_2co_payment_method_al','_payment_2co_payment_method_ck','_payment_add_to_cart','_payment_bcaption_cart_common','_payment_bcaption_cart_featured','_payment_bcaption_cart_history','_payment_bcaption_details','_payment_bcaption_payment_result','_payment_bcaption_pending_orders','_payment_bcaption_processed_orders','_payment_bcaption_settings','_payment_err_self_purchase','_payment_fldcaption_client','_payment_fldcaption_module','_payment_fldcaption_order','_payment_ocaption_select','_payment_pcaption_admin','_payment_pcaption_cart_history','_payment_pcaption_details','_payment_pcaption_payment_result','_payment_pcaption_view_cart','_payment_pcaption_view_orders','_payment_tbar_item_caption','_payment_tbar_item_description','_payment_tmenu_cart','_payment_tmenu_payments','_payment_txt_empty','_payment_wcaption_manual_order','_payment_wcaption_order_info');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_payment_2co_payment_method_al','_payment_2co_payment_method_ck','_payment_add_to_cart','_payment_bcaption_cart_common','_payment_bcaption_cart_featured','_payment_bcaption_cart_history','_payment_bcaption_details','_payment_bcaption_payment_result','_payment_bcaption_pending_orders','_payment_bcaption_processed_orders','_payment_bcaption_settings','_payment_err_self_purchase','_payment_fldcaption_client','_payment_fldcaption_module','_payment_fldcaption_order','_payment_ocaption_select','_payment_pcaption_admin','_payment_pcaption_cart_history','_payment_pcaption_details','_payment_pcaption_payment_result','_payment_pcaption_view_cart','_payment_pcaption_view_orders','_payment_tbar_item_caption','_payment_tbar_item_description','_payment_tmenu_cart','_payment_tmenu_payments','_payment_txt_empty','_payment_wcaption_manual_order','_payment_wcaption_order_info');


-- update module version

UPDATE `sys_modules` SET `version` = '1.2.0' WHERE `uri` = 'payment' AND `version` = '1.1.6';

