

-- stats site

DELETE FROM `sys_stat_site` WHERE `Name` = 'pls';

SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO 
    `sys_stat_site` 
SET 
    `Name`       = 'pls', 
    `Title`      = 'bx_polls', 
    `UserLink`   = 'modules/?r=poll/',
    `UserQuery`  = 'SELECT COUNT(`id_poll`) FROM `bx_poll_data` WHERE `poll_approval`=1 and `poll_status` = ''active'' ', 
    `AdminLink`  = 'modules/?r=poll/administration', 
    `AdminQuery` = 'SELECT COUNT(`id_poll`) FROM `bx_poll_data` WHERE `poll_approval`=0', 
    `IconName`   = 'tasks', 
    `StatOrder`  = @iStatSiteOrder;


-- menu admin

DELETE FROM `sys_menu_admin` WHERE `name` = 'Polls';

SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT INTO 
    `sys_menu_admin` 
SET
    `name`          = 'Polls',
    `title`         = '_bx_polls', 
    `url`           = '{siteUrl}modules/?r=poll/administration/',
    `description`   = 'Members can create their own polls, and you can moderate them right here',
    `icon`          = 'tasks',
    `parent_id`     = 2,
    `order`         = @iMax;


-- menu top

UPDATE `sys_menu_top` SET `Picture` = 'tasks' WHERE `Name` = 'Poll unit' AND `Parent` = 0;

SET @iMenuParentId = (SELECT `ID` FROM `sys_menu_top` WHERE `Name` = 'Polls' AND `Type` = 'top' AND `Parent` = 0);

UPDATE `sys_menu_top` SET `Picture` = 'tasks', `Icon` = 'tasks' WHERE `ID` = @iMenuParentId;

UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuParentId;

UPDATE `sys_menu_top` SET `Link` = 'modules/?r=poll/&action=user&nickname={profileUsername}' WHERE `Name` = 'Polls' AND `Parent` = 9;


-- menu member 

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_poll';

SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_poll', `Eval` = 'return BxDolService::call(''poll'', ''get_member_menu_link_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- page builder

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile') AND `Desc` IN ('Member polls', 'Member''s polls block');
DELETE FROM `sys_page_compose` WHERE `Func` IN ('LatestHome', 'FeaturedHome') AND `Page` = 'poll_home';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('View', 'Comments', 'Action', 'Owner information', 'Votes', 'Social sharing', 'OwnerBlock', 'VotingsBlock', 'CommentsBlock', 'PoolBlock', 'ActionsBlock') AND `Page` = 'show_poll_info';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'poll_home' OR `Page` = 'show_poll_info';

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '1140px', 'Member polls', '_bx_polls', 0, 0, 'PHP', 'BxDolService::call(''poll'', ''get_polls'', array(''get_polls''));', 0, 28.1, 'non,memb', 0),
('profile', '1140px', 'Member polls', '_bx_polls', 0, 0, 'PHP', 'BxDolService::call(''poll'', ''get_polls'', array(''get_profile_polls'', $this->oProfileGen->_iProfileID));', 0, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('poll_home', '1140px', 'Latest polls', '_bx_poll_latest_public', 1, 1, 'LatestHome', '', 1, 71.9, 'non,memb', 0),
('poll_home', '1140px', 'Featured polls', '_bx_poll_featured', 2, 1, 'FeaturedHome', '', 1, 28.1, 'non,memb', 0),
('show_poll_info', '1140px', 'View', '_bx_poll', 1, 1, 'PoolBlock', '', 1, 71.9, 'non,memb', 0),
('show_poll_info', '1140px', 'Comments', '_bx_poll_comments', 1, 2, 'CommentsBlock', '', 1, 71.9, 'non,memb', 0),
('show_poll_info', '1140px', 'Action', '_bx_poll_actions', 2, 1, 'ActionsBlock', '', 1, 28.1, 'non,memb', 0),
('show_poll_info', '1140px', 'Owner information', '_bx_poll_owner', 2, 2, 'OwnerBlock', '', 1, 28.1, 'non,memb', 0),
('show_poll_info', '1140px', 'Votes', '_bx_poll_votings', 2, 3, 'VotingsBlock', '', 1, 28.1, 'non,memb', 0),
('show_poll_info', '1140px', 'Social sharing', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', 1, 28.1, 'non,memb', 0);


-- objects: actions

DELETE FROM `sys_objects_actions` WHERE `Type` IN ('bx_poll_title', 'bx_poll') AND `Caption` IN ('_bx_poll_add', '_bx_poll_my', '_bx_poll_edit', '_bx_poll_delete', '_bx_poll_share', '_bx_poll_home', '{sbs_poll_title}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_poll_title' AND `Eval` LIKE '%_bx_poll_add%';

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{evalResult}', 'plus', '{BaseUri}&action=my&mode=add', '', 'return (getLoggedId() && BxDolModule::getInstance(''BxPollModule'')->isPollCreateAlowed()) ? _t(''_bx_poll_add'') : '''';', 1, 'bx_poll_title', 1),
('_bx_poll_my', 'tasks', '{evalResult}', '', 'return isMember() ? ''{BaseUri}&action=my'' : null;', 2, 'bx_poll_title', 1),
('_bx_poll_edit', 'edit', '{evalResult}', '', 'return isMember() ? BxDolService::call(''poll'', ''edit_action_button'', array({ID}, {PollId})) : null;', 1, 'bx_poll', 0),
('_bx_poll_delete', 'remove', '{evalResult}', 'if (confirm(_t(''_are you sure?''))) window.open (''{evalResult}'',''_self''); return false;', 'return isMember() ? BxDolService::call(''poll'', ''delete_action_button'', array({ID}, {PollId})) : null;', 2, 'bx_poll', 0),
('_bx_poll_share', 'share', '', 'showPopupAnyHtml (\'{BaseUri}share_popup/{PollId}\');', '', 3, 'bx_poll', 0),
('{sbs_poll_title}', 'paper-clip', '', '{sbs_poll_script}', '', 4, 'bx_poll', 0);


-- subscriptions

UPDATE `sys_sbs_types` SET `params` = 'return BxDolService::call(''poll'', ''get_subscription_params'', array($arg2, $arg3));' WHERE `unit` = 'bx_poll' AND `action` = '' AND `template` = '';
UPDATE `sys_sbs_types` SET `params` = 'return BxDolService::call(''poll'', ''get_subscription_params'', array($arg2, $arg3));' WHERE `unit` = 'bx_poll' AND `action` = 'commentPost' AND `template` = 't_sbsPollComments';

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_poll' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_poll' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsPollComments', 't_sbsPollRates');

INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsPollComments', 'New Comments To A Poll', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">poll you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to poll', 0);
 

-- sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_poll';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_poll', '_bx_polls', '0.8', 'auto', 'BxPollSiteMaps', 'modules/boonex/poll/classes/BxPollSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_poll';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_poll', '_bx_polls', 'bx_poll_data', 'poll_date', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_poll_H','_bx_poll_H1','_bx_poll_PH1','_bx_poll_action','_bx_poll_doesnt_exist','_bx_poll_error_delete','_bx_poll_info','_bx_poll_is_featured','_bx_poll_no_poll','_bx_poll_no_polls_available','_bx_poll_no_profile','_bx_poll_sbs_main','_bx_poll_sbs_votes','_bx_poll_site_poll','_bx_poll_site_polls','_bx_poll_status','_bx_poll_update');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_poll_H','_bx_poll_H1','_bx_poll_PH1','_bx_poll_action','_bx_poll_doesnt_exist','_bx_poll_error_delete','_bx_poll_info','_bx_poll_is_featured','_bx_poll_no_poll','_bx_poll_no_polls_available','_bx_poll_no_profile','_bx_poll_sbs_main','_bx_poll_sbs_votes','_bx_poll_site_poll','_bx_poll_site_polls','_bx_poll_status','_bx_poll_update');



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'poll' AND `version` = '1.0.9';

