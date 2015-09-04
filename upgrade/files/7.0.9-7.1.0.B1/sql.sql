
-- ====================== can NOT be applied twice ====================== 

-- bottom menu
ALTER TABLE `sys_menu_bottom` 
ADD `Visible` set('non','memb') NOT NULL DEFAULT '',
ADD `Active` tinyint(1) NOT NULL DEFAULT '1',
ADD `Movable` tinyint(1) NOT NULL DEFAULT '1',
ADD `Clonable` tinyint(1) NOT NULL DEFAULT '1',
ADD `Editable` tinyint(1) NOT NULL DEFAULT '1',
ADD `Deletable` tinyint(1) NOT NULL DEFAULT '1';

-- albums
ALTER TABLE `sys_albums` ADD KEY `Owner` (`Owner`);

-- flash board
ALTER TABLE `RayBoardCurrentUsers` 
CHANGE `Nick` `Nick` VARCHAR(255) NOT NULL,
ADD `Desc` varchar(255) NOT NULL AFTER `Profile`;


-- ================ can be safely applied multiple times ================ 

-- tables modifications: email templates 
ALTER TABLE `sys_email_templates` CHANGE `ID` `ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- tables modifications: options
ALTER TABLE `sys_options` CHANGE `Type` `Type` ENUM('digit', 'text', 'checkbox', 'select', 'select_multiple', 'file', 'list' ) NOT NULL DEFAULT 'digit';

-- tables modifications: page builder
ALTER TABLE `sys_page_compose` CHANGE `PageWidth` `PageWidth` VARCHAR(10) NOT NULL DEFAULT '1140px';

-- tables modifications: delete tables
DROP TABLE IF EXISTS `sys_admin_dashboard`, `sys_transactions`;


-- admin menu
UPDATE `sys_menu_admin` SET `icon` = 'group col-green1', `icon_large` = 'group' WHERE `name` = 'users' AND `parent_id` = 0;
UPDATE `sys_menu_admin` SET `icon` = 'th-large col-red1', `icon_large` = 'th-large', `name` = 'modules', `title` = '_adm_mmi_modules' WHERE `name` = 'extensions' AND `parent_id` = 0;

SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'modules' AND `parent_id` = 0);
DELETE FROM `sys_menu_admin` WHERE (`name` = 'flash_apps' OR `name` = 'manage_modules') AND `parent_id` = @iParentId;
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'manage_modules', '_adm_mmi_manage_modules', '{siteAdminUrl}modules.php', 'Manage and configure integration modules for 3d party scripts', 'plus col-red1', '', '', 0),
(@iParentId, 'flash_apps', '_adm_mmi_flash_apps', '{siteAdminUrl}flash.php', 'Flash Apps administration panel is available here', 'bolt col-red1', '', '', 1);

UPDATE `sys_menu_admin` SET `icon` = 'wrench col-green3', `icon_large` = 'wrench' WHERE `name` = 'tools' AND `parent_id` = 0;
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'tools' AND `parent_id` = 0);
DELETE FROM `sys_menu_admin` WHERE (`name` = 'mass_mailer' OR `name` = 'manage_subscribers' OR `name` = 'banners' OR `name` = 'modules' OR `name` = 'ip_blacklist' OR `name` = 'database_backup' OR `name` = 'host_tools' OR `name` = 'antispam') AND `parent_id` = @iParentId;
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'mass_mailer', '_adm_mmi_mass_mailer', '{siteAdminUrl}notifies.php', 'Using this function you are able to send a newsletter to your site members', 'envelope col-green3', '', '', 1),
(@iParentId, 'banners', '_adm_mmi_banners', '{siteAdminUrl}banners.php', 'Provides you with the ability to manage banners on your web site', 'flag col-green3', '', '', 4),
(@iParentId, 'ip_blacklist', '_adm_mmi_ip_blacklist', '{siteAdminUrl}ip_blacklist.php', 'IP Blacklist system', 'ban-circle col-green3', '', '', 6),
(@iParentId, 'database_backup', '_adm_mmi_database_backup', '{siteAdminUrl}db.php', 'Make a backup of your site database with this utility', 'download-alt col-green3', '', '', 7),
(@iParentId, 'host_tools', '_adm_mmi_host_tools', '{siteAdminUrl}host_tools.php', 'Admin Host Tools', 'hdd col-green3', '', '', 8),
(@iParentId, 'antispam', '_adm_mmi_antispam', '{siteAdminUrl}antispam.php', 'Antispam Tools', 'legal col-green3', '', '', 9),
(@iParentId, 'sitemap', '_adm_mmi_sitemap', '{siteAdminUrl}sitemap.php', 'Sitemap', 'sitemap col-green3', '', '', 10),
(@iParentId, 'cache', '_adm_mmi_cache', '{siteAdminUrl}cache.php', 'Cache', 'bolt col-green3', '', '', 11);

UPDATE `sys_menu_admin` SET `icon` = 'reorder col-red2', `icon_large` = 'reorder' WHERE `name` = 'builders' AND `parent_id` = 0;
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'builders' AND `parent_id` = 0);
DELETE FROM `sys_menu_admin` WHERE (`name` = 'navigation_menu' OR `name` = 'member_menu' OR `name` = 'profile_fields' OR `name` = 'pages_blocks' OR `name` = 'mobile_pages') AND `parent_id` = @iParentId;
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'navigation_menu', '_adm_mmi_navigation_menu', '{siteAdminUrl}nav_menu_compose.php', 'For top menu items management', 'list col-red2', '', '', 1),
(@iParentId, 'service_menu', '_adm_mmi_service_menu', '{siteAdminUrl}service_menu_compose.php', 'For top service''s menu items management', 'list col-red2', '', '', 2),
(@iParentId, 'bottom_menu', '_adm_mmi_bottom_menu', '{siteAdminUrl}bottom_menu_compose.php', 'For top bottom''s menu items management', 'list col-red2', '', '', 3),
(@iParentId, 'member_menu', '_adm_mmi_member_menu', '{siteAdminUrl}member_menu_compose.php', 'For top member''s menu items management', 'list col-red2', '', '', 4),
(@iParentId, 'profile_fields', '_adm_mmi_profile_fields', '{siteAdminUrl}fields.php', 'For member profile fields management', 'list-alt col-red2', '', '', 5),
(@iParentId, 'pages_blocks', '_adm_mmi_pages_blocks', '{siteAdminUrl}pageBuilder.php', 'Compose blocks for the site pages here', 'th-large col-red2', '', '', 6),
(@iParentId, 'mobile_pages', '_adm_mmi_mobile_pages', '{siteAdminUrl}mobileBuilder.php', 'Mobile pages builder', 'th col-red2', '', '', 7),
(@iParentId, 'predefined_values', '_adm_mmi_predefined_values', '{siteAdminUrl}preValues.php', '', 'list-ol col-red2', '', '', 8);

UPDATE `sys_menu_admin` SET `icon` = 'cogs col-blue2', `icon_large` = 'cogs' WHERE `name` = 'settings' AND `parent_id` = 0;
SET @iParentId = (SELECT `id` FROM `sys_menu_admin` WHERE `name` = 'settings' AND `parent_id` = 0);
DELETE FROM `sys_menu_admin` WHERE (`name` = 'admin_password' OR `name` = 'basic_settings' OR `name` = 'advanced_settings' OR `name` = 'languages_settings' OR `name` = 'membership_levels' OR `name` = 'email_templates' OR `name` = 'css_styles' OR `name` = 'tags_settings' OR `name` = 'database_pruning' OR `name` = 'meta_tags' OR `name` = 'moderation_settings' OR `name` = 'privacy_settings' OR `name` = 'permalinks' OR `name` = 'predefined_values' OR `name` = 'categories_settings' OR `name` = 'watermark') AND `parent_id` = @iParentId;
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(@iParentId, 'admin_password', '_adm_mmi_admin_password', '{siteAdminUrl}settings.php?cat=ap', 'Change a password for access to administration panel here', 'asterisk col-blue2', '', '', 1),
(@iParentId, 'basic_settings', '_adm_mmi_basic_settings', '{siteAdminUrl}basic_settings.php', 'For managing site system settings', 'cog col-blue2', '', '', 2),
(@iParentId, 'advanced_settings', '_adm_mmi_advanced_settings', '{siteAdminUrl}advanced_settings.php', 'More enhanced settings for your site features', 'cogs col-blue2', '', '', 3),
(@iParentId, 'languages_settings', '_adm_mmi_languages_settings', '{siteAdminUrl}lang_file.php', 'For languages management your website is using and making changes in your website content', 'globe col-blue2', '', '', 4),
(@iParentId, 'membership_levels', '_adm_mmi_membership_levels', '{siteAdminUrl}memb_levels.php', 'For setting up different membership levels, different actions for each membership level and action limits', 'star col-blue2', '', '', 5),
(@iParentId, 'email_templates', '_adm_mmi_email_templates', '{siteAdminUrl}email_templates.php', 'For setting up email texts which are sent from your website to members automatically', 'paste col-blue2', '', '', 6),
(@iParentId, 'templates', '_adm_mmi_templates', '{siteAdminUrl}templates.php', 'Templates management', 'eye-open col-blue2', '', '', 7),
(@iParentId, 'categories_settings', '_adm_mmi_categories_settings', '{siteAdminUrl}categories.php', 'Categories settings', 'folder-close col-blue2', '', '', 15),
(@iParentId, 'watermark', '_adm_mmi_watermark', '{siteAdminUrl}settings.php?cat=16', 'Setting up watermark for media content', 'certificate col-blue2', '', '', 16);

DELETE FROM `sys_menu_admin` WHERE (`name` = 'dashboard' OR `name` = 'license') AND `parent_id` = 0;
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(0, 'dashboard', '_adm_mmi_dashboard', '{siteAdminUrl}index.php', '', 'dashboard col-blue3', 'dashboard', '', 1),
(0, 'license', '_adm_mmi_license', '{siteAdminUrl}license.php', '', 'key col-red3', 'key', '', 7);

OPTIMIZE TABLE  `sys_menu_admin`;

-- admin top menu
TRUNCATE TABLE `sys_menu_admin_top`;
INSERT INTO `sys_menu_admin_top`(`name`, `caption`, `url`, `icon`, `order`) VALUES
('home', '_adm_tmi_home', '{site_url}index.php', 'globe', 1),
('info', '_adm_tmi_info', 'http://www.boonex.com/trac/dolphin/wiki/Dolphin7Docs', 'question-sign', 2),
('extensions', '_adm_tmi_extensions', 'http://www.boonex.com/market', 'shopping-cart', 3),
('logout', '_adm_tmi_logout', '{site_url}logout.php', 'off', 4);


-- service menu
CREATE TABLE IF NOT EXISTS `sys_menu_service` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(200) NOT NULL,
  `Caption` varchar(100) NOT NULL,
  `Icon` varchar(100) NOT NULL,
  `Link` varchar(250) NOT NULL,
  `Script` varchar(250) NOT NULL,
  `Target` varchar(200) NOT NULL,
  `Order` int(5) NOT NULL,
  `Visible` set('non','memb') NOT NULL DEFAULT '',
  `Active` tinyint(1) NOT NULL DEFAULT '1',
  `Movable` tinyint(1) NOT NULL DEFAULT '1',
  `Clonable` tinyint(1) NOT NULL DEFAULT '1',
  `Editable` tinyint(1) NOT NULL DEFAULT '1',
  `Deletable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_menu_service`;
INSERT INTO `sys_menu_service` (`Name`, `Caption`, `Icon`, `Link`, `Script`, `Target`, `Order`, `Visible`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`) VALUES
('Join', '_sys_sm_join', '', 'join.php', '', '', 1, 'non', 1, 3, 1, 1, 1),
('Login', '_sys_sm_login', '', '', 'showPopupLoginForm(); return false;', '', 2, 'non', 1, 3, 1, 1, 1),
('Profile', '_sys_sm_profile', '', '{memberLink}|{memberNick}|change_status.php', '', '', 1, 'memb', 1, 3, 1, 1, 1),
('Account', '_sys_sm_account', '', 'member.php', '', '', 2, 'memb', 1, 3, 1, 1, 1),
('Logout', '_sys_sm_logout', '', 'logout.php?action=member_logout', '', '', 3, 'memb', 1, 3, 1, 1, 1);

-- bottom menu
UPDATE `sys_menu_bottom` SET `Movable` = 3, `Visible` = 'non,memb';

-- objects: comments
UPDATE `sys_objects_cmts` SET `AnimationEffect` = 'none', `AnimationSpeed` = 0, `IsMood` = 0;

-- email templates 
DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_Activation', 't_AdminEmail', 't_AdminStats', 't_Compose', 't_Confirmation', 't_CupidMail', 't_Forgot', 't_FreeEmail', 't_MemExpiration', 't_MemChanged', 't_Message', 't_UserJoined', 't_UserConfirmed', 't_Rejection', 't_SpamReport', 't_TellFriend', 't_TellFriendProfile', 't_VKiss', 't_VKiss_visitor', 't_MessageCopy', 't_Subscription', 't_sbsProfileComments', 't_sbsProfileRates', 't_sbsProfileEdit', 't_FriendRequest', 't_FriendRequestAccepted', 't_SpamReportAuto');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_Activation', 'Your Profile Is Now Active', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your profile was reviewed and activated !</p>\r\n\r\n<p>Your Account: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<p>Member ID: <b><recipientID></b></p>\r\n\r\n<p>Your E-mail: <span style="color:#FF6633"><Email></span></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile activation notification.', 0),
('t_AdminEmail', '<SiteName> Admin: <MessageSubject>', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<Domain>"><SiteName></a> Admin sent you a message:</p>\r\n\r\n<hr>\r\n<p style="color:#3B5C8E"><MessageText></p>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message from the site Admin.', 0),
('t_AdminStats', 'Content Pending Admin Review', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><MessageText></p>\r\n\r\n<p>Go to <a href="<ViewLink>">administration panel</a> to review pending content.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message to admin about content pending review', 0),
('t_Compose', 'You''ve Got A New Message', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>You have received a message from <a href="<ProfileUrl>"><ProfileReference></a>!</p>\r\n\r\n<p>Go to your account to read and reply: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New message notification without message text', 0),
('t_Confirmation', 'Email Confirmation Request', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Thank you for registering at <SiteName>!</p>\r\n\r\n<p>Click to confirm your email:\r\n<a href="<ConfirmationLink>"><ConfirmationLink></a></p>\r\n\r\n<p>CONFIRMATION CODE: <ConfCode></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Email confirmation message.', 0),
('t_CupidMail', 'Your Matches', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Check out some <SiteName> members that may be a good match with you:</p>\r\n\r\n<p><a href="<MatchProfileLink>"><MatchProfileLink></a></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Matching profiles notification.', 0),
('t_Forgot', 'Your Account Password', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Your member ID: <b><recipientID></b></p>\r\n\r\n<p>Your password: <b><Password></b></p>\r\n\r\n<p>You may login here: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Password retrieval', 0),
('t_FreeEmail', 'Member Email Address', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><profileNickName>''s contact info:</p> \r\n\r\n<p><profileContactInfo></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Requested Member Email Address', 0),
('t_MemExpiration', 'Your Membership Is About To Expire', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Your <SiteName> <MembershipName> membership will expire in <ExpireDays> days.</p>\r\n\r\n<p>NOTE: -1 means it has already expired</p>\r\n\r\n\r\n<p><a href="<Domain>modules/?r=membership/index/">Renew Membership</a></p>\r\n<bx_include_auto:_email_footer.html />', 'Membership expiration notice', 0),
('t_MemChanged', 'Your Membership Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <RealName></b>,</p>\r\n\r\n<p>Your membership level was changed to: <b><a href="<Domain>modules/?r=membership/index/"><MembershipLevel></a></b></p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Membership change', 0),
('t_Message', 'You''ve Got A New Message', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<ProfileUrl>"><ProfileReference></a> sent you a message: </p>\r\n\r\n<hr>\r\n<p><MessageText></p>\r\n<hr>\r\n\r\n<p>Go to your account to reply: <a href="<Domain>member.php"><Domain>member.php</a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New message notification with message text', 0),
('t_UserJoined', 'New Member Joined', '<bx_include_auto:_email_header.html />\r\n\r\n<p>New user: <RealName></p> \r\n<p>Email: <Email></p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification about new member', 0),
('t_UserConfirmed', 'User Confirmed Email', '<bx_include_auto:_email_header.html />\r\n\r\n<p>User: <RealName></p> \r\n<p>Email: <Email></p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Admin notification - user confirmed email', 0),
('t_SpamReport', 'Profile Spam Report', '<bx_include_auto:_email_header.html />\r\n\r\n<p><a href="<Domain>profile.php?ID=<reporterID>"><reporterNick></a> reported Profile SPAM:  <a href="<Domain>profile.php?ID=<spamerID>"><b><spamerNick></b></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile Spam Report', 0),
('t_TellFriend', 'Check This Out!', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p>I thought you''d be interested: <a href="<Link>"><Link></a><br />\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Friend Invitation', 0),
('t_TellFriendProfile', 'Look At This Profile', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n\r\n<p>Check out this profile: <a href="<Link>"><Link></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Email profile to a friend', 0),
('t_VKiss', 'Greeting notification', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><ProfileReference> sent you a greeting!</p>\r\n\r\n<p><ProfileReference> may be interested in you or maybe just wants to say Hello!\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Greeting notification ', 0),
('t_VKiss_visitor', 'Greeting notification', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Our site visitor sent you a greeting!</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Greeting from visitor notification', 0),
('t_MessageCopy', 'Copy Of Your Message : <your subject here>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>You wrote:</p>\r\n<hr>\r\n<p><your message here></p>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Message copy', 0),
('t_Subscription', 'Your Subscription', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>You subscribed to <a href="<ViewLink>"><Subscription></a></p>\r\n\r\n<p>You may cancel the subscription here: <a href="<SysUnsubscribeLink>"><SysUnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription confirmation', 0),
('t_sbsProfileComments', 'New Profile Comments', '<bx_include_auto:_email_header.html />\r\n\r\n <p><b>Dear <RealName></b>,</p>\r\n\r\n<p>Profile you subscribed to got <a href="<ViewLink>">new comments</a>.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New comments to profile subscription', 0),
('t_sbsProfileEdit', 'Subscription: Profile Edited', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p><a href="<ViewLink>">Profile you subscribed to</a> has been updated.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile info subscription.', 0),
('t_FriendRequest', 'Friendship Request', '<bx_include_auto:_email_header.html />\r\n\r\n    <p><b>Dear <Recipient></b>,</p>\r\n   \r\n    <p><a href="<SenderLink>"><Sender></a> wants to be friends with you. <a href="<RequestLink>">Respond</a>.</p>\r\n    <br /> \r\n    <bx_include_auto:_email_footer.html />', 'Friendship Request', 0),
('t_FriendRequestAccepted', 'Friendship Request Accepted', '<bx_include_auto:_email_header.html />\r\n\r\n    <p><b>Dear <Recipient></b>,</p>\r\n    \r\n    <p><a href="<SenderLink>"><Sender></a> accepted your friendship request.</p>\r\n    <br /> \r\n    <bx_include_auto:_email_footer.html />', 'Friendship Request Accepted', 0),
('t_SpamReportAuto', '<SiteName> Automatic Spam Report', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<b>Profile:</b> <a href="<SpammerUrl>"><SpammerNickName></a><br />\r\n\r\n<b>Page:</b> <Page><br />\r\n\r\n<b>GET variables:</b>\r\n<pre>\r\n<Get>\r\n</pre>\r\n\r\n<b>Spam Content:</b>\r\n<pre>\r\n<SpamContent>\r\n</pre>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Automatic spam report', 0);


-- member menu
DELETE FROM `sys_menu_member` WHERE `Name` IN ('Profile', 'Mail', 'Friends', 'Settings', 'Log Out', 'MemberBlock', 'Status Message', 'Dashboard', 'Language', 'Admin Panel', 'AddContent');
INSERT INTO `sys_menu_member` (`ID`, `Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Order`, `Active`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Parent`, `Bubble`, `Description`) VALUES 
(6, '{evalResult}', 'MemberBlock', '', '{ProfileLink}', '', 'return ''<b>'' . getNickName({ID}) . ''</b>'';', 'bx_import(''BxDolUserStatusView'');\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView->getMemberMenuStatuses();', 1, '1', 3, 1, 0, 0, '', 'top', 'link', 0, '', '_Presence'),
(4, '_Settings', 'Settings', 'cog', 'pedit.php?ID={ID}', '', '', '', 2, 1, 3, 1, 0, 0, '', 'top', 'link', 0, '', '_Edit_profile_and_settings'),
(7, '_Status Message', 'Status Message', 'edit', 'javascript:void(0);', '', '', 'bx_import( ''BxDolUserStatusView'' );\r\n$oStatusView = new BxDolUserStatusView();\r\nreturn $oStatusView -> getStatusField({ID});', 3, 1, 3, 1, 1, 1, '', 'top', 'link', 0, '', '_Status Message'),
(8, '_sys_add_content', 'AddContent', 'plus', 'javascript:void(0);', '', '', 'return '''';', 4, '1', 3, 0, 0, 0, '', 'top', 'link', 0, '$isSkipItem = $aReplaced[$sPosition][$iKey][''linked_items''] ? false : true;', '_sys_add_content'),

(2, '_Mail', 'Mail', 'envelope', 'mail.php?mode=inbox', '', '', 'bx_import( ''BxTemplMailBox'' );\r\n// return list of messages ;\r\nreturn BxTemplMailBox::get_member_menu_messages_list({ID});', 1, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import( ''BxTemplMailBox'' );\r\n// return list of new messages ;\r\n$aRetEval= BxTemplMailBox::get_member_menu_bubble_new_messages({ID}, {iOldCount});', '_Mail'),
(3, '_Friends', 'Friends', 'user', 'viewFriends.php?iUser={ID}', '', '', 'bx_import( ''BxDolFriendsPageView'' );\r\nreturn BxDolFriendsPageView::get_member_menu_friends_list({ID});', 2, 1, 3, 1, 0, 0, '', 'top_extra', 'link', 0, 'bx_import( ''BxDolFriendsPageView'' );\r\n$aRetEval = BxDolFriendsPageView::get_member_menu_bubble_friend_requests( {ID}, {iOldCount});', '_Friends'),
(10, '_Admin Panel', 'Admin Panel', 'wrench', '{evalResult}', '', 'return isAdmin() ? $GLOBALS[''site''][''url_admin''] : '''';', '', 6, 1, 3, 1, 1, 1, '', 'top_extra', 'link', 0, '', '_Go admin panel');


-- options
DROP TABLE IF EXISTS `sys_options_tmp`;
CREATE TEMPORARY TABLE `sys_options_tmp` (
  `n` varchar(64) NOT NULL default '',
  `v` mediumtext NOT NULL,
  PRIMARY KEY  (`n`)
) DEFAULT CHARSET=utf8;
INSERT INTO `sys_options_tmp` SELECT `Name`, `VALUE` FROM `sys_options`;
DELETE FROM `sys_options` WHERE `Name` IN ('anon_mode', 'autoApproval_ifJoin', 'autoApproval_ifProfile', 'cmdDay', 'currency_code', 'currency_sign', 'time_format_php', 'short_date_format_php', 'date_format_php', 'time_format', 'short_date_format', 'date_format', 'db_clean_msg', 'db_clean_profiles', 'db_clean_members_visits', 'db_clean_banners_info', 'db_clean_vkiss', 'db_clean_mem_levels', 'default_country', 'enable_contact_form', 'enable_gd', 'enable_match', 'view_match_percent', 'enable_promotion_membership', 'enable_watermark', 'expire_notification_days', 'expire_notify_once', 'featured_num', 'lang_default', 'match_percent', 'max_inbox_message_size', 'member_online_time', 'MetaDescription', 'MetaKeyWords', 'msgs_per_start', 'news_enable', 'feeds_enable', 'newusernotify', 'promotion_membership_days', 'search_start_age', 'search_end_age', 'sys_calendar_starts_sunday', 'top_members_max_num', 'track_profile_view', 'transparent1', 'votes', 'Water_Mark', 'zodiac', 'php_date_format', 'tags_non_parsable', 'tags_last_parse_time', 'tags_min_rating', 'autoApproval_ifNoConfEmail', 'enable_flash_promo', 'custom_promo_code', 'license_code', 'license_expiration', 'license_checksum', 'sys_html_fields', 'sys_json_fields', 'sys_exceptions_fields', 'enable_dolphin_footer', 'enable_modrewrite', 'cupid_last_cron', 'reg_by_inv_only', 'main_div_width', 'promoWidth', 'boonexAffID', 'enable_member_store_ip', 'ipBlacklistMode', 'ipListGlobalType', 'site_email', 'site_title', 'site_email_notify', 'enable_tiny_in_comments', 'enable_global_couple', 'galleryFiles_user', 'galleryFiles_keyword', 'enable_cache_system', 'nav_menu_elements_on_line_usr', 'nav_menu_elements_on_line_gst', 'enable_guest_comments', 'enable_cmts_profile_delete', 'permalinks_browse', 'promo_relocation_link_visitor', 'promo_relocation_link_member', 'leaders_male_types', 'leaders_female_types', 'useLikeOperator', 'ext_nav_menu_top_position', 'ext_nav_menu_enabled', 'sys_ps_enabled_group_1', 'sys_ps_enabled_group_2', 'sys_ps_enabled_group_3', 'sys_ps_enabled_group_4', 'sys_ps_enabled_group_5', 'sys_ps_enabled_group_6', 'sys_ps_enabled_group_7', 'sys_make_album_cover_last', 'sys_album_auto_app', 'sys_album_default_name', 'sys_user_info_timeout', 'template', 'enable_template', 'sys_template_cache_enable', 'sys_template_cache_engine', 'sys_template_cache_image_enable', 'sys_template_cache_image_max_size', 'sys_template_cache_css_enable', 'sys_template_cache_js_enable', 'sys_template_cache_compress_enable', 'sys_template_page_width_min', 'sys_template_page_width_max', 'sys_main_logo', 'tags_perpage_browse', 'tags_show_limit', 'sys_ftp_login', 'sys_ftp_password', 'sys_ftp_dir', 'categ_perpage_browse', 'categ_show_limit', 'categ_show_columns', 'sys_security_impact_threshold_log', 'sys_security_impact_threshold_block', 'friends_per_page', 'sys_tmp_version', 'sys_security_form_token_enable', 'sys_security_form_token_lifetime', 'sys_db_cache_enable', 'sys_db_cache_engine', 'sys_cache_memcache_host', 'sys_cache_memcache_port', 'sys_pb_cache_enable', 'sys_pb_cache_engine', 'sys_mm_cache_engine', 'sys_dnsbl_enable', 'sys_uridnsbl_enable', 'sys_akismet_enable', 'sys_akismet_api_key', 'sys_antispam_block', 'sys_antispam_report');
DELETE FROM `sys_options` WHERE `Name` IN ('sys_recaptcha_key_private', 'sys_recaptcha_key_public', 'sys_ps_enable_default_values', 'sys_ps_enable_create_group', 'sys_member_info_thumb_icon', 'sys_member_info_thumb', 'sys_member_info_info', 'sys_member_info_name', 'splash_editor', 'splash_code', 'splash_visibility', 'splash_logged', 'sys_show_admin_help', 'sys_sitemap_enable', 'sys_captcha_default', 'sys_editor_default');
DELETE FROM `sys_options_cats` WHERE `ID` IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 19, 23, 25, 26, 27);
DELETE FROM `sys_options_cats` WHERE `ID` IN (24, 22, 21);
-- CAT: Profiles
SET @iCatProfiles = 1;
INSERT INTO `sys_options` VALUES
('enable_global_couple', '', @iCatProfiles, 'Enable couple profiles', 'checkbox', '', '', 10, ''),
('votes', 'on', @iCatProfiles, 'Enable profile rating', 'checkbox', '', '', 20, ''),
('zodiac', '', @iCatProfiles, 'Enable zodiac signs', 'checkbox', '', '', 30, ''),
('anon_mode', '', @iCatProfiles, 'Enable anonymous mode', 'checkbox', '', '', 40, ''),
('reg_by_inv_only', '', @iCatProfiles, 'Enable registration by invitation only', 'checkbox', '', '', 50, ''),
('enable_cmts_profile_delete', '', @iCatProfiles, 'Allow profile comments deletion by profile owner', 'checkbox', '', '', 60, ''),
('member_online_time', '1', @iCatProfiles, 'Online status timeframe (minutes)', 'digit', '', '', 70, ''),
('search_start_age', '18', @iCatProfiles, 'Lowest age possible for site members', 'digit', 'return setSearchStartAge((int)$arg0);', '', 80, ''),
('search_end_age', '75', @iCatProfiles, 'Highest age possible for site members', 'digit', 'return setSearchEndAge((int)$arg0);', '', 90, ''),
('friends_per_page', '14', @iCatProfiles, 'Number of friends displayed per page in profile', 'digit', '', '', 100, ''),
('featured_num', '8', @iCatProfiles, 'Number of Featured Members per page', 'digit', '', '', 110, ''),
('top_members_max_num', '8', @iCatProfiles, 'Number of Top Members per page', 'digit', '', '', 120, ''),
('sys_member_info_name', 'sys_username', @iCatProfiles, 'Member display-name', 'select', '', '', 130, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'name\');'),
('sys_member_info_info', 'sys_headline', @iCatProfiles, 'Member brief info', 'select', '', '', 140, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'info\');'),
('sys_member_info_thumb', 'sys_avatar', @iCatProfiles, 'Member thumb', 'select', '', '', 150, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'thumb\');'),
('sys_member_info_thumb_icon', 'sys_avatar_icon', @iCatProfiles, 'Member thumb icon', 'select', '', '', 160, 'PHP:bx_import(\'BxDolMemberInfoQuery\'); return BxDolMemberInfoQuery::getMemberInfoKeysByType(\'thumb_icon\');');
-- CAT: General
SET @iCatGeneral = 3;
INSERT INTO `sys_options` VALUES
('MetaDescription', '', @iCatGeneral, 'Homepage meta-description', 'text', '', '', 10, ''),
('MetaKeyWords', '', @iCatGeneral, 'Homepage meta-keywords', 'text', '', '', 20, ''),
('enable_tiny_in_comments', '', @iCatGeneral, 'Enable WYSIWYG editor in comments', 'checkbox', '', '', 30, ''),
('sys_make_album_cover_last', 'on', @iCatGeneral, 'Enable last-added item as album cover', 'checkbox', '', '', 70, ''),
('sys_album_default_name', 'Hidden', @iCatGeneral, 'Default album name', 'digit', '', '', 80, ''),
('news_enable', 'on', @iCatGeneral, 'Enable BoonEx News in Admin', 'checkbox', '', '', 90, ''),
('feeds_enable', 'on', @iCatGeneral, 'Enable BoonEx Market Feeds in Admin', 'checkbox', '', '', 100, ''),
('enable_contact_form', 'on', @iCatGeneral, 'Enable contact form', 'checkbox', '', '', 110, ''),
('default_country', 'US', @iCatGeneral8, 'Default country', 'digit', '', '', 120, ''),
('boonexAffID', '', @iCatGeneral, 'BoonEx affiliate ID', 'digit', '', '', 140, ''),
('enable_gd', 'on', @iCatGeneral, 'Enable GD library for image processing', 'checkbox', '', '', 150, ''),
('useLikeOperator', 'on', @iCatGeneral, 'Disable full-text search', 'checkbox', '', '', 160, '');
-- CAT: Massmailer
SET @iCatMassmailer = 4;
INSERT INTO `sys_options` VALUES
('msgs_per_start', '20', @iCatMassmailer, 'Number of emails to send from queue per run, it happens every 5m-1h', 'digit', '', '', 10, '');
-- CAT: Memberships
SET @iCatMemberships = 5;
INSERT INTO `sys_options` VALUES
('expire_notify_once', 'on', @iCatMemberships, 'Notify members about membership expiration only once (every day otherwise)', 'checkbox', '', '', 10, ''),
('expire_notification_days', '1', @iCatMemberships, 'Number of days before membership expiration to notify members (-1 = after expiration)', 'digit', '', '', 20, ''),
('enable_promotion_membership', '', @iCatMemberships, 'Enable promotional membership', 'checkbox', '', '', 30, ''),
('promotion_membership_days', '7', @iCatMemberships, 'Number of days for promotional membership', 'digit', '', '', 40, '');
-- CAT: Moderation
SET @iCatModeration = 6;
INSERT INTO `sys_options` VALUES
('autoApproval_ifJoin', '', @iCatModeration, 'Auto-activate profiles after joining', 'checkbox', '', '', 10, ''),
('autoApproval_ifProfile', 'on', @iCatModeration, 'Preserve profile status after profile info editing', 'checkbox', '', '', 20, ''),
('autoApproval_ifNoConfEmail', '', @iCatModeration, 'Auto-confirm profile without confirmation email', 'checkbox', '', '', 30, ''),
('newusernotify', 'on', @iCatModeration, 'Enable notification about new members', 'checkbox', '', '', 40, ''),
('sys_album_auto_app', 'on', @iCatModeration, 'Enable albums auto-approval', 'checkbox', '', '', 50, '');
-- CAT: Site 
SET @iCatSite = 7;
INSERT INTO `sys_options` VALUES
('site_email', 'captain@example.com', @iCatSite, 'Site Email', 'digit', '', '', 10, ''),
('site_title', 'Community', @iCatSite, 'Site Title', 'digit', '', '', 20, ''),
('site_email_notify', 'no-reply@example.com', @iCatSite, 'Email to send site''s mail from', 'digit', '', '', 30, '');
-- CAT: Privacy
SET @iCatPrivacy = 9;
INSERT INTO `sys_options` VALUES
('sys_ps_enable_create_group', '', @iCatPrivacy, 'Enable \'Create New Privacy Group\'', 'checkbox', '', '', 10, ''),
('sys_ps_enable_default_values', '', @iCatPrivacy, 'Enable \'Default Values\'', 'checkbox', '', '', 20, ''),
('sys_ps_enabled_group_1', '', @iCatPrivacy, 'Enable \'Default\' group', 'checkbox', '', '', 30, ''),
('sys_ps_enabled_group_2', 'on', @iCatPrivacy, 'Enable \'Me Only\' group', 'checkbox', '', '', 40, ''),
('sys_ps_enabled_group_3', 'on', @iCatPrivacy, 'Enable \'Public\' group', 'checkbox', '', '', 50, ''),
('sys_ps_enabled_group_4', 'on', @iCatPrivacy, 'Enable \'Members\' group', 'checkbox', '', '', 60, ''),
('sys_ps_enabled_group_5', 'on', @iCatPrivacy, 'Enable \'Friends\' group', 'checkbox', '', '', 70, ''),
('sys_ps_enabled_group_6', '', @iCatPrivacy, 'Enable \'Faves\' group', 'checkbox', '', '', 80, ''),
('sys_ps_enabled_group_7', '', @iCatPrivacy, 'Enable \'Contacts\' group', 'checkbox', '', '', 90, '');
-- CAT: Pruning
SET @iCatPruning = 11;
INSERT INTO `sys_options` VALUES
('db_clean_msg', '365', @iCatPruning, 'Delete messages older than (days)', 'digit', '', '', 10, ''),
('db_clean_profiles', '0', @iCatPruning, 'Delete profiles of members that didnвЂ™t login for (days)', 'digit', '', '', 20, ''),
('db_clean_members_visits', '90', @iCatPruning, 'Delete stored members IPs older than (days)', 'digit', '', '', 30, ''),
('db_clean_banners_info', '60', @iCatPruning, 'Delete banner views and clicks data older than (days)', 'digit', '', '', 40, ''),
('db_clean_vkiss', '90', @iCatPruning, 'Delete greeting older than (days)', 'digit', '', '', 50, ''),
('db_clean_mem_levels', '30', @iCatPruning, 'Delete membership levels expired for (days)', 'digit', '', '', 60, '');
-- CAT: Matches
SET @iCatMatches = 12;
INSERT INTO `sys_options` VALUES
('enable_match', '', @iCatMatches, 'Enable matchmaking', 'checkbox', '', '', 10, ''),
('view_match_percent', '', @iCatMatches, 'Enable match percentage display', 'checkbox', '', '', 20, ''),
('match_percent', '85', @iCatMatches, 'Match percentage threshold for email notification (0-100)', 'digit', '', '', 30, '');
-- CAT: Template
SET @iCatTemplate = 13;
INSERT INTO `sys_options` VALUES
('template', 'uni', @iCatTemplate, 'Default template', 'select', 'global $dir; return (strlen($arg0) > 0 && file_exists($dir["root"]."templates/tmpl_".$arg0) ) ? true : false;', 'Template can not be empty and must be valid', 10, 'PHP:$aValues = get_templates_array(); $aResult = array(); foreach($aValues as $sKey => $sValue) $aResult[] = array(\'key\' => $sKey, \'value\' => $sValue); return $aResult;'),
('enable_template', 'on', @iCatTemplate, 'Allow users to choose templates', 'checkbox', '', '', 20, ''),
('nav_menu_elements_on_line_usr', '16', @iCatTemplate, 'Number of main menu tabs visible to members outside of "more" tab', 'digit', '', '', 30, ''),
('nav_menu_elements_on_line_gst', '16', @iCatTemplate, 'Number of main menu tabs visible to guests outside of "more" tab', 'digit', '', '', 40, ''),
('sys_template_page_width_min', '774', @iCatTemplate, 'Minimal allowed page width (pixels)', 'digit', '', '', 50, ''),
('sys_template_page_width_max', '1600', @iCatTemplate, 'Maximal allowed page width (pixels)', 'digit', '', '', 60, ''),
('ext_nav_menu_enabled', 'on', @iCatGeneral, 'Enable member menu', 'checkbox', '', '', 70, ''),
('ext_nav_menu_top_position', 'bottom', @iCatGeneral, 'Default position of member menu', 'select', '', '', 80, 'top,bottom,static');
-- CAT: Security
SET @iCatSecurity = 14;
INSERT INTO `sys_options` VALUES
('sys_security_impact_threshold_log', '-1', @iCatSecurity, 'Breach impact threshold for report', 'digit', '', '', 10, ''),
('sys_security_impact_threshold_block', '-1', @iCatSecurity, 'Breach impact threshold for report and block', 'digit', '', '', 20, ''),
('sys_security_form_token_enable', 'on', @iCatSecurity, 'Enable CSRF token in forms', 'checkbox', '', '', 30, ''),
('sys_security_form_token_lifetime', '86400', @iCatSecurity, 'SCRF token lifetime (seconds, 0 - no tracking)', 'digit', '', '', 40, ''),
('sys_recaptcha_key_public', '', @iCatSecurity, 'reCAPTCHA public key', 'digit', '', '', 50, ''),
('sys_recaptcha_key_private', '', @iCatSecurity, 'reCAPTCHA private key', 'digit', '', '', 60, '');
-- CAT: Watermark
SET @iCatWatermark = 16;
INSERT INTO `sys_options` VALUES
('enable_watermark', '', @iCatWatermark, 'Enable Watermark', 'checkbox', '', '', 10, ''),
('transparent1', '0', @iCatWatermark, 'Transparency for first image', 'digit', '', '', 20, ''),
('Water_Mark', '', @iCatWatermark, 'Water Mark', 'file', '', '', 30, '');
-- CAT: Languages
SET @iCatLanguages = 21;
INSERT INTO `sys_options` VALUES
('lang_default', 'en', @iCatLanguages, 'Default site language', 'text', '', '', 1, ''),
('sys_calendar_starts_sunday', '', @iCatLanguages, 'Enable Sunday as the first weekday', 'checkbox', '', '', '30', ''),
('time_format_php', 'H:i', @iCatLanguages, 'Time format (for code)', 'digit', '', '', 40, ''),
('short_date_format_php', 'd.m.Y', @iCatLanguages, 'Short date format (for code)', 'digit', '', '', 50, ''),
('date_format_php', 'd.m.Y H:i', @iCatLanguages, 'Long date format (for code)', 'digit', '', '', 60, ''),
('time_format', '%H:%i', @iCatLanguages, 'Time format (for database)', 'digit', '', '', 70, ''),
('short_date_format', '%d.%m.%Y', @iCatLanguages, 'Short date format (for database)', 'digit', '', '', 80, ''),
('date_format', '%d.%m.%Y %H:%i', @iCatLanguages, 'Long date format (for database)', 'digit', '', '', 90, '');
-- CAT: IP List
SET @iCatIPList = 22;
INSERT INTO `sys_options` VALUES
('enable_member_store_ip', 'on', @iCatIPList, 'Enable member IP tracking', 'checkbox', '', '', 10, ''),
('ipBlacklistMode', '2', @iCatIPList, 'IP blacklist mode (1 - total block, 2 - login block)', 'digit', '', '', 20, ''),
('ipListGlobalType', '0', @iCatIPList, 'IP list type (0 - disabled, 1 - all allowed except listed, 2 - all blocked except listed)', 'digit', '', '', 30, '');
-- CAT: Antispam
SET @iCatAntispam = 23;
INSERT INTO `sys_options` VALUES
('sys_dnsbl_enable', 'on', @iCatAntispam, 'Enable DNS Block Lists', 'checkbox', '', '', 10, ''),
('sys_uridnsbl_enable', 'on', @iCatAntispam, 'Enable URI DNS Block Lists', 'checkbox', '', '', 20, ''),
('sys_akismet_enable', '', @iCatAntispam, 'Enable Akismet', 'checkbox', '', '', 30, ''),
('sys_akismet_api_key', '', @iCatAntispam, 'Akismet API Key', 'digit', '', '', 40, ''),
('sys_antispam_block', '', @iCatAntispam, 'Total block all spam content', 'checkbox', '', '', 50, ''),
('sys_antispam_report', 'on', @iCatAntispam, 'Send report to admin if spam content discovered', 'checkbox', '', '', 60, '');
-- CAT: Caching
SET @iCatCaching = 24;
INSERT INTO `sys_options` VALUES
('enable_cache_system', 'on', @iCatCaching, 'Enable cache for profiles information', 'checkbox', '', '', 10, ''),
('sys_db_cache_enable', 'on', @iCatCaching, 'Enable DB cache', 'checkbox', '', '', 20, ''),
('sys_db_cache_engine', 'File', @iCatCaching, 'DB cache engine (other than File option may require custom server setup)', 'select', '', '', 30, 'File,EAccelerator,Memcache,APC,XCache'),
('sys_cache_memcache_host', '', @iCatCaching, 'Memcached server host', 'digit', '', '', 40, ''),
('sys_cache_memcache_port', '11211', @iCatCaching, 'Memcached server port', 'digit', '', '', 50, ''),
('sys_pb_cache_enable', 'on', @iCatCaching, 'Enable page blocks cache', 'checkbox', '', '', 60, ''),
('sys_pb_cache_engine', 'File', @iCatCaching, 'Page blocks cache engine (other than File option may require custom server setup)', 'select', '', '', 70, 'File,EAccelerator,Memcache,APC,XCache'),
('sys_mm_cache_engine', 'File', @iCatCaching, 'Member menu cache engine (other than File option may require custom server setup)', 'select', '', '', 80, 'File,EAccelerator,Memcache,APC,XCache'),
('sys_template_cache_enable', 'on', @iCatCaching, 'Enable cache for HTML files', 'checkbox', '', '', 90, ''),
('sys_template_cache_engine', 'FileHtml', @iCatCaching, 'Template cache engine (other than FileHtml option may require custom server setup)', 'select', '', '', 100, 'FileHtml,EAccelerator,Memcache,APC,XCache'),
('sys_template_cache_css_enable', 'on', @iCatCaching, 'Enable cache for CSS files', 'checkbox', '', '', 110, ''),
('sys_template_cache_js_enable', 'on', @iCatCaching, 'Enable cache for JS files', 'checkbox', '', '', 120, ''),
('sys_template_cache_compress_enable', 'on', @iCatCaching, 'Enable compression for JS/CSS files(cache must be enabled)', 'checkbox', '', '', 130, '');
-- CAT: Tags
SET @iCatTags = 25;
INSERT INTO `sys_options` VALUES
('tags_non_parsable', 'hi, hey, hello, all, i, i''m, i''d, am, for, in, to, a, the, on, it''s, is, my, of, are, from, i''m, me, you, and, we, not, will, at, where, there', @iCatTags, 'Ignored words (lower case, comma-separated)', 'text', '', '', 10, ''),
('tags_min_rating', '2', @iCatTags, 'Minimum tag repeats to display for browsing', 'digit', '', '', 20, ''),
('tags_perpage_browse', '30', @iCatTags, 'Number of tags per page displayed for browsing', 'digit', '', '', 30, ''),
('tags_show_limit', '50', @iCatTags, 'Number of hot tags displayed for browsing', 'digit', '', '', 40, '');
-- CAT: Permalinks
SET @iCatPermalinks = 26;
INSERT INTO `sys_options` VALUES
('enable_modrewrite', 'on', @iCatPermalinks, 'Enable friendly profile permalinks', 'checkbox', '', '', 10, ''),
('permalinks_browse', 'on', @iCatPermalinks, 'Enable friendly browse permalinks', 'checkbox', '', '', 20, '');
-- CAT: Categories
SET @iCatCategories = 27;
INSERT INTO `sys_options` VALUES
('categ_perpage_browse', '30', @iCatCategories, 'Number of categories to show on browse pages', 'digit', '', '', 10, ''),
('categ_show_limit', '50', @iCatCategories, 'Number of categories to show limit', 'digit', '', '', 20, ''),
('categ_show_columns', '3', @iCatCategories, 'Number of columns to show categories', 'digit', '', '', 30, '');
-- CAT: Hidden
SET @iCatHidden = 0;
INSERT INTO `sys_options` VALUES
('sys_tmp_version', '', @iCatHidden, 'Dolphin version ', 'digit', '', '', 10, ''),
('license_code', '', @iCatHidden, 'Dolphin License Code', 'digit', '', '', 11, ''),
('license_expiration', '', @iCatHidden, 'Dolphin License Expiration', 'digit', '', '', 12, ''),
('license_checksum', '', @iCatHidden, 'Dolphin License Checksum', 'digit', '', '', 13, ''),
('enable_dolphin_footer', 'on', @iCatHidden, 'Enable BoonEx Footers', 'checkbox', '', '', 14, ''),
('sys_ftp_login', '', @iCatHidden, 'FTP server login', 'digit', '', '', 20, ''),
('sys_ftp_password', '', @iCatHidden, 'FTP server password', 'digit', '', '', 21, ''),
('sys_ftp_dir', '', @iCatHidden, 'Path to Dolphin on FTP server', 'digit', '', '', 22, ''),
('splash_editor', 'on', @iCatHidden, '', 'checkbox', '', '', 30, ''),
('splash_code', '<div class="bx-splash" style="position:relative; height:237px; background-repeat:no-repeat; background-position:0px 0px; background-image:url(templates/base/images/bx_splash_image.jpg)">\r\n    <div class="bx-splash-txt-bg" style="position:absolute; z-index:1; bottom:0px; width:100%; height:96px; background-color:#ffffff; opacity:0.7;">\r\n        <img src="templates/base/images/spacer.gif" />\r\n    </div>\r\n    <div class="bx-splash-txt" style="position:absolute; z-index:2; bottom:0px; width:100%; height:96px;">\r\n        <div class="bx-splash-txt-cnt bx-def-margin" style="position:relative; opacity:1.0;">\r\n            <div class="bx-splash-txt-l1" style="font-size: 36px;">Welcome to the community!</div>\r\n            <div class="bx-splash-txt-l2 bx-def-font-grayed" style="font-size:14px;">This social networking site is powered by <a href="http://www.boonex.com/dolphin">Dolphin Community Software</a> from BoonEx.</div>\r\n            <div class="bx-splash-action bx-def-padding-sec-topbottom" style="position:absolute; top:0px; right:0px;">\r\n                <button class="bx-btn bx-btn-primary" onclick="window.open (''join.php'',''_self'');">Join</button>\r\n                <button class="bx-btn" style="margin-left:10px;" onclick="showPopupLoginForm(); return false;">Login</button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>', @iCatHidden, '', 'text', '', '', 31, ''),
('splash_visibility', 'index', @iCatHidden, '', 'text', '', '', 32, ''),
('splash_logged', 'on', @iCatHidden, '', 'checkbox', '', '', 33, ''),
('sys_html_fields', 'a:1:{s:6:"system";a:4:{i:0;s:12:"POST.message";i:1;s:15:"REQUEST.message";i:2;s:12:"POST.CmtText";i:3;s:15:"REQUEST.CmtText";}}', @iCatHidden, 'HTML fields', 'text', '', '', 40, ''),
('sys_json_fields', '', @iCatHidden, 'JSON fields', 'text', '', '', 41, ''),
('sys_exceptions_fields', '', @iCatHidden, 'Exceptions fields', 'text', '', '', 42, ''),
('cmdDay', '10', @iCatHidden, '', 'digit', '', '', 50, ''),
('tags_last_parse_time', '0', @iCatHidden, 'Temporary value when tags cron-job was runed last time', 'digit', '', '', 51, ''),
('cupid_last_cron', '0', @iCatHidden, 'Temporary value when cupid mails checked was runed last time', 'text', '', '', 52, ''),
('sys_show_admin_help', 'on', @iCatHidden, 'Show help in admin dashboard', 'checkbox', '', '', 53, ''),
('sys_main_logo', '', @iCatHidden, 'Main logo file name', 'text', '', '', 60, ''),
('main_div_width', '1140px', @iCatHidden, 'Width of the main container of the site', 'digit', '', '', 61, ''),
('sys_template_cache_image_enable', '', @iCatHidden, 'Enable cache for images (do not work for IE7)', 'checkbox', '', '', 70, ''),
('sys_template_cache_image_max_size', '5', @iCatHidden, 'Max image size to be cached(in kb)', 'digit', '', '', 71, ''),
('sys_sitemap_enable', '', @iCatHidden, 'Enable sitemap generation', 'checkbox', '', '', 80, ''),
('sys_captcha_default', 'sys_recaptcha', @iCatHidden, 'Default CAPTCHA', 'digit', '', '', 90, ''),
('sys_editor_default', 'sys_tinymce', @iCatHidden, 'Default HTML editor', 'digit', '', '', 91, '');
INSERT INTO `sys_options_cats` VALUES(1, 'Profiles', 1);
INSERT INTO `sys_options_cats` VALUES(3, 'General', 3);
INSERT INTO `sys_options_cats` VALUES(4, 'Massmailer', 4);
INSERT INTO `sys_options_cats` VALUES(5, 'Memberships', 5);
INSERT INTO `sys_options_cats` VALUES(6, 'Moderation', 6);
INSERT INTO `sys_options_cats` VALUES(7, 'Site', 7);
INSERT INTO `sys_options_cats` VALUES(9, 'Privacy Groups', 9);
INSERT INTO `sys_options_cats` VALUES(11, 'Pruning', 11);
INSERT INTO `sys_options_cats` VALUES(12, 'Matches', 12);
INSERT INTO `sys_options_cats` VALUES(13, 'Template', 13);
INSERT INTO `sys_options_cats` VALUES(14, 'Security', 14);
INSERT INTO `sys_options_cats` VALUES(16, 'Watermark', 16);
INSERT INTO `sys_options_cats` VALUES(21, 'Languages', 21);
INSERT INTO `sys_options_cats` VALUES(22, 'IP Block List', 22);
INSERT INTO `sys_options_cats` VALUES(23, 'Antispam', 23);
INSERT INTO `sys_options_cats` VALUES(24, 'Caching', 24);
INSERT INTO `sys_options_cats` VALUES(25, 'Tags Settings', 25);
INSERT INTO `sys_options_cats` VALUES(26, 'Permalinks', 26);
INSERT INTO `sys_options_cats` VALUES(27, 'Categories Settings', 27);
UPDATE `sys_options`, `sys_options_tmp`
SET `VALUE` = `v`
WHERE `Name` = `n` AND `VALUE` !=  `v` AND `n` NOT IN ('featured_num', 'top_members_max_num', 'sys_ps_enabled_group_1', 'categ_show_columns', 'sys_uridnsbl_enable', 'main_div_width');
DROP TABLE IF EXISTS `sys_options_tmp`;

-- page builder
UPDATE `sys_page_compose` SET `ColWidth` = '28.1' WHERE (`ColWidth` = '34' OR `ColWidth` = '30');
UPDATE `sys_page_compose` SET `ColWidth` = '71.9' WHERE (`ColWidth` = '66' OR `ColWidth` = '70');
UPDATE `sys_page_compose` SET `PageWidth` = '1140px';
DELETE FROM `sys_page_compose` WHERE `Page` = 'my_friends';
DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'my_friends';
-- index
DELETE FROM `sys_page_compose` WHERE `Func` IN ('SiteStats', 'Subscribe', 'QuickSearch', 'Leaders', 'Featured', 'Tags', 'Categories', 'Members', 'LoginSection', 'Download') AND `Page` = 'index';
DELETE FROM `sys_page_compose` WHERE `Func` = 'RSS' AND `Content` = 'http://www.boonex.com/unity/blog/featured_posts/?rss=1#4' AND `Page` = 'index';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'index';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('index', '1140px', 'Shows statistic information about your site content', '_Site Stats', 2, 2, 'SiteStats', '', 1, 28.1, 'non,memb', 0, 3600),
('index', '1140px', 'Display form to subscribe to newsletters', '_Subscribe_block_caption', 2, 1, 'Subscribe', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'Quick search form', '_Quick Search', 0, 0, 'QuickSearch', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'List of featured profiles', '_featured members', 0, 0, 'Featured', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'Site Tags', '_Tags', 0, 0, 'Tags', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'Site Categories', '_Categories', 0, 0, 'Categories', '', 1, 71.9, 'non,memb', 0, 0),
('index', '1140px', 'List of profiles', '_Members', 2, 0, 'Members', '', 1, 28.1, 'non,memb', 0, 0),
('index', '1140px', 'Shows Login Form', '_Member Login', 0, 0, 'LoginSection', '', 1, 28.1, 'non', 0, 86400),
('index', '1140px', '', '_BoonEx News', 1, 0, 'RSS', 'http://www.boonex.com/notes/featured_posts/?rss=1#4', 1, 71.9, 'non,memb', 0, 86400),
('index', '1140px', 'Download', '_sys_box_title_download', 0, 0, 'Download', '', 1, 28.1, 'non,memb', 0, 86400);
-- member
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Mailbox', 'Friends', 'AccountControl', 'QuickLinks', 'FriendRequests', 'NewMessages') AND `Page` = 'member';
DELETE FROM `sys_page_compose` WHERE `Func` = 'RSS' AND `Page` = 'member' AND `Content` = 'http://www.boonex.com/unity/blog/featured_posts/?rss=1#4';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'member';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('member', '1140px', 'Quick Links', '_Quick Links', 1, 0, 'QuickLinks', '', 1, 71.9, 'memb', 0, 0),
('member', '1140px', 'Friend Requests', '_sys_bcpt_member_friend_requests', 2, 1, 'FriendRequests', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'New Messages', '_sys_bcpt_member_new_messages', 2, 2, 'NewMessages', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'Account Control', '_sys_bcpt_member_account_control', 2, 3, 'AccountControl', '', 1, 28.1, 'memb', 0, 0),
('member', '1140px', 'Member Friends', '_My Friends', 0, 0, 'Friends', '', 1, 28.1, 'memb', 0, 0);
-- profile
DELETE FROM `sys_page_compose` WHERE `Func` IN ('ActionsMenu', 'FriendRequest', 'RateProfile', 'Friends', 'Cmts', 'MutualFriends', 'Description') AND `Page` = 'profile';
DELETE FROM `sys_page_compose` WHERE `Func` = 'PFBlock' AND `Page` = 'profile' AND `Content` IN ('17', '21', '25', '34', '20');
DELETE FROM `sys_page_compose` WHERE `Func` = 'RSS' AND `Page` = 'profile' AND `Content` = 'http://www.boonex.com/unity/blog/featured_posts/?rss=1#4';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'profile';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('profile', '1140px', 'Profile actions', '_Actions', 1, 2, 'ActionsMenu', '', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Friend request notification', '_FriendRequest', 1, 3, 'FriendRequest', '', 1, 28.1, 'memb', 0, 0),
('profile', '1140px', 'Profile description block', '_Description', 2, 2, 'Description', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_Admin Controls_View', 1, 4, 'PFBlock', '21', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_General Info_View', 1, 5, 'PFBlock', '17', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Profile rating form', '_rate profile', 1, 6, 'RateProfile', '', 1, 28.1, 'non,memb', 0, 0),
('profile', '1140px', 'Member friends list', '_Friends', 0, 0, 'Friends', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Mutual friends of viewing and viewed members', '_Mutual Friends', 0, 0, 'MutualFriends', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Comments on member profile', '_profile_comments', 0, 0, 'Cmts', '', 1, 71.9, 'non,memb', 0, 0),
('profile', '1140px', 'Profile Fields Block', '_FieldCaption_Misc_View', 0, 0, 'PFBlock', '20', 1, 71.9, 'non,memb', 0, 0);
-- profile info
DELETE FROM `sys_page_compose` WHERE `Func` IN ('GeneralInfo', 'AdditionalInfo', 'Description') AND `Page` = 'profile_info';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'profile_info';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('profile_info', '1140px', '', '_FieldCaption_General Info_View', 1, 0, 'GeneralInfo', '', 1, 100, 'non,memb', 0, 0),
('profile_info', '1140px', '', '_Additional information', 1, 2, 'AdditionalInfo', '', 1, 100, 'non,memb', 0, 0),
('profile_info', '1140px', 'Profile''s description', '_Description', 1, 1, 'Description', '', 1, 100, 'non,memb', 0, 0);
-- friends
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Friends', 'FriendsRequests', 'FriendsMutual') AND `Page` = 'friends';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'friends';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('friends', '1140px', '', '_Member Friends', 1, 1, 'Friends', '', 1, 71.9, 'non,memb', 0, 0),
('friends', '1140px', '', '_Member Friends Requests', 2, 1, 'FriendsRequests', '', 1, 28.1, 'memb', 0, 0),
('friends', '1140px', '', '_Member Friends Mutual', 2, 2, 'FriendsMutual', '', 1, 28.1, 'memb', 0, 0);
-- browse page
DELETE FROM `sys_page_compose` WHERE `Func` IN ('SettingsBlock', 'SearchedMembersBlock') AND `Page` = 'browse_page';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'browse_page';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('browse_page', '1140px', '', '_Browse', 2, 0, 'SettingsBlock', '', 0, 28.1, 'non,memb', 0, 0),
('browse_page', '1140px', '', '_People', 1, 0, 'SearchedMembersBlock', '', 1, 71.9, 'non,memb', 0, 0);
-- mail pages
DELETE FROM `sys_page_compose` WHERE `Func` IN ('MailBox', 'Contacts', 'ViewMessage', 'Archives', 'ComposeMessage') AND `Page` IN ('mail_page', 'mail_page_view', 'mail_page_compose');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('mail_page', 'mail_page_view', 'mail_page_compose');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('mail_page', '1140px', '', '_Mail box', 1, 0, 'MailBox', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page', '1140px', '', '_My contacts', 2, 0, 'Contacts', '', 1, 28.1, 'non,memb', 0, 0),
('mail_page_view', '1140px', '', '_Mail box', 1, 0, 'ViewMessage', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page_view', '1140px', '', '_Archive', 2, 0, 'Archives', '', 1, 28.1, 'non,memb', 0, 0),
('mail_page_compose', '1140px', '', '_COMPOSE_H', 1, 0, 'ComposeMessage', '', 1, 71.9, 'non,memb', 0, 0),
('mail_page_compose', '1140px', '', '_My contacts', 2, 0, 'Contacts', '', 1, 28.1, 'non,memb', 0, 0);
-- search pages
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Results', 'SearchForm', 'Keyword', 'People') AND `Page` IN ('search', 'search_home');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('search', 'search_home');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('search', '1140px', 'Search Results', '_Search result', 1, 0, 'Results', '', 1, 71.9, 'non,memb', 0, 0),
('search', '1140px', 'Search Form', '_Search profiles', 2, 0, 'SearchForm', '', 1, 28.1, 'non,memb', 0, 0),
('search_home', '1140px', 'Keyword Search', '_sys_box_title_search_keyword', 1, 0, 'Keyword', '', 1, 71.9, 'non,memb', 0, 86400),
('search_home', '1140px', 'People Search', '_sys_box_title_search_people', 2, 0, 'People', '', 1, 28.1, 'non,memb', 0, 0);
-- join
DELETE FROM `sys_page_compose` WHERE `Func` IN ('JoinForm', 'LoginSection') AND `Page` = 'join';
DELETE FROM `sys_page_compose` WHERE `Func` = 'PHP' AND `Content` = 'return _t(''_why_join_desc'');' AND `Page` = 'join';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'join';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('join', '1140px', 'Join Form Block', '_Join now', 1, 0, 'JoinForm', '', 1, 71.9, 'non', 413, 0),
('join', '1140px', 'Login Form Block', '_Login', 2, 0, 'LoginSection', 'no_join_text', 1, 28.1, 'non', 250, 86400);
-- communicator
DELETE FROM `sys_page_compose` WHERE `Func` IN ('CommunicatorPage', 'Connections', 'FriendRequests') AND `Page` = 'communicator_page';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'communicator_page';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('communicator_page', '1140px', '', '_sys_cnts_bcpt_connections', 1, 1, 'Connections', '', 1, 71.9, 'memb', 0, 0),
('communicator_page', '1140px', '', '_sys_cnts_bcpt_friend_requests', 2, 1, 'FriendRequests', '', 1, 28.1, 'memb', 0, 0);
-- tags pages
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Recent', 'Popular', 'Calendar', 'TagsDate', 'Form', 'Founded', 'All') AND `Page` IN ('tags_home', 'tags_calendar', 'tags_search', 'tags_module');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('tags_home', 'tags_calendar', 'tags_search', 'tags_module');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('tags_home', '1140px', 'Recent Tags', '_tags_recent', 1, 0, 'Recent', '', 1, 28.1, 'non,memb', 0, 0),
('tags_home', '1140px', 'Popular Tags', '_popular_tags', 2, 0, 'Popular', '', 1, 71.9, 'non,memb', 0, 0),
('tags_calendar', '1140px', 'Calendar', '_tags_calendar', 1, 0, 'Calendar', '', 1, 100, 'non,memb', 0, 0),
('tags_calendar', '1140px', 'Date Tags', '_Tags', 1, 1, 'TagsDate', '', 1, 100, 'non,memb', 0, 0),
('tags_search', '1140px', 'Search Form', '_tags_search_form', 1, 0, 'Form', '', 1, 100, 'non,memb', 0, 86400),
('tags_search', '1140px', 'Founded Tags', '_tags_founded_tags', 1, 1, 'Founded', '', 1, 100, 'non,memb', 0, 0),
('tags_module', '1140px', 'Recent Tags', '_tags_recent', 1, 0, 'Recent', '', 1, 28.1, 'non,memb', 0, 0),
('tags_module', '1140px', 'All Tags', '_all_tags', 2, 0, 'All', '', 1, 71.9, 'non,memb', 0, 0);
-- categories pages
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Calendar', 'CategoriesDate', 'Form', 'Founded', 'Common', 'All') AND `Page` IN ('categ_calendar', 'categ_search', 'categ_module');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('categ_calendar', 'categ_search', 'categ_module');
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('categ_calendar', '1140px', 'Calendar', '_categ_caption_calendar', 1, 0, 'Calendar', '', 1, 100, 'non,memb', 0, 0),
('categ_calendar', '1140px', 'Categories By Day', '_categ_caption_day', 1, 1, 'CategoriesDate', '', 1, 100, 'non,memb', 0, 0),
('categ_search', '1140px', 'Search Form', '_categ_caption_search_form', 1, 0, 'Form', '', 1, 100, 'non,memb', 0, 86400),
('categ_search', '1140px', 'Founded Categories', '_categ_caption_founded', 1, 1, 'Founded', '', 1, 100, 'non,memb', 0, 0),
('categ_module', '1140px', 'Common Categories', '_categ_caption_common', 1, 0, 'Common', '', 1, 28.1, 'non,memb', 0, 0),
('categ_module', '1140px', 'All Categories', '_categ_caption_all', 2, 0, 'All', '', 1, 71.9, 'non,memb', 0, 0);
-- profile edit
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Info', 'Privacy', 'Membership') AND `Page` = 'pedit';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'pedit';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('pedit', '1140px', 'Profile fields', '_edit_profile_info', 1, 1, 'Info', '', 1, 71.9, 'memb', 0, 0),
('pedit', '1140px', 'Profile privacy', '_edit_profile_privacy', 2, 1, 'Privacy', '', 1, 28.1, 'memb', 0, 0),
('pedit', '1140px', 'Profile membership', '_edit_profile_membership', 2, 2, 'Membership', '', 1, 28.1, 'memb', 0, 0);
-- private profile 
DELETE FROM `sys_page_compose` WHERE `Func` IN ('ActionsMenu', 'PrivacyExplain') AND `Page` = 'profile_private';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'profile_private';
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('profile_private', '1140px', 'Actions that other members can do', '_Actions', 1, 0, 'ActionsMenu', '', 1, 28.1, 'non,memb', 0, 0),
('profile_private', '1140px', 'Some text to explain why this profile can not be viewed. Translation for this block is stored in ''_sys_profile_private_text'' language key.', '_sys_profile_private_text_title', 2, 0, 'PrivacyExplain', '', 1, 71.9, 'non,memb', 0, 0);


-- stats member
DELETE FROM `sys_stat_member` WHERE `Type` = 'mfr';
INSERT INTO `sys_stat_member` VALUES('mfr', 'SELECT COUNT(*) FROM `sys_friend_list` as f LEFT JOIN `Profiles` as p ON p.`ID` = f.`ID` WHERE f.`Profile` = __member_id__ AND f.`Check` = ''0'' AND p.`Status`=''Active''');

-- stat site
DELETE FROM `sys_stat_site` WHERE `Name` IN ('all', 'onl', 'ntd', 'nmh', 'tgs', 'nyr');
INSERT INTO `sys_stat_site`(`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('all', 'Members', 'browse.php', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Active'' AND (`Couple`=''0'' OR `Couple`>`ID`)', 'profiles.php?profiles=Approval', 'SELECT COUNT(`ID`) FROM `Profiles` WHERE `Status`=''Approval'' AND (`Couple`=''0'' OR `Couple`>`ID`)', 'user', 1);


-- menu top
DELETE FROM `sys_menu_top` WHERE `ID` = 90 AND `Parent` = 0 AND `Name` = 'Tags';
DELETE FROM `sys_menu_top` WHERE `Parent` = 90;
DELETE FROM `sys_menu_top` WHERE `ID` = 91 AND `Parent` = 0 AND `Name` = 'Categories';
DELETE FROM `sys_menu_top` WHERE `Parent` = 91;
OPTIMIZE TABLE  `sys_menu_top`;
DELETE FROM `sys_menu_top` WHERE `ID` = 110 AND `Parent` = 118 AND `Name` = 'Log out';
UPDATE `sys_menu_top` SET `Link` = '{memberLink}|{memberUsername}|change_status.php', `Type` = 'system', `Picture` = 'user', `Icon` = '' WHERE `ID` = 4 AND `Parent` = 0 AND `Name` = 'My Profile';
UPDATE `sys_menu_top` SET `Picture` = 'home', `Icon` = '' WHERE `ID` = 5 AND `Name` = 'Home';
UPDATE `sys_menu_top` SET `Picture` = 'user', `Icon` = '' WHERE `ID` = 6 AND `Name` = 'People';
UPDATE `sys_menu_top` SET `Picture` = '', `Icon` = '' WHERE `ID` = 7 AND `Name` = 'All members';
UPDATE `sys_menu_top` SET `Link` = '{profileUsername}|pedit.php?ID={profileID}' WHERE `ID` = 9 AND `Name` = 'Profile View';
UPDATE `sys_menu_top` SET `Link` = '{memberLink}|{memberUsername}|profile.php?ID={memberID}' WHERE `ID` = 11 AND `Name` = 'View My Profile';
UPDATE `sys_menu_top` SET `Name` = 'Mail Compose', `BQuickLink` = 0 WHERE `ID` = 12 AND `Name` = 'Compose';
UPDATE `sys_menu_top` SET `Picture` = '', `BQuickLink` = 0 WHERE `ID` = 14 AND `Name` = 'Mail Outbox';
UPDATE `sys_menu_top` SET `Order` = '2' WHERE `ID` = 14 AND `Name` = 'Mail Outbox' AND `Order` = '1';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 17 AND `Name` = 'Mail Inbox';
UPDATE `sys_menu_top` SET `Order` = '1' WHERE `ID` = 17 AND `Name` = 'Mail Inbox' AND `Order` = '2';
UPDATE `sys_menu_top` SET `Picture` = '', `BQuickLink` = 0 WHERE `ID` = 18 AND `Name` = 'Mail Trash';
UPDATE `sys_menu_top` SET `Picture` = 'user' WHERE `ID` = 20 AND `Name` = 'Edit My Profile';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 25 AND `Name` = 'Online Members';
UPDATE `sys_menu_top` SET `Link` = '{profileLink}|{profileUsername}|profile.php?ID={profileID}' WHERE `ID` = 60 AND `Name` = 'View Profile';
UPDATE `sys_menu_top` SET `Type` = 'system', `Picture` = 'user' WHERE `ID` = 98 AND `Parent` = 0 AND `Name` = 'Join';
UPDATE `sys_menu_top` SET `Picture` = 'user' WHERE `ID` = 99 AND `Name` = 'Login';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 127 AND `Name` = 'Match';
UPDATE `sys_menu_top` SET `Name` = 'Privacy Groups', `Check` = 'bx_import(\'BxDolPrivacy\'); return BxDolPrivacy::isPrivacyPage();' WHERE `ID` = 107 AND `Name` = 'Privacy Settings';
UPDATE `sys_menu_top` SET `Type` = 'system', `Picture` = 'dashboard', `Icon` = '' WHERE `ID` = 118 AND `Parent` = 0 AND `Name` = 'Dashboard';
UPDATE `sys_menu_top` SET `Picture` = 'info-sign' WHERE `ID` = 120 AND `Name` = 'About';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 131 AND `Name` = 'Birthdays';
UPDATE `sys_menu_top` SET `Picture` = 'search' WHERE `ID` = 138 AND `Name` = 'Search';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 139 AND `Name` = 'Keyword Search';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 141 AND `Name` = 'People Search';
UPDATE `sys_menu_top` SET `Picture` = 'question-sign' WHERE `ID` = 159 AND `Name` = 'Help';
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `ID` = 176 AND `Name` = 'Search Home';
UPDATE `sys_menu_top` SET `Type` = 'system', `Picture` = 'envelope', `Icon` = '' WHERE `ID` = 179 AND `Parent` = 0 AND `Name` = 'Mail';


-- objects: actions
DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND (`Caption` IN ('{cpt_fave}', '{cpt_befriend}', '{cpt_greet}', '{cpt_get_mail}', '{cpt_share}', '{cpt_report}', '{cpt_block}', '{sbs_profile_title}', '{cpt_remove_friend}', '{cpt_unblock}', '{cpt_remove_fave}') OR `Eval` LIKE '%{cpt_edit}%' OR `Eval` LIKE '%{cpt_send_letter}%');
DELETE FROM `sys_objects_actions` WHERE `Type` IN ('Mailbox', 'ProfileTitle', 'AccountTitle');
OPTIMIZE TABLE  `sys_objects_actions`;
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{evalResult}', 'edit', 'pedit.php?ID={ID}', '', 'if ({ID} != {member_id}) return;\r\nreturn _t(''{cpt_edit}'');', 1, 'Profile', 0),
('{evalResult}', 'envelope', 'mail.php?mode=compose&recipient_id={ID}', '', 'if ({ID} == {member_id}) return;\r\nreturn _t(''{cpt_send_letter}'');', 2, 'Profile', 0),
('{cpt_fave}', 'asterisk', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFaveAdd({ID}, {member_id});', 3, 'Profile', 0),
('{cpt_remove_fave}', 'asterisk', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFaveCancel({ID}, {member_id});', 3, 'Profile', 0),
('{cpt_befriend}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAdd({ID}, {member_id});', 4, 'Profile', 0),
('{cpt_remove_friend}', 'minus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendCancel({ID}, {member_id}, false);', 4, 'Profile', 0),
('{cpt_greet}', 'hand-right', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn "$.post(''greet.php'', { sendto: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 5, 'Profile', 0),
('{cpt_get_mail}', 'envelope-alt', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\n$bAnonymousMode  = ''{anonym_mode}'';\r\n\r\nif ( !$bAnonymousMode ) {\r\n    return "$.post(''freemail.php'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n}\r\n', 6, 'Profile', 0),
('{cpt_share}', 'share', '', 'return launchTellFriendProfile({ID});', '', 7, 'Profile', 0),
('{cpt_report}', 'exclamation-sign', '', '{evalResult}', 'if ({ID} == {member_id}) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=spam'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 8, 'Profile', 0),
('{cpt_block}', 'ban-circle', '', '{evalResult}', 'if ( {ID} == {member_id} || isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn  "$.post(''list_pop.php?action=block'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0),
('{sbs_profile_title}', 'paper-clip', '', '{sbs_profile_script}', '', 10, 'Profile', 0),
('{cpt_unblock}', 'ban-circle', '', '{evalResult}', 'if ({ID} == {member_id} || !isBlocked({member_id}, {ID}) ) return;\r\n\r\nreturn "$.post(''list_pop.php?action=unblock'', { ID: ''{ID}'' }, function(sData){ $(''#ajaxy_popup_result_div_{ID}'').html(sData) } );return false;";\r\n', 9, 'Profile', 0),
('{evalResult}', 'plus', '{BaseUri}mail.php?mode=compose', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_sys_am_mailbox_compose'') : '''';', 1, 'Mailbox', 1),
('{cpt_am_friend_add}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAdd({ID}, {member_id}, false);', 1, 'ProfileTitle', 1),
('{cpt_am_friend_accept}', 'plus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendAccept({ID}, {member_id}, false);', 2, 'ProfileTitle', 1),
('{cpt_am_friend_cancel}', 'minus', '', '{evalResult}', 'return $GLOBALS[''oTopMenu'']->getScriptFriendCancel({ID}, {member_id}, false);', 3, 'ProfileTitle', 1),
('{cpt_am_profile_message}', 'envelope', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlProfileMessage({ID});', 4, 'ProfileTitle', 1),
('{cpt_am_profile_account_page}', 'dashboard', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlAccountPage({ID});', 5, 'ProfileTitle', 1),
('{cpt_am_account_profile_page}', 'user', '{evalResult}', '', 'return $GLOBALS[''oTopMenu'']->getUrlProfilePage({ID});', 1, 'AccountTitle', 1);


-- injections
DELETE FROM `sys_injections` WHERE `name` = 'site_search' OR `name` = 'site_service_menu';
INSERT INTO `sys_injections` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('site_search', '0', 'injection_logo_before', 'php', 'return $GLOBALS[''oFunctions'']->genSiteSearch();', '0', '1'),
('site_service_menu', '0', 'injection_logo_after', 'php', 'return $GLOBALS[''oFunctions'']->genSiteServiceMenu();', '0', '1');


-- injections admin
DELETE FROM `sys_injections_admin` WHERE `name` = 'lfa';
INSERT INTO `sys_injections_admin` (`name`, `page_index`, `key`, `type`, `data`, `replace`, `active`) VALUES 
('lfa', '0', 'injection_header', 'php', 'return lfa();', '0', '1');


-- privacy groups
UPDATE `sys_privacy_groups` SET `get_content` = 'return isMember() && isProfileActive($arg2);' WHERE `id` = '4';
SET @iNextPrivacyGroupId = (SELECT MAX(`id`) + 1 FROM `sys_privacy_groups`);
UPDATE `sys_privacy_groups` SET `id` = @iNextPrivacyGroupId WHERE `id` = '8' AND `title` != '' AND `owner_id` != 0;
INSERT IGNORE INTO `sys_privacy_groups` VALUES
('8', '0', '0', '', '', '', '', 0);


-- subscriptions
SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'profile' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'profile' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `unit` = 'system' AND `action` = '' AND `template` = '';
OPTIMIZE TABLE `sys_sbs_types`;
INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('system', '', '', 'return array(''template'' => array(''Subscription'' => _t(''_sbs_txt_sbs_mass_mailer''), ''ViewLink'' => BX_DOL_URL_ROOT));');


-- downloads box
UPDATE `sys_box_download` SET `url` = 'http://itunes.apple.com/us/app/oo/id345450186' WHERE `url` = 'http://www.boonex.com/products/mobile/iphone/';
UPDATE `sys_box_download` SET `url` = 'https://play.google.com/store/apps/details?id=com.boonex.oo' WHERE `url` = 'https://market.android.com/details?id=com.boonex.oo';


-- cronjobs
DELETE FROM `sys_cron_jobs` WHERE `name` = 'sitemap';
INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
('sitemap', '0 2 * * *', '', '', 'bx_import(''BxDolSiteMaps'');\r\nBxDolSiteMaps::generateAllSiteMaps();');


-- mobile pages
DELETE FROM `sys_menu_mobile_pages` WHERE `page` = 'search';
DELETE FROM `sys_menu_mobile` WHERE `page` = 'search';
INSERT INTO `sys_menu_mobile_pages` (`page`, `title`, `order`) VALUES
('search', '_adm_mobile_page_search', 3);
INSERT INTO `sys_menu_mobile` (`type`, `page`, `title`, `icon`, `action`, `action_data`, `eval_bubble`, `eval_hidden`, `order`, `active`) VALUES
('system', 'search', '_sys_mobile_search_by_keyword', '', 30, '', '', '', 1, 1),
('system', 'search', '_sys_mobile_search_by_location', '', 31, '', '', '', 2, 1);


-- objects: social sharing
CREATE TABLE IF NOT EXISTS `sys_objects_social_sharing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `order` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_social_sharing`;
INSERT INTO `sys_objects_social_sharing` (`object`, `content`, `order`, `active`) VALUES
('facebook', '<iframe src="//www.facebook.com/plugins/like.php?href={url_encoded}&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;locale={locale}" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100%; height:21px;" allowTransparency="true"></iframe>', 1, 1),
('googleplus', '<div style="height:21px;">\r\n<div class="g-plusone" data-size="medium" data-href="{url}"></div>\r\n<script type="text/javascript">\r\n  window.___gcfg = {lang: ''{lang}''};\r\n  (function() {\r\n    var po = document.createElement(''script''); po.type = ''text/javascript''; po.async = true;\r\n    po.src = ''https://apis.google.com/js/plusone.js'';\r\n    var s = document.getElementsByTagName(''script'')[0]; s.parentNode.insertBefore(po, s);\r\n  })();\r\n</script>\r\n</div>', 2, 1),
('twitter', '<iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/tweet_button.html?url={url_encoded}&amp;text={title_encoded}&amp;size=medium&amp;count=horizontal&amp;lang={lang}" style="width:100%;height:21px;"></iframe>', 3, 1),
('pinterest', '<a href="http://pinterest.com/pin/create/button/?url={url_encoded}&media={img_url_encoded}&description={title_encoded}" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>\r\n\r\n<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>', 4, 1);


-- objects: xml site maps
CREATE TABLE IF NOT EXISTS `sys_objects_site_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `priority` varchar(5) NOT NULL DEFAULT '0.6',
  `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never','auto') NOT NULL DEFAULT 'auto',
  `class_name` varchar(255) NOT NULL,
  `class_file` varchar(255) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_site_maps`;
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('system', '_sys_sitemap_system', '0.6', 'weekly', 'BxDolSiteMapsSystem', '', 1, 1),
('profiles', '_sys_sitemap_profiles', '0.8', 'daily', 'BxDolSiteMapsProfiles', '', 2, 1),
('profiles_info', '_sys_sitemap_profiles_info', '0.8', 'daily', 'BxDolSiteMapsProfilesInfo', '', 3, 1);


-- objects: charts
CREATE TABLE IF NOT EXISTS `sys_objects_charts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `field_date_ts` varchar(255) NOT NULL,
  `field_date_dt` varchar(255) NOT NULL,
  `column_date` int(11) NOT NULL DEFAULT '0',
  `column_count` int(11) NOT NULL DEFAULT '1',
  `type` varchar(255) NOT NULL,
  `options` text NOT NULL,
  `query` text NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_charts`;
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('sys_profiles', '_Members', 'Profiles', '', 'DateReg', '', 1, 1),
('sys_subscribers', '_Subscribers', 'sys_sbs_users', 'date', '', '', 1, 2),
('sys_messages', '_Messages', 'sys_messages', '', 'Date', '', 1, 3),
('sys_greetings', '_Greetings', 'sys_greetings', '', 'When', '', 1, 4),
('sys_tags', '_Tags', 'sys_tags', '', 'Date', '', 1, 5),
('sys_categories', '_Categories', 'sys_categories', '', 'Date', '', 1, 6),
('sys_banners', '_adm_bann_clicks_chart', 'sys_banners_clicks', 'Date', '', '', 1, 7);


-- objects: captchas
CREATE TABLE IF NOT EXISTS `sys_objects_captcha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_captcha`;
INSERT INTO `sys_objects_captcha` (`object`, `title`, `override_class_name`, `override_class_file`) VALUES
('sys_recaptcha', 'reCAPTCHA', 'BxTemplCaptchaReCAPTCHA', '');


-- objects: HTML editors
CREATE TABLE IF NOT EXISTS `sys_objects_editor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `skin` varchar(255) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_editor`;
INSERT INTO `sys_objects_editor` (`object`, `title`, `skin`, `override_class_name`, `override_class_file`) VALUES
('sys_tinymce', 'TinyMCE', 'default', 'BxTemplEditorTinyMCE', '');


-- objects: member info
CREATE TABLE IF NOT EXISTS `sys_objects_member_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL,
  `override_class_name` varchar(255) NOT NULL,
  `override_class_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
TRUNCATE TABLE `sys_objects_member_info`;
INSERT INTO `sys_objects_member_info` (`object`, `title`, `type`, `override_class_name`, `override_class_file`) VALUES
('sys_username', '_sys_member_info_username', 'name', '', ''),
('sys_first_name', '_sys_member_info_first_name', 'name', '', ''),
('sys_first_name_last_name', '_sys_member_info_first_name_last_name', 'name', '', ''),
('sys_last_name_firs_name', '_sys_member_info_last_name_firs_name', 'name', '', ''),
('sys_headline', '_sys_member_info_headline', 'info', '', ''),
('sys_status_message', '_sys_member_info_status_message', 'info', '', ''),
('sys_age_sex', '_sys_member_info_age_sex', 'info', '', ''),
('sys_location', '_sys_member_info_location', 'info', '', ''),
('sys_avatar', '_sys_member_thumb_avatar', 'thumb', '', ''),
('sys_avatar_icon', '_sys_member_thumb_icon_avatar', 'thumb_icon', '', '');



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_(available for <span>N</span> seconds)','_<- Remove','_AFFILIATES','_AFFILIATES_H','_AFFILIATES_H1','_ATT_FRIEND','_ATT_FRIEND_NONE','_ATT_MESSAGE','_ATT_MESSAGE_NONE','_ATT_VKISS','_ATT_VKISS_NONE','_About is required','_Accept','_Account Control','_Account settings','_Ad of the Day','_Add ->','_Add comment','_Add member','_Add record','_Add story','_Add to Friend List','_Add to Hot List','_Admin_Links','_Advanced Search','_Advanced search','_Affiliate','_Affiliate Program','_Affiliates','_Aged from','_Alerts','_Alerts settings','_All Videos','_All friends','_All members','_All_Cards','_All_Sites','_All_Users','_AllowAlbumView','_Approval','_Are you sure want to delete this image?','_Background color','_Background picture','_Blocked Me','_Blocked, Viewed, etc','_BoonEx Community Software Experts','_By Author','_CHECKOUT_H','_COMPOSE_REJECT_MEMBER_NOT_FOUND','_COMPOSE_STORY_H','_COMPOSE_STORY_H1','_COMPOSE_STORY_VIEW_H','_COMPOSE_STORY_VIEW_H1','_Cards','_Cards_Calendar','_Cards_Categories','_Cards_Home','_Cast my vote','_Category Caption','_Category Deleted Successfully','_Category are not deleted','_Category is required','_Change Password','_Charges number','_ChatNow','_Check Out','_Check all','_Choose forum','_Choose module type','_Choose_membership','_City is required','_Clear','_Click to Play/Pause','_Click_here_to_update_your_status','_Communicator');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_(available for <span>N</span> seconds)','_<- Remove','_AFFILIATES','_AFFILIATES_H','_AFFILIATES_H1','_ATT_FRIEND','_ATT_FRIEND_NONE','_ATT_MESSAGE','_ATT_MESSAGE_NONE','_ATT_VKISS','_ATT_VKISS_NONE','_About is required','_Accept','_Account Control','_Account settings','_Ad of the Day','_Add ->','_Add comment','_Add member','_Add record','_Add story','_Add to Friend List','_Add to Hot List','_Admin_Links','_Advanced Search','_Advanced search','_Affiliate','_Affiliate Program','_Affiliates','_Aged from','_Alerts','_Alerts settings','_All Videos','_All friends','_All members','_All_Cards','_All_Sites','_All_Users','_AllowAlbumView','_Approval','_Are you sure want to delete this image?','_Background color','_Background picture','_Blocked Me','_Blocked, Viewed, etc','_BoonEx Community Software Experts','_By Author','_CHECKOUT_H','_COMPOSE_REJECT_MEMBER_NOT_FOUND','_COMPOSE_STORY_H','_COMPOSE_STORY_H1','_COMPOSE_STORY_VIEW_H','_COMPOSE_STORY_VIEW_H1','_Cards','_Cards_Calendar','_Cards_Categories','_Cards_Home','_Cast my vote','_Category Caption','_Category Deleted Successfully','_Category are not deleted','_Category is required','_Change Password','_Charges number','_ChatNow','_Check Out','_Check all','_Choose forum','_Choose module type','_Choose_membership','_City is required','_Clear','_Click to Play/Pause','_Click_here_to_update_your_status','_Communicator');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Compose new message','_Confirm E-mail','_Confirm your e-mail','_Confirm your password','_Congratulation','_Contacts','_Country is required','_Created','_Credit card number','_Custom','_Customize Profile','_DateOfBirth','_DateOfBirth_err_msg','_DateOfBirth_lbl','_Day','_Delete from Friend List','_DescriptionMedia','_Disabled','_Dolphin Smart Community Builder','_EMAIL_INVALID_AFF','_Edit members','_Edit profile info','_Enter member NickName or ID','_Enter profile ID','_Enter search parameters','_Enter what you see:','_Existed_RSS','_Explanation','_FAILED_TO_DELETE_PIC','_FAILED_TO_UPLOAD_PIC','_Fail to sent subscription cancellation request','_Favorited','_Featured Posts','_Feedback','_Fetch','_File already is favorite','_File upload error','_File was added to favorite','_File was deleted','_File was uploaded','_Find','_Find more...','_Flag','_Font color','_Font family','_Font size','_Friends tracker','_GETMEM_H','_GETMEM_H1','_Gallery upload_desc','_General self-description','_Get BoonEx ID','_Go to Mailbox','_Google_Site_Search','_Got_members_part_1','_Got_members_part_2','_Got_new_membership_part_1','_Got_new_membership_part_2','_Got_new_membership_part_3','_Greeted me','_Hacker String','_Hot list','_Hot lists','_I Blocked','_I agree','_I am a','_ICQ','_IM now','_INBOX_H','_INBOX_H1','_INCORRECT_EMAIL','_Import','_Import BoonEx ID','_Invalid module type selected.','_Invite list');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Compose new message','_Confirm E-mail','_Confirm your e-mail','_Confirm your password','_Congratulation','_Contacts','_Country is required','_Created','_Credit card number','_Custom','_Customize Profile','_DateOfBirth','_DateOfBirth_err_msg','_DateOfBirth_lbl','_Day','_Delete from Friend List','_DescriptionMedia','_Disabled','_Dolphin Smart Community Builder','_EMAIL_INVALID_AFF','_Edit members','_Edit profile info','_Enter member NickName or ID','_Enter profile ID','_Enter search parameters','_Enter what you see:','_Existed_RSS','_Explanation','_FAILED_TO_DELETE_PIC','_FAILED_TO_UPLOAD_PIC','_Fail to sent subscription cancellation request','_Favorited','_Featured Posts','_Feedback','_Fetch','_File already is favorite','_File upload error','_File was added to favorite','_File was deleted','_File was uploaded','_Find','_Find more...','_Flag','_Font color','_Font family','_Font size','_Friends tracker','_GETMEM_H','_GETMEM_H1','_Gallery upload_desc','_General self-description','_Get BoonEx ID','_Go to Mailbox','_Google_Site_Search','_Got_members_part_1','_Got_members_part_2','_Got_new_membership_part_1','_Got_new_membership_part_2','_Got_new_membership_part_3','_Greeted me','_Hacker String','_Hot list','_Hot lists','_I Blocked','_I agree','_I am a','_ICQ','_IM now','_INBOX_H','_INBOX_H1','_INCORRECT_EMAIL','_Import','_Import BoonEx ID','_Invalid module type selected.','_Invite list');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Invite others','_Invites succesfully sent','_JOIN1_AFF','_JOIN_AFF2','_JOIN_AFF_H','_JOIN_AFF_ID','_Join Now Top','_Kisses','_LINKS_H','_LINKS_H1','_LOGIN_ERROR','_Latest files from this user','_Leaders','_License Agreement','_Lifetime','_Links','_Log Out2','_MEDIA_GALLERY_H','_MEMBERSHIP_BUY_MORE_DAYS','_MEMBERSHIP_H','_MEMBERSHIP_H1','_MEMBERSHIP_STANDARD','_MEMBERS_YOU_VIEWED','_MEMBERS_YOU_VIEWED_BY','_Member menu settings','_Member succesfully approved','_Member succesfully rejected','_Members count','_Membership NEW','_Membership Status','_Membership purchase','_Message successfully deleted','_Moderator','_Module directory was not set. Module must be re-configurated','_Module selection','_Module type selection','_Module_access_error','_Month','_Must be valid','_My Contacts','_My Mail','_My Membership','_My Profile','_My Settings','_My Videos','_My account','_My info','_NEWS_H','_NICK_LEAST2','_NOT_RECOGNIZED','_NO_LINKS','_NO_RESULTS','_NO_STORIES','_Need_more_members','_New Message','_New Today','_Nickname','_No actions allowed for this membership','_No file','_No members found here','_No messages in Inbox','_No messages in Outbox','_No modules found','_No modules of this type installed','_No news available','_No success story available.','_No tags found here','_Not Recognized','_Notification','_Notification send failed','_Notifications','_Notifications settings','_OUTBOX_H','_OUTBOX_H1','_Open join');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Invite others','_Invites succesfully sent','_JOIN1_AFF','_JOIN_AFF2','_JOIN_AFF_H','_JOIN_AFF_ID','_Join Now Top','_Kisses','_LINKS_H','_LINKS_H1','_LOGIN_ERROR','_Latest files from this user','_Leaders','_License Agreement','_Lifetime','_Links','_Log Out2','_MEDIA_GALLERY_H','_MEMBERSHIP_BUY_MORE_DAYS','_MEMBERSHIP_H','_MEMBERSHIP_H1','_MEMBERSHIP_STANDARD','_MEMBERS_YOU_VIEWED','_MEMBERS_YOU_VIEWED_BY','_Member menu settings','_Member succesfully approved','_Member succesfully rejected','_Members count','_Membership NEW','_Membership Status','_Membership purchase','_Message successfully deleted','_Moderator','_Module directory was not set. Module must be re-configurated','_Module selection','_Module type selection','_Module_access_error','_Month','_Must be valid','_My Contacts','_My Mail','_My Membership','_My Profile','_My Settings','_My Videos','_My account','_My info','_NEWS_H','_NICK_LEAST2','_NOT_RECOGNIZED','_NO_LINKS','_NO_RESULTS','_NO_STORIES','_Need_more_members','_New Message','_New Today','_Nickname','_No actions allowed for this membership','_No file','_No members found here','_No messages in Inbox','_No messages in Outbox','_No modules found','_No modules of this type installed','_No news available','_No success story available.','_No tags found here','_Not Recognized','_Notification','_Notification send failed','_Notifications','_Notifications settings','_OUTBOX_H','_OUTBOX_H1','_Open join');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_PWD_INVALID2','_PWD_INVALID3','_Pages','_Payment amount','_Payment description','_Payment info','_Payment methods','_Period','_Period (hours)','_Personal information','_Playbacks','_Please fill up all fields','_Please login before using Ray chat','_Please select at least one search parameter','_Possible subscription period','_Post a new topic','_Post topic','_Prev','_Previous rated','_Print As','_Privacy settings','_Profile Videos','_Profile tags','_Profile_Sites','_Profiles purchase','_Profiles tags','_Quick Search Members','_Quick search results','_RESULT-1','_Ray is not enabled. Select <link> another module','_Read news in archive','_Readed','_Reject','_Reject Invite','_Related Files','_Reload Security Image','_Reply to Someone comment','_Reported','_SIMG_ERR','_STORY_ADDED','_STORY_ADDED_FAILED','_STORY_EMPTY_HEADER','_STORY_UPDATED','_STORY_UPDATED_FAILED','_STORY_VIEW_H','_STORY_VIEW_H1','_Search by','_Search by ID','_Search by Nickname','_Search by Tag','_See all music of this user','_See all videos of this user','_Seeking for a','_Select Category','_Select it','_Select module type','_Selected messages','_Send Kiss','_Send eCard','_Send invites','_Send kiss','_Send to communicator','_Send to e-mail','_Send virtual kiss2','_Send virtual kiss3','_Sender','_Services','_Set as thumbnail','_Set up your status','_Sex','_Shoutbox','_Show <b>N</b>-<u>N</u> of N discussions','_Show me','_Showing results:','_Simple Search');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_PWD_INVALID2','_PWD_INVALID3','_Pages','_Payment amount','_Payment description','_Payment info','_Payment methods','_Period','_Period (hours)','_Personal information','_Playbacks','_Please fill up all fields','_Please login before using Ray chat','_Please select at least one search parameter','_Possible subscription period','_Post a new topic','_Post topic','_Prev','_Previous rated','_Print As','_Privacy settings','_Profile Videos','_Profile tags','_Profile_Sites','_Profiles purchase','_Profiles tags','_Quick Search Members','_Quick search results','_RESULT-1','_Ray is not enabled. Select <link> another module','_Read news in archive','_Readed','_Reject','_Reject Invite','_Related Files','_Reload Security Image','_Reply to Someone comment','_Reported','_SIMG_ERR','_STORY_ADDED','_STORY_ADDED_FAILED','_STORY_EMPTY_HEADER','_STORY_UPDATED','_STORY_UPDATED_FAILED','_STORY_VIEW_H','_STORY_VIEW_H1','_Search by','_Search by ID','_Search by Nickname','_Search by Tag','_See all music of this user','_See all videos of this user','_Seeking for a','_Select Category','_Select it','_Select module type','_Selected messages','_Send Kiss','_Send eCard','_Send invites','_Send kiss','_Send to communicator','_Send to e-mail','_Send virtual kiss2','_Send virtual kiss3','_Sender','_Services','_Set as thumbnail','_Set up your status','_Sex','_Shoutbox','_Show <b>N</b>-<u>N</u> of N discussions','_Show me','_Showing results:','_Simple Search');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Sites','_Sites_Calendar','_Sites_Categories','_Sites_Home','_Sorry, no members found','_Sorry, user is OFFLINE','_Sorry, you\'re already joined','_Sort by','_Spy','_Stories2','_Submitted by','_Subscription cancellation request was successfully sent','_Subscriptions','_Successfully uploaded','_Suspended','_Tags_Home','_Tags_caption','_Tags_err_msg','_This Month','_This Week','_This Year','_Total','_Tracker','_UPLOAD_MEDIA','_Uncategorized','_Uncheck all','_Unopened','_Unsubscribe','_Untitled','_Update story','_Upload File','_Upload error','_Upload image','_Upload succesfull','_Upload successful','_Uploaded by','_Uploading','_User was added to hot list','_User was added to im','_Users Other Listing','_Video','_Video Info','_Videos','_View Comments','_View Profile','_View all members','_View all topics','_View my profile','_View profile','_Viewed me','_Vote','_Vote accepted','_Week','_Welcome_to_the_community','_Where','_Write','_Write new Message','_XML Block','_Year','_You already voted','_You should select correct image file','_You should specify at least one member','_You should specify file','_You were approved','_You were rejected','_You\'re not creator','_Your friends','__Aland Islands','__Isle of Man','__Myanmar','__Saint Barthelemy','__Saint Martin (French part)','_about_BoonEx','_active_story','_add answer');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Sites','_Sites_Calendar','_Sites_Categories','_Sites_Home','_Sorry, no members found','_Sorry, user is OFFLINE','_Sorry, you\'re already joined','_Sort by','_Spy','_Stories2','_Submitted by','_Subscription cancellation request was successfully sent','_Subscriptions','_Successfully uploaded','_Suspended','_Tags_Home','_Tags_caption','_Tags_err_msg','_This Month','_This Week','_This Year','_Total','_Tracker','_UPLOAD_MEDIA','_Uncategorized','_Uncheck all','_Unopened','_Unsubscribe','_Untitled','_Update story','_Upload File','_Upload error','_Upload image','_Upload succesfull','_Upload successful','_Uploaded by','_Uploading','_User was added to hot list','_User was added to im','_Users Other Listing','_Video','_Video Info','_Videos','_View Comments','_View Profile','_View all members','_View all topics','_View my profile','_View profile','_Viewed me','_Vote','_Vote accepted','_Week','_Welcome_to_the_community','_Where','_Write','_Write new Message','_XML Block','_Year','_You already voted','_You should select correct image file','_You should specify at least one member','_You should specify file','_You were approved','_You were rejected','_You\'re not creator','_Your friends','__Aland Islands','__Isle of Man','__Myanmar','__Saint Barthelemy','__Saint Martin (French part)','_about_BoonEx','_active_story','_add answer');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_add_category','_add_new','_adm-mmi_css_styles','_adm_admtools_Show_all_files','_adm_bann_default','_adm_box_cpt_css_edit','_adm_box_cpt_email_settings','_adm_box_cpt_lang_keys','_adm_box_cpt_lang_settings','_adm_box_cpt_mlevel_create','_adm_box_cpt_mlevel_edit','_adm_box_cpt_mlevel_levels','_adm_box_cpt_mlevel_settings','_adm_box_cpt_overview','_adm_box_cpt_promo','_adm_box_cpt_watermark','_adm_btn_css_save','_adm_btn_reset_page','_adm_fields_moderator','_adm_mbuilder_Visible_In_Quick_Link','_adm_mmail_Email_already_in_queue_X','_adm_mmail_notify','_adm_mmi_database_pruning','_adm_mmi_extensions','_adm_mmi_manage_subscribers','_adm_mmi_meta_tags','_adm_mmi_moderation_settings','_adm_mmi_permalinks','_adm_mmi_privacy_settings','_adm_mmi_tags_settings','_adm_page_cpt_css_edit','_adm_pbuilder_HTML_content','_adm_tmi_boonex_news','_adm_tmi_dashboard','_adm_tmi_docs','_adm_tmi_menu_builder','_adm_txt_boonex_promo_text','_adm_txt_boonex_promo_title','_adm_txt_css_cannot_write','_adm_txt_css_content','_adm_txt_css_failed_save','_adm_txt_css_file','_adm_txt_css_success_save','_adm_txt_dashboard_alerts','_adm_txt_dashboard_cache_js_css','_adm_txt_dashboard_change_password','_adm_txt_dashboard_extensions','_adm_txt_dashboard_last_login','_adm_txt_dashboard_license','_adm_txt_dashboard_license_unlimit','_adm_txt_dashboard_mails','_adm_txt_dashboard_mails_compose','_adm_txt_dashboard_mails_inbox','_adm_txt_dashboard_mails_sent','_adm_txt_dashboard_mails_trash','_adm_txt_dashboard_users','_adm_txt_dashboard_users_all','_adm_txt_dashboard_users_unapproved','_adm_txt_dashboard_users_unconfirmed','_adm_txt_email_default','_adm_txt_go_to_boonex_feed','_adm_txt_hot_from_unity_market','_adm_txt_keys_parameters','_adm_txt_langs_available','_adm_txt_langs_create','_adm_txt_langs_files','_adm_txt_langs_import','_adm_txt_mlevels_disabled','_adm_txt_mlevels_enabled','_adm_txt_modules_recompile_browse','_adm_txt_modules_recompile_member_stats','_adm_txt_pb_enter_name_of_new_page','_adm_txt_settings_delete','_adm_txt_settings_file_cannot_delete','_adm_txt_settings_promo_browse');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_add_category','_add_new','_adm-mmi_css_styles','_adm_admtools_Show_all_files','_adm_bann_default','_adm_box_cpt_css_edit','_adm_box_cpt_email_settings','_adm_box_cpt_lang_keys','_adm_box_cpt_lang_settings','_adm_box_cpt_mlevel_create','_adm_box_cpt_mlevel_edit','_adm_box_cpt_mlevel_levels','_adm_box_cpt_mlevel_settings','_adm_box_cpt_overview','_adm_box_cpt_promo','_adm_box_cpt_watermark','_adm_btn_css_save','_adm_btn_reset_page','_adm_fields_moderator','_adm_mbuilder_Visible_In_Quick_Link','_adm_mmail_Email_already_in_queue_X','_adm_mmail_notify','_adm_mmi_database_pruning','_adm_mmi_extensions','_adm_mmi_manage_subscribers','_adm_mmi_meta_tags','_adm_mmi_moderation_settings','_adm_mmi_permalinks','_adm_mmi_privacy_settings','_adm_mmi_tags_settings','_adm_page_cpt_css_edit','_adm_pbuilder_HTML_content','_adm_tmi_boonex_news','_adm_tmi_dashboard','_adm_tmi_docs','_adm_tmi_menu_builder','_adm_txt_boonex_promo_text','_adm_txt_boonex_promo_title','_adm_txt_css_cannot_write','_adm_txt_css_content','_adm_txt_css_failed_save','_adm_txt_css_file','_adm_txt_css_success_save','_adm_txt_dashboard_alerts','_adm_txt_dashboard_cache_js_css','_adm_txt_dashboard_change_password','_adm_txt_dashboard_extensions','_adm_txt_dashboard_last_login','_adm_txt_dashboard_license','_adm_txt_dashboard_license_unlimit','_adm_txt_dashboard_mails','_adm_txt_dashboard_mails_compose','_adm_txt_dashboard_mails_inbox','_adm_txt_dashboard_mails_sent','_adm_txt_dashboard_mails_trash','_adm_txt_dashboard_users','_adm_txt_dashboard_users_all','_adm_txt_dashboard_users_unapproved','_adm_txt_dashboard_users_unconfirmed','_adm_txt_email_default','_adm_txt_go_to_boonex_feed','_adm_txt_hot_from_unity_market','_adm_txt_keys_parameters','_adm_txt_langs_available','_adm_txt_langs_create','_adm_txt_langs_files','_adm_txt_langs_import','_adm_txt_mlevels_disabled','_adm_txt_mlevels_enabled','_adm_txt_modules_recompile_browse','_adm_txt_modules_recompile_member_stats','_adm_txt_pb_enter_name_of_new_page','_adm_txt_settings_delete','_adm_txt_settings_file_cannot_delete','_adm_txt_settings_promo_browse');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_adm_txt_settings_promo_image','_adm_txt_settings_promo_text','_adm_txt_settings_promo_uploaded','_affiliate_system_was_disabled','_aged','_answer variants','_block member','_blocked','_both2','_buried','_by keyword','_by newest','_by popular','_categ_calendar','_categ_common','_categ_search','_category_delete_failed','_category_deleted','_category_successfully_added','_changes_successfully_applied','_characters_left','_chat now','_children','_close window','_comment_added_successfully','_comments N','_contacts','_controls','_day(s)','_day_of_1','_day_of_10','_day_of_11','_day_of_12','_day_of_2','_day_of_3','_day_of_4','_day_of_5','_day_of_6','_day_of_7','_day_of_8','_day_of_9','_days ago','_disable able to rate','_edit_category','_enable able to rate','_failed_to_add_category','_failed_to_add_comment','_failed_to_add_post','_faves','_for','_free','_friend member','_friends only','_from ZIP','_from user favorite list','_get other members emails','_greeted','_hot member','_hour(s)','_hours ago','_im_textLogin','_im_textNoCurrUser','_in Category','_in country','_joined','_km','_latest news','_letters','_make it','_make search','_media actions','_member info','_membership','_messages_from','_messages_to');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_adm_txt_settings_promo_image','_adm_txt_settings_promo_text','_adm_txt_settings_promo_uploaded','_affiliate_system_was_disabled','_aged','_answer variants','_block member','_blocked','_both2','_buried','_by keyword','_by newest','_by popular','_categ_calendar','_categ_common','_categ_search','_category_delete_failed','_category_deleted','_category_successfully_added','_changes_successfully_applied','_characters_left','_chat now','_children','_close window','_comment_added_successfully','_comments N','_contacts','_controls','_day(s)','_day_of_1','_day_of_10','_day_of_11','_day_of_12','_day_of_2','_day_of_3','_day_of_4','_day_of_5','_day_of_6','_day_of_7','_day_of_8','_day_of_9','_days ago','_disable able to rate','_edit_category','_enable able to rate','_failed_to_add_category','_failed_to_add_comment','_failed_to_add_post','_faves','_for','_free','_friend member','_friends only','_from ZIP','_from user favorite list','_get other members emails','_greeted','_hot member','_hour(s)','_hours ago','_im_textLogin','_im_textNoCurrUser','_in Category','_in country','_joined','_km','_latest news','_letters','_make it','_make search','_media actions','_member info','_membership','_messages_from','_messages_to');

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_minute(s)','_minutes ago','_never','_no subject','_no_info','_no_messages_from','_no_messages_to','_not_active_story','_please_select','_post my feedback','_post_successfully_added','_powered_by','_question','_ratio','_read','_recurring not allowed','_recurring not supported','_recurring payment','_refresh','_requires_N_members','_sbs_txt_sbs_profile_rates','_seconds ago','_seeking a','_send eCards','_send greetings','_send messages','_sorry, i can not define you ip adress. IT\'S TIME TO COME OUT !','_sys_adm_btn_dnsbl_settings','_sys_album_caption_err_capt','_sys_album_edit','_sys_album_reverse','_sys_breadcrumb_account','_sys_breadcrumb_guest','_sys_breadcrumb_join','_sys_breadcrumb_login','_sys_breadcrumb_logout','_sys_from_album','_sys_invitation_no_users_selected','_tags_date','_tags_empty','_tags_home','_tags_not_found','_tags_search','_td_allow_comment','_td_allow_vote','_td_categories','_this_member','_to_compose_new_message','_toggle','_too_many_files','_unread','_use Orca private forums','_use Orca public forums','_use Ray chat','_use Ray instant messenger','_use Ray video recorder','_use chat','_use forum','_use gallery','_view Video','_view as photo gallery','_view as profile details','_view other members\' galleries','_view profiles','_viewed','_vote','_why_join_desc','_within','_wrote','_{0} votes');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_minute(s)','_minutes ago','_never','_no subject','_no_info','_no_messages_from','_no_messages_to','_not_active_story','_please_select','_post my feedback','_post_successfully_added','_powered_by','_question','_ratio','_read','_recurring not allowed','_recurring not supported','_recurring payment','_refresh','_requires_N_members','_sbs_txt_sbs_profile_rates','_seconds ago','_seeking a','_send eCards','_send greetings','_send messages','_sorry, i can not define you ip adress. IT\'S TIME TO COME OUT !','_sys_adm_btn_dnsbl_settings','_sys_album_caption_err_capt','_sys_album_edit','_sys_album_reverse','_sys_breadcrumb_account','_sys_breadcrumb_guest','_sys_breadcrumb_join','_sys_breadcrumb_login','_sys_breadcrumb_logout','_sys_from_album','_sys_invitation_no_users_selected','_tags_date','_tags_empty','_tags_home','_tags_not_found','_tags_search','_td_allow_comment','_td_allow_vote','_td_categories','_this_member','_to_compose_new_message','_toggle','_too_many_files','_unread','_use Orca private forums','_use Orca public forums','_use Ray chat','_use Ray instant messenger','_use Ray video recorder','_use chat','_use forum','_use gallery','_view Video','_view as photo gallery','_view as profile details','_view other members\' galleries','_view profiles','_viewed','_vote','_why_join_desc','_within','_wrote','_{0} votes');



-- last step is to update current version

INSERT INTO `sys_options` VALUES ('sys_tmp_version', '7.1.0.B1', 0, 'Temporary Dolphin version ', 'digit', '', '', 0, '') ON DUPLICATE KEY UPDATE `VALUE` = '7.1.0.B1';

