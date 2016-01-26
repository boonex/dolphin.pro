

CREATE TABLE IF NOT EXISTS `bx_wall_repost_track` (
  `event_id` int(11) NOT NULL default '0',
  `author_id` int(11) NOT NULL default '0',
  `author_nip` int(11) unsigned NOT NULL default '0',
  `reposted_id` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  UNIQUE KEY `event_id` (`event_id`),
  KEY `repost` (`reposted_id`, `author_nip`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bx_wall_voting` (
  `wall_id` bigint(8) NOT NULL default '0',
  `wall_rating_count` int(11) NOT NULL default '0',
  `wall_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `wall_id` (`wall_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bx_wall_voting_track` (
  `wall_id` bigint(8) NOT NULL default '0',
  `wall_ip` varchar(20) default NULL,
  `wall_date` datetime default NULL,
  KEY `wall_ip` (`wall_ip`,`wall_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DELETE FROM `bx_wall_handlers` WHERE `alert_unit` = 'wall_common_repost' AND `alert_action` = '';
DELETE FROM `bx_wall_handlers` WHERE `alert_unit` = 'profile' AND `alert_action` = 'comment_add';
DELETE FROM `bx_wall_handlers` WHERE `alert_unit` = 'comment' AND `alert_action` = 'add';
INSERT INTO `bx_wall_handlers`(`alert_unit`, `alert_action`, `module_uri`, `module_class`, `module_method`, `groupable`, `group_by`, `timeline`, `outline`) VALUES
('wall_common_repost', '', '', '', '', 0, '', 1, 0),
('profile', 'comment_add', '', '', '', 0, '', 1, 0),
('comment', 'add', '', '', '', 0, '', 1, 0);



DELETE FROM `sys_objects_vote` WHERE `ObjectName` = 'bx_wall';
INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`, `OverrideClassName`, `OverrideClassFile`) 
VALUES ('bx_wall', 'bx_wall_voting', 'bx_wall_voting_track', 'wall_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, '', '', '', '', '', '', 'BxWallVoting', 'modules/boonex/wall/classes/BxWallVoting.php');



DELETE FROM `sys_options` WHERE `Name` = 'wall_uploaders_hide_timeline';
SET @iCategoryId = (SELECT `kateg` FROM `sys_options` WHERE `Name` = 'wall_events_hide_outline');
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('wall_uploaders_hide_timeline', '', @iCategoryId, 'Hide uploaders from Post to Timeline block', 'select_multiple', '', '', 11, 'PHP:return BxDolService::call(\'wall\', \'get_uploaders_checklist\', array(\'timeline\'));');



DELETE `sys_acl_actions`, `sys_acl_matrix` FROM `sys_acl_actions`, `sys_acl_matrix` WHERE `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID` AND `sys_acl_actions`.`Name` IN('timeline repost');
DELETE FROM `sys_acl_actions` WHERE `Name` IN ('timeline repost');

SET @iLevelNonMember := 1;
SET @iLevelStandard := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions`(`Name`, `AdditionalParamName`) VALUES ('timeline repost', '');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
(@iLevelStandard, @iAction), 
(@iLevelPromotion, @iAction);



SET @iHandlerId = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_wall');

DELETE FROM `sys_alerts` WHERE `unit` = 'comment' AND `action` = 'add' AND `handler_id` = @iHandlerId;
DELETE FROM `sys_alerts` WHERE `unit` = 'profile' AND `action` = 'commentPost' AND `handler_id` = @iHandlerId;

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('comment', 'add', @iHandlerId);



-- delete unused language keys


DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_Comment to this comment','_Hide N comments','_Show N comments','_wall_add_music','_wall_added_music','_wall_added_photo','_wall_added_video','_wall_shared_link','_wall_wrote');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_Comment to this comment','_Hide N comments','_Show N comments','_wall_add_music','_wall_added_music','_wall_added_photo','_wall_added_video','_wall_shared_link','_wall_wrote');
        

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'wall' AND `version` = '1.2.1';

