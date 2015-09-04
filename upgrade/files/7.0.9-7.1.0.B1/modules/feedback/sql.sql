

-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'thumbs-up', `Name` = 'FeedbackView' WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = '[db_prefix]_view';


-- menu member

DELETE FROM `sys_menu_member` WHERE `Name` = 'Feedback';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'thumbs-up' WHERE `name` = 'bx_feedback';


-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index') AND `Desc` IN ('Feedback from your customers');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('Content', 'Comment', 'Action', 'Vote', 'SocialSharing') AND `Page` = 'feedback';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'feedback';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Feedback from your customers', '_feedback_bcaption_index', 0, 0, 'PHP', 'return BxDolService::call(\'feedback\', \'archive_block_index\', array(0, 0, false));', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback main content', '_feedback_bcaption_view_main', 1, 0, 'Content', '', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback comments', '_feedback_bcaption_view_comment', 1, 1, 'Comment', '', 1, 71.9, 'non,memb', 0),
('feedback', '1140px', 'Feedback actions', '_feedback_bcaption_view_action', 2, 0, 'Action', '', 1, 28.1, 'non,memb', 0),
('feedback', '1140px', 'Feedback rating', '_feedback_bcaption_view_vote', 2, 1, 'Vote', '', 1, 28.1, 'non,memb', 0),
('feedback', '1140px', 'Social sharing', '_sys_block_title_social_sharing', 2, 2, 'SocialSharing', '', 1, 28.1, 'non,memb', 0);


-- objects: actions 

UPDATE `sys_objects_actions` SET `Icon` = 'paper-clip' WHERE `Caption` = '{sbs_feedback_title}' AND `Type` = 'bx_feedback';
UPDATE `sys_objects_actions` SET `Icon` = 'remove' WHERE `Caption` = '{del_feedback_title}' AND `Type` = 'bx_feedback';


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_feedback' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_feedback' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsFeedbackComments', 't_sbsFeedbackRates');
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsFeedbackComments', 'New Comments To A Feedback Post', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">feedback post you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to feedback', 0);


-- site stats

DELETE FROM `sys_stat_site` WHERE `Name` = 'fdb';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'fdb', 'feedback_ss', 'modules/?r=feedback/index/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''0''', 'modules/?r=feedback/admin/', 'SELECT COUNT(`ID`) FROM `[db_prefix]entries` WHERE `status`=''1''', 'thumbs-up', @iStatSiteOrder);


-- sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_feedback';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_feedback', '_feedback_sitemap', '0.8', 'auto', 'BxFdbSiteMaps', 'modules/boonex/feedback/classes/BxFdbSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_feedback';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_feedback', '_feedback_chart', 'bx_fdb_entries', 'date', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_allow_comment_to','_allow_vote_to','_feedback_action_error_access_denied','_feedback_action_failed_approve','_feedback_action_failed_delete','_feedback_action_failed_reject','_feedback_action_success_approve','_feedback_action_success_delete','_feedback_action_success_reject','_feedback_ext_menu_item','_feedback_sbs_rate');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_allow_comment_to','_allow_vote_to','_feedback_action_error_access_denied','_feedback_action_failed_approve','_feedback_action_failed_delete','_feedback_action_failed_reject','_feedback_action_success_approve','_feedback_action_success_delete','_feedback_action_success_reject','_feedback_ext_menu_item','_feedback_sbs_rate');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'feedback' AND `version` = '1.0.9';

