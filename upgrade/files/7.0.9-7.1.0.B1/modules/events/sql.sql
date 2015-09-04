
-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile', 'member') AND `Desc` IN ('Events', 'User Events', 'Joined Events');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Func` IN ('Actions', 'Rate', 'Info', 'Participants', 'ParticipantsUnconfirmed', 'Desc', 'Photos', 'Videos', 'Sounds', 'Files', 'Comments', 'SocialSharing');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_view' AND `Desc` = 'Event''s Location';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Func` IN ('UpcomingPhoto', 'UpcomingList', 'PastList', 'RecentlyAddedList', 'Calendar');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Desc` = 'Map';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_my' AND `Func` IN ('Owner', 'Browse');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('bx_events_view', 'bx_events_main', 'bx_events_my');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_events_view', '1140px', 'Event''s info block', '_bx_events_block_info', '2', '0', 'Info', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s actions block', '_bx_events_block_actions', '2', '1', 'Actions', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s rate block', '_bx_events_block_rate', '2', '2', 'Rate', '', '1', '28.1', 'non,memb', '0'),    
    ('bx_events_view', '1140px', 'Event''s social sharing block', '_sys_block_title_social_sharing', '2', '3', 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
    ('bx_events_view', '1140px', 'Event''s files block', '_bx_events_block_files', '2', '4', 'Files', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s participants block', '_bx_events_block_participants', '2', '5', 'Participants', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s unconfirmed participants block', '_bx_events_block_participants_unconfirmed', '2', '6', 'ParticipantsUnconfirmed', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s Location', '_Location', '2', '7', 'PHP', 'return BxDolService::call(''wmap'', ''location_block'', array(''events'', $this->aDataEntry[$this->_oDb->_sFieldId]));', 1, 28.1, 'non,memb', 0),
    ('bx_events_view', '1140px', 'Event''s description block', '_bx_events_block_desc', '1', '0', 'Desc', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s photos block', '_bx_events_block_photos', '1', '1', 'Photos', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s videos block', '_bx_events_block_videos', '1', '2', 'Videos', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s sounds block', '_bx_events_block_sounds', '1', '3', 'Sounds', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_view', '1140px', 'Event''s comments block', '_bx_events_block_comments', '1', '4', 'Comments', '', '1', '71.9', 'non,memb', '0'),    

    ('bx_events_main', '1140px', 'Upcoming Events Photo', '_bx_events_block_upcoming_photo', '1', '0', 'UpcomingPhoto', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Upcoming Events List', '_bx_events_block_upcoming_list', '1', '1', 'UpcomingList', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Map', '_Map', '1', '2', 'PHP', 'return BxDolService::call(''wmap'', ''homepage_part_block'', array (''events''));', 1, 71.9, 'non,memb', 0),
    ('bx_events_main', '1140px', 'Calendar', '_bx_events_block_calendar', '2', '0', 'Calendar', '', '1', '28.1', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Past Events', '_bx_events_block_past_list', '0', '0', 'PastList', '', '1', '71.9', 'non,memb', '0'),
    ('bx_events_main', '1140px', 'Recently Added Events', '_bx_events_block_recently_added_list', '0', '0', 'RecentlyAddedList', '', '1', '71.9', 'non,memb', '0'),

    ('bx_events_my', '1140px', 'Administration', '_bx_events_block_administration', '1', '0', 'Owner', '', '1', '100', 'non,memb', '0'),
    ('bx_events_my', '1140px', 'User''s events', '_bx_events_block_user_events', '1', '1', 'Browse', '', '0', '100', 'non,memb', '0'),

    ('index', '1140px', 'Events', '_bx_events_block_home', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''homepage_block'');', 1, 71.9, 'non,memb', 0),
    ('profile', '1140px', 'User Events', '_bx_events_block_my_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
    ('profile', '1140px', 'Joined Events', '_bx_events_block_joined_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0),
    ('member', '1140px', 'Joined Events', '_bx_events_block_joined_events', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''events'', ''profile_block_joined'', array($this->oProfileGen->_iProfileID));', 1, 71.9, 'non,memb', 0);


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_events' AND `Caption` IN ('{TitleEdit}', '{TitleDelete}', '{TitleJoin}', '{TitleInvite}', '{TitleShare}', '{TitleBroadcast}', '{AddToFeatured}', '{TitleManageFans}', '{TitleUploadPhotos}', '{TitleUploadVideos}', '{TitleUploadSounds}', '{TitleUploadFiles}', '{TitleSubscribe}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_events_title' AND (`Eval` LIKE '%_bx_events_action_create_event%' OR `Eval` LIKE '%_bx_events_action_my_events%' OR `Eval` LIKE '%_bx_events_action_events_home%');

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''edit/{ID}'';', '0', 'bx_events'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', '1', 'bx_events'),
    ('{TitleJoin}', '{IconJoin}', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''join/{ID}/{iViewer}'';', '2', 'bx_events'),
    ('{TitleInvite}', 'plus-sign', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''invite/{ID}'';', '3', 'bx_events'),
    ('{TitleShare}', 'share', '', 'showPopupAnyHtml (''{BaseUri}share_popup/{ID}'');', '', '4', 'bx_events'),
    ('{TitleBroadcast}', 'envelope', '{BaseUri}broadcast/{ID}', '', '', '5', 'bx_events'),
    ('{AddToFeatured}', 'star-empty', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxEventsModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''mark_featured/{ID}'';', 6, 'bx_events'),

    ('{TitleManageFans}', 'group', '', 'showPopupAnyHtml (''{BaseUri}manage_fans_popup/{ID}'');', '', '7', 'bx_events'),
    ('{TitleUploadPhotos}', 'picture', '{BaseUri}upload_photos/{URI}', '', '', '8', 'bx_events'),
    ('{TitleUploadVideos}', 'film', '{BaseUri}upload_videos/{URI}', '', '', '9', 'bx_events'),
    ('{TitleUploadSounds}', 'music', '{BaseUri}upload_sounds/{URI}', '', '', '10', 'bx_events'),
    ('{TitleUploadFiles}', 'save', '{BaseUri}upload_files/{URI}', '', '', '11', 'bx_events'),    

    ('{TitleSubscribe}', 'paper-clip', '', '{ScriptSubscribe}', '', 7, 'bx_events'),
    ('{evalResult}', 'plus', '{BaseUri}browse/my&bx_events_filter=add_event', '', 'return ($GLOBALS[''logged''][''member''] && BxDolModule::getInstance(''BxEventsModule'')->isAllowedAdd()) || $GLOBALS[''logged''][''admin''] ? _t(''_bx_events_action_create_event'') : '''';', 1, 'bx_events_title'),
    ('{evalResult}', 'calendar', '{BaseUri}browse/my', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_events_action_my_events'') : '''';', '2', 'bx_events_title');


-- menu top

SET @iMenuEventsSystem = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'Events');
UPDATE `sys_menu_top` SET `Picture` = 'calendar' WHERE `ID` = @iMenuEventsSystem;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuEventsSystem;
UPDATE `sys_menu_top` SET `Check` = '$oModuleDb = new BxDolModuleDb(); return $oModuleDb->getModuleByUri(''forum'') ? true : false;' WHERE `Parent` = @iMenuEventsSystem AND `Name` = 'Event View Forum';

SET @iMenuEventsTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Events');
UPDATE `sys_menu_top` SET `Picture` = 'calendar', `Icon` = 'calendar' WHERE `ID` = @iMenuEventsTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuEventsTop;
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=events/browse/user/{profileUsername}|modules/?r=events/browse/joined/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Events';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_events';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_events', `Eval` = 'return BxDolService::call(''events'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'calendar' WHERE `name` = 'bx_events';


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('bx_events_invitation', 'bx_events_broadcast', 'bx_events_sbs', 'bx_events_join_request', 'bx_events_join_reject', 'bx_events_join_confirm', 'bx_events_fan_remove', 'bx_events_fan_become_admin', 'bx_events_admin_become_fan');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('bx_events_invitation', 'Invitation To Event: <EventName>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<InviterUrl>"><InviterNickName></a> invited you to this event:</p>\r\n\r\n<hr>\r\n<pre>\r\n<InvitationText>\r\n</pre>\r\n<hr>  \r\n\r\n<p>\r\nEvent Information:<br /> \r\nName: <EventName><br /> \r\nLocation: <EventLocation><br /> \r\nTime: <EventStart><br /> <br />\r\n\r\n<a href="<EventUrl>">More details</a>\r\n</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event invitation', 0),
('bx_events_broadcast', '<BroadcastTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<EntryUrl>"><EntryTitle></a> event admin message:</p> <hr><BroadcastMessage><hr> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Broadcast', 0),
('bx_events_sbs', 'Subscription: Event Details Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<ViewLink>"><EntryTitle></a> event details changed: <br /> <ActionName> </p> \r\n<hr>\r\n<p>Cancel this subscription: <a href="<UnsubscribeLink>"><UnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Subscription', 0),
('bx_events_join_request', 'New Request To Join Your Event', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>New request to join your event: <a href="<EntryUrl>"><EntryTitle></a>.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'New join request to an event', 0),
('bx_events_join_reject', 'Your Request To Join Event Was Rejected', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> \r\n\r\n<p>Your request to join <a href="<EntryUrl>"><EntryTitle></a> event was rejected by event admin.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Request To Join Event Was Rejected', 0),
('bx_events_join_confirm', 'Your Request To Join Event Was Approved', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p>Congratulations! Your request to join <a href="<EntryUrl>"><EntryTitle></a> event was approved by the event admin.</p>\r\n \r\n<bx_include_auto:_email_footer.html />', 'Request To Join Event Approved', 0),
('bx_events_fan_remove', 'You Were Removed From Event Participants', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> <p>You were removed from participants of <a href="<EntryUrl>"><EntryTitle></a> event by the event admin.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Removed From Event Participants', 0),
('bx_events_fan_become_admin', 'You Are The Event Admin Now', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> \r\n<p>You are an admin of <a href="<EntryUrl>"><EntryTitle></a> event now.\r\n</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Event Admin Status Granted', 0),
('bx_events_admin_become_fan', 'You Are No Longer The Event Admin', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> <p>Your admin status was revoked from <a href="<EntryUrl>"><EntryTitle></a> event by the event creator.</p> \r\n\r\n<bx_include_auto:_email_footer.html />', 'Event admin status revoked.', 0);


-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'evs';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES
(NULL, 'evs', 'bx_events', 'modules/?r=events/browse/recent', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `Status`=''approved''', 'modules/?r=events/administration', 'SELECT COUNT(`ID`) FROM `[db_prefix]main` WHERE `Status`=''pending''', 'calendar', @iStatSiteOrder);


-- alert handlers

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_events_map_install' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_events_map_install', '', '', 'if (''wmap'' == $this->aExtras[''uri''] && $this->aExtras[''res''][''result'']) BxDolService::call(''events'', ''map_install'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'module', 'install', @iHandler);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_events' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_events' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_events';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_events', '_bx_events', '0.8', 'auto', 'BxEventsSiteMaps', 'modules/boonex/events/classes/BxEventsSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_events';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_events', '_bx_events', 'bx_events_main', 'Date', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_event_wall_added_new','_bx_event_wall_object','_bx_events_action_events_home','_bx_events_add_new_event_admin','_bx_events_admin_home','_bx_events_allow_comments_all','_bx_events_allow_comments_friends','_bx_events_allow_comments_none','_bx_events_allow_comments_participants','_bx_events_allow_rate_all','_bx_events_allow_rate_friends','_bx_events_allow_rate_none','_bx_events_allow_rate_participants','_bx_events_announce','_bx_events_caption_allow_comments','_bx_events_caption_allow_rate','_bx_events_caption_allow_view_participants','_bx_events_caption_author_id','_bx_events_caption_join_filter','_bx_events_caption_photo','_bx_events_err_allow_comments','_bx_events_err_allow_rate','_bx_events_err_allow_view_participants','_bx_events_err_join_filter','_bx_events_err_photo','_bx_events_error_occured','_bx_events_info_allow_comments','_bx_events_info_allow_rete','_bx_events_info_allow_view_participants','_bx_events_info_author','_bx_events_info_join_filter','_bx_events_join_all','_bx_events_join_friends_only','_bx_events_menu_add','_bx_events_menu_my_events','_bx_events_menu_my_pending_events','_bx_events_menu_view_files','_bx_events_menu_view_photos','_bx_events_menu_view_sounds','_bx_events_menu_view_videos','_bx_events_msg_access_denied','_bx_events_msg_page_not_found','_bx_events_photo_is_pending_approval','_bx_events_settings_admin','_bx_events_view_participants_all','_bx_events_view_participants_friends','_bx_events_view_participants_none');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_event_wall_added_new','_bx_event_wall_object','_bx_events_action_events_home','_bx_events_add_new_event_admin','_bx_events_admin_home','_bx_events_allow_comments_all','_bx_events_allow_comments_friends','_bx_events_allow_comments_none','_bx_events_allow_comments_participants','_bx_events_allow_rate_all','_bx_events_allow_rate_friends','_bx_events_allow_rate_none','_bx_events_allow_rate_participants','_bx_events_announce','_bx_events_caption_allow_comments','_bx_events_caption_allow_rate','_bx_events_caption_allow_view_participants','_bx_events_caption_author_id','_bx_events_caption_join_filter','_bx_events_caption_photo','_bx_events_err_allow_comments','_bx_events_err_allow_rate','_bx_events_err_allow_view_participants','_bx_events_err_join_filter','_bx_events_err_photo','_bx_events_error_occured','_bx_events_info_allow_comments','_bx_events_info_allow_rete','_bx_events_info_allow_view_participants','_bx_events_info_author','_bx_events_info_join_filter','_bx_events_join_all','_bx_events_join_friends_only','_bx_events_menu_add','_bx_events_menu_my_events','_bx_events_menu_my_pending_events','_bx_events_menu_view_files','_bx_events_menu_view_photos','_bx_events_menu_view_sounds','_bx_events_menu_view_videos','_bx_events_msg_access_denied','_bx_events_msg_page_not_found','_bx_events_photo_is_pending_approval','_bx_events_settings_admin','_bx_events_view_participants_all','_bx_events_view_participants_friends','_bx_events_view_participants_none');



-- update module version

UPDATE `sys_modules` SET `dependencies` = '' WHERE `uri` = 'events';
UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'events' AND `version` = '1.0.9';

