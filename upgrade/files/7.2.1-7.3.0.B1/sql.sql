

-- ================ can be safely applied multiple times ================ 


DELETE FROM `sys_menu_admin` WHERE `name` = 'privacy';
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'settings');
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'privacy', '_adm_mmi_privacy', '{siteAdminUrl}privacy.php', 'Privacy settings', 'lock col-blue2', '', '', 8);



DELETE FROM `sys_options` WHERE `Name` = 'ban_duration';
SET @iCatModeration = 6;
INSERT INTO `sys_options` VALUES
('ban_duration', '10', @iCatModeration, 'Profile ban duration (in days)', 'digit', '', '', 50, '');



UPDATE `sys_options` SET `VALUE` = '<div class="bx-splash bx-def-round-corners" style="background-image: url(templates/base/images/bx_splash_image.jpg);"><div class="bx-splash-txt"><div class="bx-splash-txt-cnt"><div class="bx-splash-txt-l1 bx-def-padding-sec-leftright"><h1 class="bx-cd-headline zoom"><span class="bx-cd-words-wrapper"><b class="bx-cd-word is-visible">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b></span></h1></div><div class="bx-splash-actions bx-hide-when-logged-in"><button class="bx-btn bx-btn-primary bx-btn-sa-join">Join</button><button class="bx-btn bx-def-margin-left bx-btn-sa-login">Login</button></div></div></div></div>' WHERE `Name` = 'splash_code' AND `VALUE` = '<div class="bx-splash bx-def-round-corners" style="background-image: url(templates/base/images/bx_splash_image.jpg);"><div class="bx-splash-txt"><div class="bx-splash-txt-cnt"><div class="bx-splash-txt-l1 bx-def-padding-sec-leftright"><h1 class="bx-cd-headline zoom"><span class="bx-cd-words-wrapper"><b class="bx-cd-word is-visible">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b><b class="bx-cd-word">Be The Gift You Bring!</b></span></h1></div><div class="bx-splash-actions"><button class="bx-btn bx-btn-primary bx-btn-sa-join">Join</button><button class="bx-btn bx-def-margin-left bx-btn-sa-login">Login</button></div></div></div></div>';



DELETE FROM `sys_page_compose` WHERE `Func` = 'Sample' AND `Content` = 'Text';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('', '1140px', 'Simple Text Block', '_Text Block', 0, 0, 'Sample', 'Text', 11, 0, 'non,memb', 0, 0);



SET @iMatchPercentSex = (SELECT `MatchPercent` FROM `sys_profile_fields` WHERE `Name` = 'Sex');
SET @iMatchPercentLookingFor = (SELECT `MatchPercent` FROM `sys_profile_fields` WHERE `Name` = 'LookingFor');
SET @iMatchPercentCountry = (SELECT `MatchPercent` FROM `sys_profile_fields` WHERE `Name` = 'Country');
SET @iMatchPercentRelationshipStatus = (SELECT `MatchPercent` FROM `sys_profile_fields` WHERE `Name` = 'RelationshipStatus');
SET @iMatchPercentEthnicity = (SELECT `MatchPercent` FROM `sys_profile_fields` WHERE `Name` = 'Ethnicity');

UPDATE `sys_profile_fields` SET `MatchPercent` = 30 WHERE `Name` = 'Sex' AND @iMatchPercentSex = 15 AND @iMatchPercentLookingFor = 30 AND @iMatchPercentCountry = 40 AND @iMatchPercentRelationshipStatus = 10 AND @iMatchPercentEthnicity = 5;
UPDATE `sys_profile_fields` SET `MatchPercent` = 25 WHERE `Name` = 'Country' AND @iMatchPercentSex = 15 AND @iMatchPercentLookingFor = 30 AND @iMatchPercentCountry = 40 AND @iMatchPercentRelationshipStatus = 10 AND @iMatchPercentEthnicity = 5;



UPDATE `sys_menu_top` SET `Deletable` = 0 WHERE `Name` IN('Home', 'People', 'All members', 'Search Members', 'View My Profile', 'Mail Compose', 'Mail Inbox', 'Mail Outbox', 'Mail Trash', 'Edit My Profile', 'Online Members', 'View Profile', 'My Friends', 'Info', 'Member Friends', 'Join', 'Login', 'Main', 'Account home', 'Top Rated', 'Match', 'Featured', 'Privacy Groups', 'Unregister', 'Profile Info', 'About', 'Terms of Use', 'Privacy Policy', 'Activity', 'Popular', 'Birthdays', 'People Calendar', 'Search', 'Keyword Search', 'People Search', 'Help', 'FAQ', 'Contact', 'Advice', 'Help', 'About', 'Search Home', 'Mail', 'Subscriptions', 'Cart', 'Payments');



UPDATE `sys_objects_actions` SET `Script` = 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete&ID={ID}'', false, ''post'', true); return false;' WHERE `Caption` = '{cpt_delete}' AND `Type` = 'Profile';
UPDATE `sys_objects_actions` SET `Script` = 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''list_pop.php?action=delete_spam&ID={ID}'', false, ''post'', true); return false;' WHERE `Caption` = '{cpt_delete_spam}' AND `Type` = 'Profile';



DELETE FROM `sys_injections` WHERE `name` = 'sys_prompt_popup';
INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('sys_prompt_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxPrompt.html'', array());', '0', '1');



DELETE FROM `sys_injections_admin` WHERE `name` = 'sys_prompt_popup';
INSERT INTO `sys_injections_admin` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('sys_prompt_popup', '0', 'injection_footer', 'php', 'return $GLOBALS[''oSysTemplate'']->parseHtmlByName(''transBoxPrompt.html'', array());', '0', '1');



UPDATE `sys_box_download` SET `icon` = 'apple' WHERE `icon` = 'iphone.png';
UPDATE `sys_box_download` SET `icon` = 'android' WHERE `icon` = 'android.png';


-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.3.0.B1' WHERE `Name` = 'sys_tmp_version';

