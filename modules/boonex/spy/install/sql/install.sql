
    --
    -- Table structure for table `[db_prefix]handlers`
    --

    CREATE TABLE IF NOT EXISTS `[db_prefix]handlers` (
      `id` int(11) NOT NULL auto_increment,
      `alert_unit` varchar(64) NOT NULL default '',
      `alert_action` varchar(64) NOT NULL default '',
      `module_uri` varchar(64) NOT NULL default '',
      `module_class` varchar(64) NOT NULL default '',
      `module_method` varchar(64) NOT NULL default '',
      PRIMARY KEY  (`id`),
      UNIQUE `handler` (`alert_unit`, `alert_action`, `module_uri`, `module_class`, `module_method`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    
    --
    -- Table structure for table `bx_spy_data`
    --

    CREATE TABLE `[db_prefix]data` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `alert_unit` varchar(64) NOT NULL default '',
      `alert_action` varchar(64) NOT NULL default '',
      `object_id` int(11) NOT NULL default '0',
      `comment_id` int(11) NOT NULL default '0',
      `sender_id` int(11) NOT NULL default '0',
      `recipient_id` int(11) NOT NULL default '0',
      `lang_key` varchar(100) collate utf8_unicode_ci NOT NULL,
      `params` text collate utf8_unicode_ci NOT NULL,
      `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
      `type` enum('content_activity','profiles_activity') collate utf8_unicode_ci NOT NULL,
      `viewed` tinyint(1) NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `recipient_id` (`recipient_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    --
    -- Table structure for table `[db_prefix]friends_events`
    --

    CREATE TABLE IF NOT EXISTS `[db_prefix]friends_data` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `sender_id` int(11) NOT NULL,
      `friend_id` int(11) NOT NULL,
      `event_id` int(11) NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `event_id` (`event_id`),
      KEY `friend_id` (`friend_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

    --
    -- Dumping data for tables `sys_alerts_handlers` and `sys_alerts`
    --
    INSERT INTO `sys_alerts_handlers` (`name`, `class`, `file`, `eval`) VALUES
	('bx_spy_content_activity', '', '', 'BxDolService::call(\'spy\', \'response_content\', array($this));'),
	('bx_spy_profiles_activity', '', '', 'BxDolService::call(\'spy\', \'response_profiles\', array($this));');

    SET @iLastHandler = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='bx_spy_profiles_activity' LIMIT 1);
	INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
    ('profile', 'commentPost', @iLastHandler),
    ('profile', 'commentRemove', @iLastHandler),
    ('profile', 'rate', @iLastHandler),
    ('profile', 'join', @iLastHandler),
    ('profile', 'edit', @iLastHandler),
    ('profile', 'edit_status_message', @iLastHandler),
    ('profile', 'delete', @iLastHandler),
    ('friend',  'accept', @iLastHandler);

    --     
    -- Top menu ;
    -- 
    SET @iTMOrder = (SELECT MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`=118);
    INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
    (118, 'Spy Personal', '_bx_spy_notifications', 'modules/?r=spy/', @iTMOrder+1, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

    --     
    -- Member menu ;
    -- 
    INSERT INTO 
        `sys_menu_member` 
    SET
        `Caption`   = '_bx_spy_notifications', 
        `Name`      = 'Spy',
        `Icon`      = 'bell', 
        `Link`      = 'modules/?r=spy/',
        `Position`  = 'top_extra',
        `Order`     = 3,
        `PopupMenu` = 'BxDolService::call(''spy'', ''get_member_menu_spy_data''); ',
        `Description` = '_bx_spy_notifications',
        `Bubble`    = '$aRetEval = BxDolService::call(''spy'', ''get_member_menu_bubbles_data'', array({iOldCount}));';

    --
    -- Admin menu ;
    --
    SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
    INSERT INTO 
        `sys_menu_admin` 
    SET
        `name`          = 'Spy',
        `title`         = '_bx_spy', 
        `url`           = '{siteUrl}modules/?r=spy/administration/',
        `description`   = 'Spy settings',
        `icon`          = 'crosshairs',
        `parent_id`     = 2,
        `order`         = @iOrder+1;


    --
    -- Dumping data for table `sys_page_compose`
    --
    INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
    ('index', '1140px', 'Spy General', '_bx_spy', 0, 0, 'PHP', 'return BxDolService::call(''spy'', ''get_spy_block'');', 1, 28.1, 'non,memb', 0),
    ('member', '1140px', 'Spy Personal', '_bx_spy_notifications', 0, 0, 'PHP', 'return BxDolService::call(''spy'', ''get_spy_block'', array(''member.php'', $this->iMember));', 1, 28.1, 'memb', 0),
    ('friends', '1140px', 'Spy Friends', '_bx_spy_friends', 2, 2, 'PHP', 'return BxDolService::call(''spy'', ''get_spy_block_friends'', array($this->iProfileID));', 1, 28.1, 'non,memb', 0);

    --
    -- Dumping data for table `sys_cron_jobs`
    --

    INSERT INTO 
        `sys_cron_jobs` 
    (`name`, `time`, `class`, `file`)
        VALUES
    ('bx_spy', '1 */12 * * *', 'BxSpyCron', 'modules/boonex/spy/classes/BxSpyCron.php');

    --
    -- `sys_options_cats` ;
    --

    SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
    INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Spy', @iMaxOrder);
    SET @iKatId = (SELECT LAST_INSERT_ID());

    --
    -- `sys_options` ;
    --

    INSERT INTO 
        `sys_options` 
    SET 
        `Name` = 'bx_spy_keep_rows_days', 
        `VALUE` = '30', 
        `kateg` = @iKatId, 
        `desc` = 'Number of days to keep records', 
        `Type` = 'digit';

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_spy_update_time',
        `kateg` = @iKatId,
        `desc`  = 'Spy page refresh time (in milliseconds)',
        `Type`  = 'digit',
        `VALUE` = '10000',
        `check` = 'return is_numeric($arg0);';
    
    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_spy_toggle_up',
        `kateg` = @iKatId,
        `desc`  = 'Speed of block restoration (in milliseconds)',
        `Type`  = 'digit',
        `VALUE` = '1500',
        `check` = 'return is_numeric($arg0);';

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_spy_toggle_down',
        `kateg` = @iKatId,
        `desc`  = 'Speed of block minimization(in milliseconds)',
        `Type`  = 'digit',
        `VALUE` = '1500',
        `check` = 'return is_numeric($arg0);';

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_spy_per_page',
        `kateg` = @iKatId,
        `desc`  = 'Count of events for per page',
        `Type`  = 'digit',
        `VALUE` = '10',
        `check` = 'return is_numeric($arg0);';

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_spy_guest_allow',
        `kateg` = @iKatId,
        `desc`  = 'Track spy activities for guests',
        `Type`  = 'checkbox',
        `VALUE` = '';

    --
    -- Settings
    --

    INSERT INTO 
        `sys_options` 
    (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) 
        VALUES
    ('bx_spy_permalinks', 'on', 26, 'Enable friendly permalinks in spy', 'checkbox', '', '', '0', '');

    INSERT INTO
        `sys_permalinks`
    SET
        `standard`  = 'modules/?r=spy/',
        `permalink` = 'm/spy/',
        `check`     = 'bx_spy_permalinks';

    --
    -- chart
    --

    SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
    INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
    ('bx_spy', '_bx_spy_chart', 'bx_spy_data', '', 'date', '', 1, @iMaxOrderCharts);

    
    --
    -- export
    --

    SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
    INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
    ('bx_spy', '_sys_module_spy', 'BxSpyExport', 'modules/boonex/spy/classes/BxSpyExport.php', @iMaxOrderExports, 1);

