
SET @sModuleName = 'Payment';

-- options

UPDATE `sys_options` SET `Type` = 'digit' WHERE `Name` = 'pmt_default_currency_sign';

DELETE FROM `sys_options` WHERE `Name` = 'pmt_site_admin';
SET @iCategoryId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = @sModuleName);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('pmt_site_admin', '', @iCategoryId, 'Site administrator', 'select', '', '', 2, 'PHP:return BxDolService::call(''payment'', ''get_admins'');');


-- page builder

UPDATE `sys_page_compose` SET `ColWidth` = 50 WHERE `Page` = 'bx_pmt_cart' AND `Func` IN ('Featured', 'Common');


-- menu member

UPDATE `sys_menu_member` SET `Icon` = 'shopping-cart', `Order` = 3 WHERE `Name` = 'Shopping Cart';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'credit-card' WHERE `name` = 'bx_payment';


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_pmt';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_pmt', '_payment_chart', 'bx_pmt_transactions', 'date', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_payment_bcaption_checkout','_payment_pcaption_checkout','_payment_pp_business_err','_payment_pp_sandbox_err','_payment_txt_by','_payment_txt_checkout','_payment_txt_items_on','_payment_txt_username_administrator','_payment_txt_view');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_payment_bcaption_checkout','_payment_pcaption_checkout','_payment_pp_business_err','_payment_pp_sandbox_err','_payment_txt_by','_payment_txt_checkout','_payment_txt_items_on','_payment_txt_username_administrator','_payment_txt_view');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'payment' AND `version` = '1.0.9';

