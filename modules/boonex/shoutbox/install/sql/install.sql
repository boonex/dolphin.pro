
    --
    -- Table structure for table `bx_shoutbox_messages`
    --

    CREATE TABLE `[db_prefix]messages` (
      `ID` int(10) unsigned NOT NULL auto_increment,
      `HandlerID` int(11) NOT NULL,
      `OwnerID` int(11) NOT NULL,
      `Message` blob NOT NULL,
      `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `IP` int(10) unsigned NOT NULL,
      PRIMARY KEY (`ID`),
      KEY `IP` (`IP`),
      KEY `HandlerID` (`HandlerID`)
    ) ENGINE=MyISAM;

    --
    -- Table structure for table `bx_shoutbox_objects`
    --

    CREATE TABLE IF NOT EXISTS `[db_prefix]objects` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(64) NOT NULL,
      `title` varchar(255) NOT NULL,
      `table` varchar(255) NOT NULL,
      `code_allow_use` varchar(255) NOT NULL,
      `code_allow_delete` varchar(255) NOT NULL,
      `code_allow_block` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=MyISAM;

    INSERT INTO `bx_shoutbox_objects` (`name`, `title`, `table`, `code_allow_use`, `code_allow_delete`, `code_allow_block`) VALUES
    ('bx_shoutbox', '_bx_shoutbox', '[db_prefix]messages', '', '', '');

    --
    -- Dumping data for table `sys_page_compose`
    --

    INSERT INTO 
        `sys_page_compose` 
    (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`)
        VALUES
    ('index', '960px', 'Shoutbox', '_bx_shoutbox', 2, 5, 'PHP', 'BxDolService::call(''shoutbox'', ''get_shoutbox'');', 11, 50, 'non,memb', 0);

    --
    -- Dumping data for table `sys_acl_actions`
    --

    SET @iLevelNonMember := 1;
    SET @iLevelStandard  := 2;
    SET @iLevelPromotion := 3;

    INSERT INTO `sys_acl_actions` VALUES (NULL, 'shoutbox use', NULL);
    SET @iAction := LAST_INSERT_ID();

    INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
        (@iLevelNonMember, @iAction), 
        (@iLevelStandard, @iAction), 
        (@iLevelPromotion, @iAction);

    INSERT INTO `sys_acl_actions` VALUES (NULL, 'shoutbox delete messages', NULL);
    INSERT INTO `sys_acl_actions` VALUES (NULL, 'shoutbox block by ip', NULL);

    --
    -- Admin menu ;
    --
    SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
    INSERT INTO 
        `sys_menu_admin` 
    SET
        `name`          = 'Shoutbox',
        `title`         = '_bx_shoutbox', 
        `url`           = '{siteUrl}modules/?r=shoutbox/administration/',
        `description`   = 'Some shoutbox''s settings',
        `icon`          = 'comment',
        `parent_id`     = 2,
        `order`         = @iOrder+1;
    
    --
    -- `sys_options_cats` ;
    --

    SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
    INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Shoutbox', @iMaxOrder);
    SET @iKategId = (SELECT LAST_INSERT_ID());

    --
    -- Dumping data for table `sys_options`;
    --

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'shoutbox_update_time',
        `kateg` = @iKategId,
        `desc`  = 'Shoutbox update time (in milliseconds)',
        `Type`  = 'digit',
        `VALUE` = '7000',
        `check` = 'return is_numeric($arg0);',
        `order_in_kateg` = 1;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'shoutbox_allowed_messages',
        `kateg` = @iKategId,
        `desc`  = 'The number of the saved messages',
        `Type`  = 'digit',
        `VALUE` = '30',
        `check` = 'return is_numeric($arg0);',
        `order_in_kateg` = 2;
         
   INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'shoutbox_clean_oldest',
        `kateg` = @iKategId,
        `desc`  = 'Clean messages older than (sec)',
        `Type`  = 'digit',
        `VALUE` = '172800',
        `check` = 'return is_numeric($arg0);',
        `order_in_kateg` = 4;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'shoutbox_block_sec',
        `kateg` = @iKategId,
        `desc`  = 'IP blocking time (sec)',
        `Type`  = 'digit',
        `VALUE` = '86400',
        `check` = 'return is_numeric($arg0);',
        `order_in_kateg` = 5;

    --
    -- Dumping data for table `sys_cron_jobs`
    --

    INSERT INTO 
        `sys_cron_jobs` 
    (`name`, `time`, `class`, `file`)
        VALUES
    ('BxShoutBox', '*/5 * * * *', 'BxShoutBoxCron', 'modules/boonex/shoutbox/classes/BxShoutBoxCron.php');

    --
    -- chart
    --

    SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
    INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
    ('bx_shoutbox', '_bx_shoutbox_chart', 'bx_shoutbox_messages', '', 'Date', '', 1, @iMaxOrderCharts);

    INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('bx_shoutbox_profile_delete', 'BxDolService::call(''shoutbox'', ''response_profile_delete'', array($this));');
    SET @iHandler := LAST_INSERT_ID();
    INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);

    --
    -- export
    --

    SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
    INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
    ('bx_shoutbox', '_sys_module_shoutbox', 'BxShoutBoxExport', 'modules/boonex/shoutbox/classes/BxShoutBoxExport.php', @iMaxOrderExports, 1);

