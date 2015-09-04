
-- ================ can be safely applied multiple times ================ 


DELETE FROM `sys_menu_member` WHERE `Name` = 'ShoppingCart';
INSERT INTO `sys_menu_member` (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Order`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Parent`, `Bubble`, `Description`) VALUES 
('_sys_pmt_shopping_cart_caption', 'ShoppingCart', 'shopping-cart', 'cart.php', '', '', 'bx_import(''BxDolPayments'');\r\nreturn BxDolPayments::getInstance()->getCartItems();', 4, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import(''BxDolPayments'');\r\n$oPayment = BxDolPayments::getInstance();\r\nif($oPayment->isActive()) $aRetEval = $oPayment->getCartItemCount({ID}, {iOldCount}); else $isSkipItem = true;', '_sys_pmt_shopping_cart_description');


SET @iCatGeneral = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'General' LIMIT 1);
UPDATE `sys_options` SET `kateg` = @iCatGeneral WHERE `Name` IN('sys_ftp_login', 'sys_ftp_password', 'sys_ftp_dir');

UPDATE `sys_options` SET `VALUE` = 'payment' WHERE `Name` = 'sys_default_payment' AND `VALUE` = '';

UPDATE `sys_options` SET `VALUE` = '<div class="bx-splash bx-def-round-corners" style="background-image: url(templates/base/images/bx_splash_image.jpg);"><div class="bx-splash-txt"><div class="bx-splash-txt-cnt"><div class="bx-splash-txt-l1 bx-def-padding-sec-leftright"><h1 class="bx-cd-headline zoom"><span class="bx-cd-words-wrapper"><b class="bx-cd-word is-visible">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b></span></h1></div><div class="bx-splash-actions"><button class="bx-btn bx-btn-primary bx-btn-sa-join">Join</button><button class="bx-btn bx-def-margin-left bx-btn-sa-login">Login</button></div></div></div></div>' WHERE `Name` = 'splash_code';


UPDATE `sys_menu_top` SET `Check` = 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();' WHERE `Name` = 'Cart';
UPDATE `sys_menu_top` SET `Check` = 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();' WHERE `Name` = 'Payments';


-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_adm_pbuilder_Caption_Cache', '_adm_pbuilder_Info_Cache');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_adm_pbuilder_Caption_Cache', '_adm_pbuilder_Info_Cache');


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.2.0.B2', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.2.0.B2';

