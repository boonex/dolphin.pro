
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `sys_menu_admin_top` ADD `target` varchar(64) NOT NULL default '' AFTER `url`;

ALTER TABLE `sys_objects_auths` ADD `Name` varchar(64) NOT NULL AFTER `ID`;
ALTER TABLE `sys_objects_auths` ADD `Icon` varchar(64) NOT NULL AFTER `Link`;
  
ALTER TABLE `sys_acl_levels_members` ADD `Expiring` tinyint(4) unsigned NOT NULL default '0' AFTER `TransactionID`;

ALTER TABLE `Profiles` ADD `FullName` varchar(255) NOT NULL AFTER `FavoriteBooks`;
UPDATE `Profiles` SET `FullName` = CONCAT(`FirstName`, ' ', `LastName`);
-- ALTER TABLE `Profiles` DROP `FirstName`, DROP `LastName`;
-- ALTER TABLE `Profiles` DROP `Headline`;
ALTER TABLE  `Profiles` DROP INDEX  `NickName_2`, ADD FULLTEXT  `NickName_2` (`NickName`, `City`, `DescriptionMe`, `Tags`);

ALTER TABLE `RayChatMessages` ADD `SndRcp` varchar(40) NOT NULL default '' AFTER `Room`;

-- ================ can be safely applied multiple times ================ 

CREATE TABLE IF NOT EXISTS `RayChatHistory` (
  `ID` int(11) NOT NULL auto_increment,
  `Room` int(11) NOT NULL default 0, 
  `SndRcp` varchar(40) NOT NULL default '', 
  `Sender` varchar(20) NOT NULL default '', 
  `Recipient` varchar(20) NOT NULL default '', 
  `Message` text NOT NULL default '',
  `When` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- admin main menu

UPDATE `sys_menu_admin` SET `icon` = 'users col-green1', `icon_large` = 'users' WHERE `name` = 'users';

UPDATE `sys_menu_admin` SET `icon` = 'puzzle-piece col-red1', `icon_large` = 'puzzle-piece' WHERE `name` = 'modules';

UPDATE `sys_menu_admin` SET `icon` = 'ban col-green3' WHERE `name` = 'ip_blacklist';

UPDATE `sys_menu_admin` SET `icon` = 'download col-green3' WHERE `name` = 'database_backup';

UPDATE `sys_menu_admin` SET `icon` = 'hdd-o col-green3' WHERE `name` = 'host_tools';

UPDATE `sys_menu_admin` SET `icon` = 'gavel col-green3' WHERE `name` = 'antispam';

UPDATE `sys_menu_admin` SET `icon` = 'magic col-red2', `icon_large` = 'magic' WHERE `name` = 'builders';

UPDATE `sys_menu_admin` SET `icon` = 'sliders col-blue2', `icon_large` = 'sliders' WHERE `name` = 'settings';

UPDATE `sys_menu_admin` SET `icon` = 'user-secret col-blue2' WHERE `name` = 'admin_password';

UPDATE `sys_menu_admin` SET `icon` = 'language col-blue2' WHERE `name` = 'languages_settings';

UPDATE `sys_menu_admin` SET `icon` = 'star-o col-blue2' WHERE `name` = 'membership_levels';

UPDATE `sys_menu_admin` SET `icon` = 'clipboard col-blue2' WHERE `name` = 'email_templates';

UPDATE `sys_menu_admin` SET `icon` = 'eye col-blue2' WHERE `name` = 'templates';

UPDATE `sys_menu_admin` SET `icon` = 'folder col-blue2' WHERE `name` = 'categories_settings';

UPDATE `sys_menu_admin` SET `icon` = 'tachometer col-blue3', `icon_large` = 'tachometer' WHERE `name` = 'dashboard';

DELETE FROM `sys_menu_admin` WHERE `name` = 'license';


-- admin top menu

DELETE FROM `sys_menu_admin_top` WHERE `name` IN('home','info','extensions','logout');

INSERT INTO `sys_menu_admin_top`(`name`, `caption`, `url`, `target`, `icon`, `order`) VALUES
('home', '_adm_tmi_home', '{site_url}index.php', '_blank', 'external-link-square', 1),
('extensions', '_adm_tmi_extensions', 'http://www.boonex.com/market', '', 'puzzle-piece', 2),
('info', '_adm_tmi_info', 'http://www.boonex.com/trac/dolphin/wiki/Dolphin7Docs', '', 'question-circle', 3),
('logout', '_adm_tmi_logout', '{site_url}logout.php', '', 'sign-out', 4);


-- service menu

UPDATE `sys_menu_service` SET `Icon` = 'user', `Link` = '', `Script` = 'showPopupJoinForm(); return false;' WHERE `Name` = 'Join';
UPDATE `sys_menu_service` SET `Icon` = 'sign-in', `Order` = '0', `Active` = '0' WHERE `Name` = 'Login';
UPDATE `sys_menu_service` SET `Order` = '0', `Active` = '0' WHERE `Name` = 'Profile';
UPDATE `sys_menu_service` SET `Icon` = 'tachometer', `Order` = '1' WHERE `Name` = 'Account';
UPDATE `sys_menu_service` SET `Icon` = 'sign-out' WHERE `Name` = 'Logout';

DELETE FROM `sys_menu_service` WHERE `Name` = 'ProfileSettings';
INSERT INTO `sys_menu_service` (`Name`, `Caption`, `Icon`, `Link`, `Script`, `Target`, `Order`, `Visible`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`) VALUES
('ProfileSettings', '_sys_sm_profile_settings', 'cog', 'pedit.php?ID={memberID}', '', '', 2, 'memb', 1, 3, 1, 1, 1);


-- member menu

UPDATE `sys_menu_member` SET `Eval` = 'return ''<b class="menu_item_username">'' . getNickName({ID}) . ''</b>'';' WHERE `Name` = 'MemberBlock';
UPDATE `sys_menu_member` SET `Order` = 0, `Active` = 0, `Position` = 'top_extra' WHERE `Name` = 'Settings';
UPDATE `sys_menu_member` SET `Name` = 'StatusMessage', `Order` = 0, `Active` = 0, `Position` = 'top_extra' WHERE `Name` = 'Status Message';
UPDATE `sys_menu_member` SET `PopupMenu` = 'bx_import( ''BxDolUserStatusView'' );\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView -> getStatusField({ID});', `Order` = 6, `Active` = 1, `Position` = 'top_extra' WHERE `Name` = 'AddContent';
UPDATE `sys_menu_member` SET `Icon` = 'users', `Order` = 3 WHERE `Name` = 'Friends';
UPDATE `sys_menu_member` SET `Name` = 'AdminPanel', `Order` = 5 WHERE `Name` = 'Admin Panel';

DELETE FROM `sys_menu_member` WHERE `Name` = 'ShoppingCart';
INSERT INTO `sys_menu_member` (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Order`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Parent`, `Bubble`, `Description`) VALUES 
('_sys_pmt_shopping_cart_caption', 'ShoppingCart', 'shopping-cart', 'cart.php', '', '', 'bx_import(''BxDolPayments'');\r\nreturn BxDolPayments::getInstance()->getCartItems();', 4, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import(''BxDolPayments'');\r\n$oPayment = BxDolPayments::getInstance();\r\nif($oPayment->isActive()) $aRetEval = $oPayment->getCartItemCount({ID}, {iOldCount}); else $isSkipItem = true;', '_sys_pmt_shopping_cart_description');


-- options

SET @iCatProfiles = 1;

DELETE FROM `sys_options` WHERE `Name` IN('disable_join_form','sys_headline','sys_member_info_info');

INSERT INTO `sys_options` VALUES
('disable_join_form', '', @iCatProfiles, 'Disable free join', 'checkbox', '', '', 55, ''),
('sys_member_info_info', 'sys_status_message', @iCatProfiles, 'Member brief info', 'select', '', '', 140, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'info\');');

UPDATE `sys_options` SET `err_text` = 'Must be > 0', `check` = 'return (int)$arg0 > 0;' WHERE `Name` = 'member_online_time';


SET @iCatGeneral = 3;

DELETE FROM `sys_options` WHERE `Name` IN('sys_default_payment', 'sys_embedly_key');

INSERT INTO `sys_options` VALUES
('sys_default_payment', 'payment', @iCatGeneral, 'Payment module (at least one payment processing module should be installed)', 'select', '', '', 170, 'PHP:bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->getPayments();'),
('sys_embedly_key', '', @iCatGeneral, 'Embedly Key', 'digit', '', '', 180, '');

UPDATE `sys_options` SET `kateg` = @iCatGeneral WHERE `Name` IN('sys_ftp_login', 'sys_ftp_password', 'sys_ftp_dir');


SET @iCatModeration = 6;

DELETE FROM `sys_options` WHERE `Name` IN('sys_album_auto_app');


SET @iCatTemplate = 13;

UPDATE `sys_options` SET `VALUE` = 'evo' WHERE `Name` = 'template' AND `VALUE` = 'uni';

SET @sTemplate = (SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'template');
UPDATE `sys_options` SET `VALUE` = '14' WHERE `Name` IN('nav_menu_elements_on_line_usr','nav_menu_elements_on_line_gst') AND 'evo' = @sTemplate;


SET @iCatSecurity = 14;

DELETE FROM `sys_options` WHERE `Name` IN('sys_security_impact_threshold_log', 'sys_security_impact_threshold_block');

UPDATE `sys_options` SET `desc` = 'CSRF token lifetime (seconds, 0 - no tracking)' WHERE `Name` = 'sys_security_form_token_lifetime';


SET @iCatLanguages = 21;

DELETE FROM `sys_options` WHERE `Name` IN('lang_subst_from_en');

INSERT INTO `sys_options` VALUES
('lang_subst_from_en', 'on', @iCatLanguages, 'Substitute (during compilation) missing translations with english ones', 'checkbox', '', '', 2, '');


SET @iCatHidden = 0;

DELETE FROM `sys_options` WHERE `Name` IN('sys_html_fields', 'sys_json_fields', 'sys_exceptions_fields');

UPDATE `sys_options` SET `VALUE` = '<div class="bx-splash bx-def-round-corners" style="background-image: url(templates/base/images/bx_splash_image.jpg);"><div class="bx-splash-txt"><div class="bx-splash-txt-cnt"><div class="bx-splash-txt-l1 bx-def-padding-sec-leftright"><h1 class="bx-cd-headline zoom"><span class="bx-cd-words-wrapper"><b class="bx-cd-word is-visible">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b></span></h1></div><div class="bx-splash-actions"><button class="bx-btn bx-btn-primary bx-btn-sa-join">Join</button><button class="bx-btn bx-def-margin-left bx-btn-sa-login">Login</button></div></div></div></div>' WHERE `Name` = 'splash_code';


-- pages

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'index' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` + 1 WHERE `Column` != 0 AND @iFirstColumn != 0;

UPDATE `sys_page_compose` SET `Caption` = '_Member_Login', `DesignBox` = 11 WHERE `Page` = 'index' AND `Desc` = 'Shows Login Form';

DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Profile cover';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('profile', '1140px', 'Profile cover', '_sys_bcpt_profile_cover', 1, 1, 'Cover', '', 0, 100, 'non,memb', 0, 0);

UPDATE `sys_page_compose` SET `Caption` = '_Join_now', `ColWidth` = 100 WHERE `Page` = 'join' AND `Desc` = 'Join Form Block';
UPDATE `sys_page_compose` SET `Column` = 0, `DesignBox` = 11, `ColWidth` = 100 WHERE `Page` = 'join' AND `Desc` = 'Login Form Block';


-- pre values

UPDATE `sys_pre_values` SET `Order` = 2 WHERE `Key` = 'Sex' AND `Value` = 'female';
UPDATE `sys_pre_values` SET `Order` = 1 WHERE `Key` = 'Sex' AND `Value` = 'male';

DELETE FROM `sys_pre_values` WHERE `Key` = 'Sex' AND `Value` = 'intersex';
INSERT INTO `sys_pre_values` VALUES('Sex', 'intersex', 3, '_Intersex', '_LookinIntersex', '', '', '', '');


-- profile fields

SET @iMaxJoinPage = (SELECT MAX(`JoinPage`) FROM `sys_profile_fields` WHERE `Type` = 'block' AND `JoinOrder` IS NOT NULL);

SET @iGeneralBlockId = (SELECT `ID` FROM `sys_profile_fields` WHERE `Name` = 'General Info' AND `Type` = 'block');

SET @iMiscBlockId = (SELECT `ID` FROM `sys_profile_fields` WHERE `Name` = 'Misc Info' AND `Type` = 'block');
SET @iMiscBlockMaxJoinOrder = (SELECT MAX(`JoinOrder`) FROM `sys_profile_fields` WHERE `JoinBlock` = @iMiscBlockId AND @iMiscBlockId);

SET @iSecurityBlockId = (SELECT `ID` FROM `sys_profile_fields` WHERE `Name` = 'Security Image' AND `Type` = 'block');
SET @iSecurityBlockFieldsCount = (SELECT COUNT(*) FROM `sys_profile_fields` WHERE `JoinBlock` = @iSecurityBlockId AND @iSecurityBlockId > 0);
SET @iSecurityBlockCaptchaId = (SELECT `ID` FROM `sys_profile_fields` WHERE `JoinBlock` = @iSecurityBlockId AND @iSecurityBlockId > 0 AND `Name` = 'Captcha');
SET @iSecurityBlockTermsOfUseId = (SELECT `ID` FROM `sys_profile_fields` WHERE `JoinBlock` = @iSecurityBlockId AND @iSecurityBlockId > 0 AND `Name` = 'TermsOfUse');
SET @iSecurityBlockIsOriginal = (SELECT (@iSecurityBlockFieldsCount = 2 AND @iSecurityBlockCaptchaId AND @iSecurityBlockTermsOfUseId AND @iMiscBlockId));

-- profile fields: remove headline field
DELETE FROM `sys_profile_fields` WHERE `Name` = 'Headline';

-- profile fields: make general block on first page and other blocks on second page - only if one step join form was used
UPDATE `sys_profile_fields` SET `JoinPage` = `JoinPage` + 1 WHERE @iMaxJoinPage = 0 AND `ID` != @iGeneralBlockId AND `Type` = 'block' AND `JoinOrder` IS NOT NULL;
UPDATE `sys_profile_fields` SET `JoinPage` = 0 WHERE @iMaxJoinPage = 0 AND `ID` = @iGeneralBlockId;

-- profile fields: delete security image block if it is in original state; move security image field to misc block; deactivate terms of use field
DELETE FROM `sys_profile_fields` WHERE `ID` = @iSecurityBlockId AND @iSecurityBlockIsOriginal;
UPDATE `sys_profile_fields` SET `JoinBlock` = @iMiscBlockId, `JoinOrder` = @iMiscBlockMaxJoinOrder + 1 WHERE `ID` = @iSecurityBlockCaptchaId AND @iSecurityBlockIsOriginal;
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `ID` = @iSecurityBlockTermsOfUseId AND @iSecurityBlockIsOriginal;

-- profile fields: remove first and last name fields; add fullname field at the beginning of Misc block; add agree field at the end of misc block
DELETE FROM `sys_profile_fields` WHERE `Name` IN('FirstName', 'LastName', 'FullName','Agree');
UPDATE `sys_profile_fields` SET `JoinOrder` = `JoinOrder` + 1 WHERE `JoinBlock` = @iMiscBlockId AND `JoinOrder` > 0;
INSERT INTO `sys_profile_fields` VALUES(NULL, 'FullName', 'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 1, 0, IFNULL(@iMiscBlockId, 0), IF(ISNULL(@iMiscBlockId), NULL, 1), 17, 2, 17, 2, 0, NULL, 17, 2, 17, 5, 0, NULL, 17, 2, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT INTO `sys_profile_fields` VALUES(NULL, 'Agree', 'system', NULL, '', NULL, NULL, '', 'LKey', '', 0, '', 0, 0, 0, IFNULL(@iMiscBlockId, 0), IF(ISNULL(@iMiscBlockId), NULL, @iMiscBlockMaxJoinOrder + 2), 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);

-- profile fields: make sex field selectbox; disable lookign for field on join form
UPDATE `sys_profile_fields` SET `Control` = 'select', `Default` = '' WHERE `Name` = 'Sex';
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `Name` = 'LookingFor';

-- profile fields: disable country, city and zip fields on join form
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL, `Default` = '' WHERE `Name` = 'Country';
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `Name` = 'City';
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `Name` = 'zip';

-- profile fields: disable email notify field on join form
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `Name` = 'EmailNotify';

-- profile fields: disable tags field on join form and make it available on edit own profile page
UPDATE `sys_profile_fields` SET `JoinBlock` = 0, `JoinOrder` = NULL WHERE `Name` = 'Tags';
UPDATE `sys_profile_fields` SET `EditOwnBlock` = @iMiscBlockId, `EditOwnOrder` = 9 WHERE `Name` = 'Tags';


-- top menu

UPDATE `sys_menu_top` SET `Link` = 'browse.php|browse' WHERE `Name` = 'All members' AND `Link` LIKE 'browse.php%';

UPDATE `sys_menu_top` SET `Picture` = 'tachometer' WHERE `Name` = 'Dashboard' AND `Picture` = 'dashboard';

UPDATE `sys_menu_top` SET `Picture` = 'info-circle' WHERE `Name` = 'About' AND `Picture` = 'info-sign';

UPDATE `sys_menu_top` SET `Picture` = 'question-circle' WHERE `Name` = 'Help' AND `Picture` = 'question-sign';

DELETE FROM `sys_menu_top` WHERE `Name` = 'Cart' AND `Caption` = '_sys_pmt_tmenu_cart';
DELETE FROM `sys_menu_top` WHERE `Name` = 'Payments' AND `Caption` = '_sys_pmt_tmenu_payments';
SET @iMaxId = (SELECT MAX(`ID`) FROM `sys_menu_top`);
UPDATE `sys_menu_top` SET `ID` = @iMaxId + 1 WHERE `ID` = 192;
UPDATE `sys_menu_top` SET `ID` = @iMaxId + 2 WHERE `ID` = 193;
INSERT INTO `sys_menu_top` (`ID`, `Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`) VALUES 
(192, 118, 'Cart', '_sys_pmt_tmenu_cart', 'cart.php|modules/?r={sys_payment_module_uri}/cart/|modules/?r={sys_payment_module_uri}/history/', 9, 'memb', '', '', 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();', 3, 1, 1, 1, 1, 'custom', '', '', 0, ''),
(193, 118, 'Payments', '_sys_pmt_tmenu_payments', 'orders.php|modules/?r={sys_payment_module_uri}/orders/|modules/?r={sys_payment_module_uri}/details/', 10, 'memb', '', '', 'bx_import(\'BxDolPayments\'); return BxDolPayments::getInstance()->isActive();', 3, 1, 1, 1, 1, 'custom', '', '', 0, '');


-- actions menu

UPDATE `sys_objects_actions` SET `Icon` = 'hand-o-right' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_greet}';

UPDATE `sys_objects_actions` SET `Icon` = 'envelope-o' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_get_mail}';

UPDATE `sys_objects_actions` SET `Icon` = 'share-square-o' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_share}';

UPDATE `sys_objects_actions` SET `Icon` = 'exclamation-circle' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_report}';

UPDATE `sys_objects_actions` SET `Icon` = 'ban' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_block}';

UPDATE `sys_objects_actions` SET `Icon` = 'paperclip' WHERE `Type` = 'Profile' AND `Caption` = '{sbs_profile_title}';

UPDATE `sys_objects_actions` SET `Icon` = 'ban' WHERE `Type` = 'Profile' AND `Caption` = '{cpt_unblock}';

UPDATE `sys_objects_actions` SET `Icon` = 'tachometer' WHERE `Type` = 'ProfileTitle' AND `Caption` = '{cpt_am_profile_account_page}';

DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Caption` IN('{cpt_activate}', '{cpt_ban}', '{cpt_delete}', '{cpt_delete_spam}', '{cpt_feature}');
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{cpt_activate}', 'check', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_activate}&ID={ID}'', false, ''post''); return false;', '', 11, 'Profile', 0),
('{cpt_ban}', 'exclamation-circle', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_ban}&ID={ID}'', false, ''post''); return false;', '', 12, 'Profile', 0),
('{cpt_delete}', 'times', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete&ID={ID}'', false, ''post''); return false;', '', 13, 'Profile', 0),
('{cpt_delete_spam}', 'times', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete_spam&ID={ID}'', false, ''post''); return false;', '', 14, 'Profile', 0),
('{cpt_feature}', 'asterisk', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action={act_feature}&ID={ID}'', false, ''post''); return false;', '', 15, 'Profile', 0);


-- injections

DELETE FROM `sys_injections` WHERE `name` IN('site_search', 'site_service_menu', 'sys_confirm_popup');
INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('sys_confirm_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxConfirm.html'', array());', '0', '1');

DELETE FROM `sys_injections_admin` WHERE `name` IN('sys_confirm_popup');
INSERT INTO `sys_injections_admin` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('sys_confirm_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxConfirm.html'', array());', '0', '1');


-- DNSBL lists

UPDATE `sys_dnsbl_rules` SET `zonedomain` = 'dnsbl.tornevall.org.' WHERE `chain` = 'spammers' AND `zonedomain` = 'opm.tornevall.org.' AND `postvresp` = '230';

DELETE FROM `sys_dnsbl_rules` WHERE `zonedomain` = 'uribl.swinog.ch.';
INSERT INTO `sys_dnsbl_rules` (`chain`, `zonedomain`, `postvresp`, `url`, `recheck`, `comment`, `added`, `active`) VALUES
('spammers', 'uribl.swinog.ch.', '127.0.0.3', 'http://antispam.imp.ch/06-dnsbl.php?lng=1', '', 'ImproWare Antispam', 1393336086, 0),
('uridns', 'uribl.swinog.ch.', 'any', 'http://antispam.imp.ch/05-uribl.php?lng=1', '', 'ImproWare Antispam', 1393336170, 0);


-- TinyMCE integration 

UPDATE `sys_objects_editor` SET `skin` = 'lightgray' WHERE `object` = 'sys_tinymce';


-- member info objects

DELETE FROM `sys_objects_member_info` WHERE `object` IN('sys_first_name', 'sys_first_name_last_name', 'sys_last_name_firs_name', 'sys_headline', 'sys_full_name', 'sys_avatar_2x', 'sys_avatar_icon_2x');

INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('sys_full_name', '_sys_member_info_full_name', 'name', '', ''),
('sys_avatar_2x', '_sys_member_thumb_avatar_2x', 'thumb_2x', '', ''),
('sys_avatar_icon_2x', '_sys_member_thumb_icon_avatar_2x', 'thumb_icon_2x', '', '');


-- payment objects

CREATE TABLE IF NOT EXISTS `sys_objects_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `uri` varchar(32) NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- delete unused language keys

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Are you sure?','_FieldCaption_FirstName_Edit','_FieldCaption_FirstName_Join','_FieldCaption_FirstName_View','_FieldCaption_LastName_Edit','_FieldCaption_LastName_Join','_FieldCaption_LastName_View','_FieldCaption_Security Image_Join','_FieldDesc_FirstName_Join','_FieldDesc_LastName_Join','_FieldError_FirstName_Mandatory','_FieldError_FirstName_Max','_FieldError_FirstName_Min','_FieldError_LastName_Mandatory','_FieldError_LastName_Max','_FieldError_LastName_Min','_Join now','_MEMBERSHIP_EXPIRES_IN_DAYS','_MEMBERSHIP_EXPIRES_TODAY','_Member Login','_adm_pbuilder_Caption_Cache','_adm_pbuilder_Info_Cache','_adm_txt_modules_wrong_permissions','_are you sure?','_in_x_minute','_sys_member_info_first_name','_sys_member_info_first_name_last_name','_sys_member_info_last_name_firs_name','_x_minute_ago');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Are you sure?','_FieldCaption_FirstName_Edit','_FieldCaption_FirstName_Join','_FieldCaption_FirstName_View','_FieldCaption_LastName_Edit','_FieldCaption_LastName_Join','_FieldCaption_LastName_View','_FieldCaption_Security Image_Join','_FieldDesc_FirstName_Join','_FieldDesc_LastName_Join','_FieldError_FirstName_Mandatory','_FieldError_FirstName_Max','_FieldError_FirstName_Min','_FieldError_LastName_Mandatory','_FieldError_LastName_Max','_FieldError_LastName_Min','_Join now','_MEMBERSHIP_EXPIRES_IN_DAYS','_MEMBERSHIP_EXPIRES_TODAY','_Member Login','_adm_pbuilder_Caption_Cache','_adm_pbuilder_Info_Cache','_adm_txt_modules_wrong_permissions','_are you sure?','_in_x_minute','_sys_member_info_first_name','_sys_member_info_first_name_last_name','_sys_member_info_last_name_firs_name','_x_minute_ago');


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.2.0', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.2.0';

