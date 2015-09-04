
-- ====================== can NOT be applied twice ====================== 

ALTER TABLE `sys_acl_levels` ADD `Order` int(11) NOT NULL default '0';

-- ================ can be safely applied multiple times ================ 

-- zip codes

DROP TABLE IF EXISTS `sys_zip_codes`;

-- profile actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND (`Caption` = '{cpt_fave}' OR `Caption` = '{cpt_befriend}' OR `Caption` = '{cpt_greet}' OR `Caption` = '{cpt_get_mail}' OR `Caption` = '{cpt_report}' OR `Caption` = '{cpt_block}' OR `Caption` = '{cpt_remove_friend}' OR `Caption` = '{cpt_unblock}');

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{cpt_fave}', 'action_fave.png', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn "$.post(''list_pop.php?action=hot'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 3, 'Profile', 0),
('{cpt_befriend}', 'action_friends.png', '', '{evalResult}', 'if ({ID} == {member_id} OR is_friends({ID} , {member_id})) return;\r\n\r\nreturn "$.post(''list_pop.php?action=friend'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 4, 'Profile', 0),
('{cpt_greet}', 'action_greet.png', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn "$.post(''greet.php'', { sendto: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 5, 'Profile', 0),
('{cpt_get_mail}', 'action_email.png', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\n$bAnonymousMode  = ''{anonym_mode}'';\r\n\r\nif ( !$bAnonymousMode ) {\r\n    return "$.post(''freemail.php'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n}\r\n', 6, 'Profile', 0),
('{cpt_report}', 'action_report.png', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=spam'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 8, 'Profile', 0),
('{cpt_block}', 'action_block.png', '', '{evalResult}', 'if ( {ID} == {member_id} || isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=block'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0),
('{cpt_remove_friend}', 'action_friends.png', '', '{evalResult}', 'if ({ID} == {member_id} OR !is_friends({ID} , {member_id}) ) return;\r\n\r\nreturn "$.post(''list_pop.php?action=remove_friend'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 4, 'Profile', 0),
('{cpt_unblock}', 'action_block.png', '', '{evalResult}', 'if ({ID} == {member_id} || !isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn "$.post(''list_pop.php?action=unblock'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0);

-- profile fields

DELETE FROM `sys_profile_fields` WHERE `Name` = 'Membership' AND `Type` = 'system';

-- admin menu

SET @iMenuAdminParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'builders');
SET @iMenuAdminOrder = (SELECT MAX(`order`) + 1 FROM `sys_menu_admin` WHERE `parent_id` = @iMenuAdminParentId);
DELETE FROM `sys_menu_admin` WHERE `name` = 'mobile_pages';
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iMenuAdminParentId, 'mobile_pages', '_adm_mmi_mobile_pages', '{siteAdminUrl}mobileBuilder.php', 'Mobile pages builder', 'mmi_mobile_builder.png', '', '', @iMenuAdminOrder);

-- pages blocks builder

DELETE FROM `sys_page_compose` WHERE `Page` = 'pedit' AND `Desc` = 'Profile membership';
INSERT INTO `sys_page_compose` VALUES(NULL, 'pedit', '998px', 'Profile membership', '_edit_profile_membership', 2, 2, 'Membership', '', 1, 50, 'memb', 0, 0);

INSERT IGNORE INTO `sys_page_compose_pages` VALUES('profile_private', 'Profile Private', 15, 1);
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile_private' AND `Desc` = 'Actions that other members can do';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile_private' AND `Desc` = 'Some text to explain why this profile can not be viewed. Translation for this block is stored in ''_sys_profile_private_text'' language key.';
INSERT INTO `sys_page_compose` VALUES(NULL, 'profile_private', '998px', 'Actions that other members can do', '_Actions', 1, 0, 'ActionsMenu', '', 1, 34, 'non,memb', 0, 0);
INSERT INTO `sys_page_compose` VALUES(NULL, 'profile_private', '998px', 'Some text to explain why this profile can not be viewed. Translation for this block is stored in ''_sys_profile_private_text'' language key.', '_sys_profile_private_text_title', 2, 0, 'PrivacyExplain', '', 1, 66, 'non,memb', 0, 0);

-- options

INSERT IGNORE INTO `sys_options` VALUES('db_clean_mem_levels', '30', 11, 'Clean expired membership levels ( days )', 'digit', '', '', NULL, '');
DELETE FROM `sys_options` WHERE `Name` = 'enable_zip_loc';
DELETE FROM `sys_options` WHERE `Name` = 'enable_new_dhtml_popups';

-- mobile pages builder

CREATE TABLE IF NOT EXISTS `sys_menu_mobile_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page` (`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

TRUNCATE TABLE `sys_menu_mobile_pages`;

INSERT INTO `sys_menu_mobile_pages` (`id`, `page`, `title`, `order`) VALUES
(1, 'homepage', '_adm_mobile_page_homepage', 1),
(2, 'profile', '_adm_mobile_page_profile', 2);


CREATE TABLE IF NOT EXISTS `sys_menu_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `page` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `action` int(11) NOT NULL,
  `action_data` varchar(255) NOT NULL,
  `eval_bubble` text NOT NULL,
  `eval_hidden` text NOT NULL,
  `order` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

TRUNCATE TABLE `sys_menu_mobile`;

INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('system', 'homepage', '_sys_mobile_status', 'home_status.png', 1, '', '', '', 1, 1),
('system', 'homepage', '_sys_mobile_mail', 'home_messages.png', 3, '', 'return getNewLettersNum({member_id});', '', 2, 1),
('system', 'homepage', '_sys_mobile_friends', 'home_friends.png', 4, '', 'return getFriendRequests({member_id});', '', 3, 1),
('system', 'homepage', '_sys_mobile_info', 'home_info.png', 5, '', '', '', 4, 1),
('system', 'homepage', '_sys_mobile_search', 'home_search.png', 6, '', '', '', 5, 1),
('system', 'profile', '_sys_mobile_profile_info', '', 8, '', '', '', 1, 1),
('system', 'profile', '_sys_mobile_profile_contact', '', 3, '', '', '', 2, 1),
('system', 'profile', '_sys_mobile_profile_friends', '', 4, '', 'return getFriendNumber(''{profile_id}'');', '', 3, 1);

-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Set membership', '_starts immediately', '_friend requests', '_Friend requests', '_adm_admtools_Host_Params', '_adm_admtools_Name', '_adm_admtools_Value', '_adm_admtools_Recommended', '_adm_admtools_Different_settings', '_adm_admtools_Installed_apache_modules');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Set membership', '_starts immediately', '_friend requests', '_Friend requests', '_adm_admtools_Host_Params', '_adm_admtools_Name', '_adm_admtools_Value', '_adm_admtools_Recommended', '_adm_admtools_Different_settings', '_adm_admtools_Installed_apache_modules');


-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.0.7', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.0.7';

