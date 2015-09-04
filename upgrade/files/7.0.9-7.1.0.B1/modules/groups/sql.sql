
-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile', 'member') AND `Desc` IN ('Groups', 'User Groups', 'Joined Groups');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_view' AND `Func` IN ('Actions', 'Rate', 'Info', 'Fans', 'FansUnconfirmed', 'Desc', 'Photo', 'Video', 'Sound', 'Files', 'Comments', 'SocialSharing');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_view' AND `Desc` = 'Group''s Location';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_main' AND `Func` IN ('LatestFeaturedGroup', 'Recent', 'Categories');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_main' AND `Desc` = 'Map';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_my' AND `Func` IN ('Owner', 'Browse');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('bx_groups_view', 'bx_groups_main', 'bx_groups_my');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_groups_view', '1140px', 'Group''s info block', '_bx_groups_block_info', 2, 0, 'Info', '', '1', 28.1, 'non,memb', '0'),
    ('bx_groups_view', '1140px', 'Group''s actions block', '_bx_groups_block_actions', 2, 1, 'Actions', '', '1', 28.1, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s rate block', '_bx_groups_block_rate', 2, 2, 'Rate', '', '1', 28.1, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s social sharing block', '_sys_block_title_social_sharing', 2, 3, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
    ('bx_groups_view', '1140px', 'Group''s fans block', '_bx_groups_block_fans', 2, 4, 'Fans', '', '1', 28.1, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s unconfirmed fans block', '_bx_groups_block_fans_unconfirmed', 2, 5, 'FansUnconfirmed', '', '1', 28.1, 'memb', '0'),
    ('bx_groups_view', '1140px', 'Group''s Location', '_Location', 2, 6, 'PHP', 'return BxDolService::call(''wmap'', ''location_block'', array(''groups'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 1, 28.1, 'non,memb', 0),
    ('bx_groups_view', '1140px', 'Group''s description block', '_bx_groups_block_desc', 1, 0, 'Desc', '', '1', 71.9, 'non,memb', '0'),
    ('bx_groups_view', '1140px', 'Group''s photo block', '_bx_groups_block_photo', 1, 1, 'Photo', '', '1', 71.9, 'non,memb', '0'),
    ('bx_groups_view', '1140px', 'Group''s videos block', '_bx_groups_block_video', 1, 2, 'Video', '', '1', 71.9, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s sounds block', '_bx_groups_block_sound', 1, 3, 'Sound', '', '1', 71.9, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s files block', '_bx_groups_block_files', 1, 4, 'Files', '', '1', 71.9, 'non,memb', '0'),    
    ('bx_groups_view', '1140px', 'Group''s comments block', '_bx_groups_block_comments', 1, 5, 'Comments', '', '1', 71.9, 'non,memb', '0'),

    ('bx_groups_main', '1140px', 'Latest Featured Group', '_bx_groups_block_latest_featured_group', '1', '0', 'LatestFeaturedGroup', '', '1', '71.9', 'non,memb', '0'),
    ('bx_groups_main', '1140px', 'Recent Groups', '_bx_groups_block_recent', '1', '1', 'Recent', '', '1', '71.9', 'non,memb', '0'),
    ('bx_groups_main', '1140px', 'Map', '_Map', '1', '2', 'PHP', 'return BxDolService::call(''wmap'', ''homepage_part_block'', array (''groups''));', 1, 71.9, 'non,memb', 0),
    ('bx_groups_main', '1140px', 'Groups Categories', '_bx_groups_block_categories', '2', '0', 'Categories', '', '1', '28.1', 'non,memb', '0'),

    ('bx_groups_my', '1140px', 'Administration Owner', '_bx_groups_block_administration_owner', '1', '0', 'Owner', '', '1', '100', 'non,memb', '0'),
    ('bx_groups_my', '1140px', 'User''s groups', '_bx_groups_block_users_groups', '1', '1', 'Browse', '', '0', '100', 'non,memb', '0'),

    ('index', '1140px', 'Groups', '_bx_groups_block_homepage', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''groups'', ''homepage_block'');', 1, 71.9, 'non,memb', 0),
	('profile', '1140px', 'Joined Groups', '_bx_groups_block_my_groups_joined', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''groups'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
    ('profile', '1140px', 'User Groups', '_bx_groups_block_my_groups', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''groups'', ''profile_block'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
    ('member', '1140px', 'Joined Groups', '_bx_groups_block_my_groups_joined', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''groups'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0);


-- objects: actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_groups' AND `Caption` IN ('{TitleEdit}', '{TitleDelete}', '{TitleShare}', '{TitleBroadcast}', '{TitleJoin}', '{TitleInvite}', '{AddToFeatured}', '{TitleManageFans}', '{TitleUploadPhotos}', '{TitleUploadVideos}', '{TitleUploadSounds}', '{TitleUploadFiles}', '{TitleSubscribe}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_groups_title' AND (`Eval` LIKE '%_bx_groups_action_add_group%' OR `Eval` LIKE '%_bx_groups_action_my_groups%' OR `Eval` LIKE '%_bx_groups_action_groups_home%');

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''edit/{ID}'';', '0', 'bx_groups'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', 1, 'bx_groups'),
    ('{TitleShare}', 'share', '', 'showPopupAnyHtml (''{BaseUri}share_popup/{ID}'');', '', '2', 'bx_groups'),
    ('{TitleBroadcast}', 'envelope', '{BaseUri}broadcast/{ID}', '', '', '3', 'bx_groups'),
    ('{TitleJoin}', '{IconJoin}', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''join/{ID}/{iViewer}'';', '4', 'bx_groups'),
    ('{TitleInvite}', 'plus-sign', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''invite/{ID}'';', '5', 'bx_groups'),
    ('{AddToFeatured}', 'star-empty', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxGroupsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''mark_featured/{ID}'';', '6', 'bx_groups'),
    ('{TitleManageFans}', 'group', '', 'showPopupAnyHtml (''{BaseUri}manage_fans_popup/{ID}'');', '', '8', 'bx_groups'),
    ('{TitleUploadPhotos}', 'picture', '{BaseUri}upload_photos/{URI}', '', '', '9', 'bx_groups'),
    ('{TitleUploadVideos}', 'film', '{BaseUri}upload_videos/{URI}', '', '', '10', 'bx_groups'),
    ('{TitleUploadSounds}', 'music', '{BaseUri}upload_sounds/{URI}', '', '', '11', 'bx_groups'),
    ('{TitleUploadFiles}', 'save', '{BaseUri}upload_files/{URI}', '', '', '12', 'bx_groups'),
    ('{TitleSubscribe}', 'paper-clip', '', '{ScriptSubscribe}', '', '13', 'bx_groups'),
    ('{evalResult}', 'plus', '{BaseUri}browse/my&bx_groups_filter=add_group', '', 'return ($GLOBALS[''logged''][''member''] && BxDolModule::getInstance(''BxGroupsModule'')->isAllowedAdd()) || $GLOBALS[''logged''][''admin''] ? _t(''_bx_groups_action_add_group'') : '''';', 1, 'bx_groups_title'),
    ('{evalResult}', 'group', '{BaseUri}browse/my', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_groups_action_my_groups'') : '''';', '2', 'bx_groups_title');


-- menu top

SET @iMenuGroupsSystem = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'Groups');
UPDATE `sys_menu_top` SET `Picture` = 'group' WHERE `ID` = @iMenuGroupsSystem;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuGroupsSystem;
UPDATE `sys_menu_top` SET `Check` = '$oModuleDb = new BxDolModuleDb(); return $oModuleDb->getModuleByUri(''forum'') ? true : false;' WHERE `Parent` = @iMenuGroupsSystem AND `Name` = 'Group View Forum';

SET @iMenuGroupsTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Groups');
UPDATE `sys_menu_top` SET `Picture` = 'group', `Icon` = 'group' WHERE `ID` = @iMenuGroupsTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuGroupsTop;
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=groups/browse/user/{profileUsername}|modules/?r=groups/browse/joined/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Groups';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_groups';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_groups', `Eval` = 'return BxDolService::call(''groups'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- menu admin 

UPDATE `sys_menu_admin` SET `icon` = 'group' WHERE `name` = 'bx_groups';


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'bx_groups';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'bx_groups', 'bx_groups', 'modules/?r=groups/browse/recent', 'SELECT COUNT(`id`) FROM `[db_prefix]main` WHERE `status`=''approved''', 'modules/?r=groups/administration', 'SELECT COUNT(`id`) FROM `[db_prefix]main` WHERE `status`=''pending''', 'group', @iStatSiteOrder);


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('bx_groups_broadcast', 'bx_groups_join_request', 'bx_groups_join_reject', 'bx_groups_join_confirm', 'bx_groups_fan_remove', 'bx_groups_fan_become_admin', 'bx_groups_admin_become_fan', 'bx_groups_invitation', 'bx_groups_sbs');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('bx_groups_broadcast', '<BroadcastTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n<p>Message from <a href="<EntryUrl>"><EntryTitle></a> group admin:</p> <pre><hr><BroadcastMessage></pre> <hr> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Groups broadcast message', 0),
('bx_groups_join_request', 'Request To Join Your Group', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>New request to join your group: <a href="<EntryUrl>"><EntryTitle></a>.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Join request to a group', 0),
('bx_groups_join_reject', 'Request To Join A Group Was Rejected', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> <p>Your request to join <a href="<EntryUrl>"><EntryTitle></a> group was rejected by group admin.</p> \r\n<bx_include_auto:_email_footer.html />', 'Join group request was rejected', 0),
('bx_groups_join_confirm', 'Your Request To Join A Group Was Confirmed', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n<p>Your request to join <a href="<EntryUrl>"><EntryTitle></a> group was confirmed by the group admin.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Join group request confirmed', 0),
('bx_groups_fan_remove', 'Your Profile Removed From Group Fans', '<bx_include_auto:_email_header.html /> \r\n\r\n<p>Hello <NickName>,</p> <p>Your profile was removed fans list of <a href="<EntryUrl>"><EntryTitle></a> group by the group admin.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Profile Removed From Group Fans', 0),
('bx_groups_fan_become_admin', 'You Are A Group Admin Now', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>You are an admin of <a href="<EntryUrl>"><EntryTitle></a> group now.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Group admin status granted', 0),
('bx_groups_admin_become_fan', 'Your Group Admin Status Was Revoked', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>Your admin status was revoked from <a href="<EntryUrl>"><EntryTitle></a> group by the group creator.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Group admin status revoked', 0),
('bx_groups_invitation', 'Invitation to <GroupName> Group', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<InviterUrl>"><InviterNickName></a> invited you to <a href="<GroupUrl>"><GroupName> group</a>.</p> \r\n\r\n<p>\r\n<hr><InvitationText><hr> \r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Invitation to group', 0),
('bx_groups_sbs', 'Subscription: Group Details Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<ViewLink>"><EntryTitle></a> group details changed: <br /> <ActionName> </p> \r\n<hr>\r\n<p>Cancel this subscription: <a href="<UnsubscribeLink>"><UnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: group changes', 0);


-- alert handlers

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_groups_map_install' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_groups_map_install', '', '', 'if (''wmap'' == $this->aExtras[''uri''] && $this->aExtras[''res''][''result'']) BxDolService::call(''groups'', ''map_install'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'module', 'install', @iHandler);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_groups' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_groups' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_groups';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_groups', '_bx_groups', '0.8', 'auto', 'BxGroupsSiteMaps', 'modules/boonex/groups/classes/BxGroupsSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_groups';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_groups', '_bx_groups', 'bx_groups_main', 'created', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_groups_action_groups_home','_bx_groups_add','_bx_groups_block_file','_bx_groups_caption_add','_bx_groups_caption_admin_actions','_bx_groups_caption_pending_approval','_bx_groups_for_group','_bx_groups_form_caption_author_id','_bx_groups_form_caption_file_allow_purcase_to','_bx_groups_form_caption_file_price','_bx_groups_form_err_city','_bx_groups_form_err_country','_bx_groups_form_err_zip','_bx_groups_form_info_author','_bx_groups_menu_view_files','_bx_groups_menu_view_photos','_bx_groups_menu_view_sounds','_bx_groups_menu_view_videos','_bx_groups_msg_photo_is_pending_approval','_bx_groups_privacy_edit_group');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_groups_action_groups_home','_bx_groups_add','_bx_groups_block_file','_bx_groups_caption_add','_bx_groups_caption_admin_actions','_bx_groups_caption_pending_approval','_bx_groups_for_group','_bx_groups_form_caption_author_id','_bx_groups_form_caption_file_allow_purcase_to','_bx_groups_form_caption_file_price','_bx_groups_form_err_city','_bx_groups_form_err_country','_bx_groups_form_err_zip','_bx_groups_form_info_author','_bx_groups_menu_view_files','_bx_groups_menu_view_photos','_bx_groups_menu_view_sounds','_bx_groups_menu_view_videos','_bx_groups_msg_photo_is_pending_approval','_bx_groups_privacy_edit_group');



-- update module version

UPDATE `sys_modules` SET `dependencies` = '' WHERE `uri` = 'groups';
UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'groups' AND `version` = '1.0.9';

