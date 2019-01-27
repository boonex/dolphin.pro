
    --
    -- Table structure for table `bx_facebook_accounts`
    --

    CREATE TABLE `[db_prefix]accounts` (
      `id_profile` int(10) unsigned NOT NULL,
      `fb_profile` bigint(20) NOT NULL,
      PRIMARY KEY (`id_profile`),
      KEY `fb_profile` (`fb_profile`)
    ) ENGINE=MyISAM;


	INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
    ('t_fb_connect_password_generated', 'New Password Generated', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Hello <NickName></b>, \r\n\r\n<p>\r\nYour new password - <b><NewPassword></b></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Facebook connect password generated', 0);

    --
    -- Dumping data for table `sys_objects_auths`
    --

    INSERT INTO 
        `sys_objects_auths` 
    (`Name`, `Title`, `Link`, `Icon`) 
        VALUES
    ('facebook', '_bx_facebook_auth_title', 'modules/?r=facebook_connect/login_form', 'facebook-square');

    --
    -- `sys_alerts_handlers` ;
    --

    INSERT INTO
        `sys_alerts_handlers`
    SET
        `name`  = 'bx_facebook_connect',
        `class` = 'BxFaceBookConnectAlerts',
        `file`  = 'modules/boonex/facebook_connect/classes/BxFaceBookConnectAlerts.php';

    SET @iHandlerId := (SELECT `id` FROM `sys_alerts_handlers`  WHERE `name`  =  'bx_facebook_connect');

    --
    -- `sys_alerts` ;
    --

    INSERT INTO
        `sys_alerts`
    SET
        `unit`       = 'profile',
        `action`     = 'logout',
        `handler_id` = @iHandlerId;

    INSERT INTO
        `sys_alerts`
    SET
        `unit`       = 'profile',
        `action`     = 'join',
        `handler_id` = @iHandlerId;

    INSERT INTO
        `sys_alerts`
    SET
        `unit`       = 'profile',
        `action`     = 'delete',
        `handler_id` = @iHandlerId;

	INSERT INTO
        `sys_alerts`
    SET
        `unit`       = 'system',
        `action`     = 'join_after_payment',
        `handler_id` = @iHandlerId;

    --
    -- need for compatibility with old style login, will need remove it in a feature version
    --

    ALTER TABLE `Profiles` ADD `FacebookProfile` VARCHAR(32) NOT NULL;
	ALTER TABLE `Profiles` ADD INDEX (`FacebookProfile`) ;

   	--
    -- `sys_options_cats` ;
    --

    SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
    INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Facebook connect', @iMaxOrder);
    SET @iKategId = (SELECT LAST_INSERT_ID());

    --
    -- Dumping data for table `sys_options`;
    --

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_facebook_connect_api_key',
        `kateg` = @iKategId,
        `desc`  = 'Facebook App ID',
        `Type`  = 'digit',
        `VALUE` = '',
        `order_in_kateg` = 1;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_facebook_connect_secret',
        `kateg` = @iKategId,
        `desc`  = 'Facebook App Secret',
        `Type`  = 'digit',
        `VALUE` = '',
        `order_in_kateg` = 2;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_facebook_connect_redirect_page',
        `kateg` = @iKategId,
        `desc`  = 'Redirect page after first sign in',
        `Type`  = 'select',
        `VALUE` = 'join',
        `AvailableValues` = 'join,pedit,avatar,member,index',
        `order_in_kateg` = 3;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_facebook_connect_auto_friends',
        `kateg` = @iKategId,
        `desc`  = 'Auto-friend members if they are already friends on Facebook',
        `Type`  = 'checkbox',
        `VALUE` = 'on',
        `order_in_kateg` = 4;

    INSERT INTO 
        `sys_options` 
    SET
        `Name` = 'bx_facebook_connect_extended_info',
        `kateg` = @iKategId,
        `desc`  = 'Fetch extended profile info (facebook app review is required)',
        `Type`  = 'checkbox',
        `VALUE` = '',
        `order_in_kateg` = 5;

    -- 
    -- `sys_menu_admin`;
    --
    SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
    INSERT INTO 
        `sys_menu_admin` 
    SET
        `name`           = 'Facebook connect',
        `title`          = '_bx_facebook', 
        `url`            = '{siteUrl}modules/?r=facebook_connect/administration/', 
        `description`    = 'Managing the \'facebook connect\' settings', 
        `icon`           = 'facebook',
        `parent_id`      = 2,
        `order`          = @iOrder+1;

    --
    -- permalink
    --

    INSERT INTO 
        `sys_permalinks` 
    SET
        `standard`  = 'modules/?r=facebook_connect/', 
        `permalink` = 'm/facebook_connect/', 
        `check`     = 'bx_facebook_connect_permalinks';
        
    --
    -- settings
    --

    INSERT INTO 
        `sys_options` 
    (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) 
        VALUES
    ('bx_facebook_connect_permalinks', 'on', 26, 'Enable friendly permalinks in facebook connect', 'checkbox', '', '', '0', '');

    UPDATE `sys_profile_fields` SET `Max` = 500 WHERE `Name` = 'NickName' LIMIT 1;

    --
    -- chart
    --
    SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
    INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `column_date`, `column_count`, `type`, `options`, `query`, `active`, `order`) VALUES
    ('bx_facebook', '_bx_facebook_chart', '', '', 'DateReg', 0, 1, '', '', 'SELECT {field_date_formatted} AS `period`, COUNT(*) AS {object} FROM `Profiles` INNER JOIN `bx_facebook_accounts` ON (`id_profile` = `ID`) WHERE {field_date} >= ''{from}'' AND {field_date} <= ''{to}'' GROUP BY `period` ORDER BY {field_date} ASC', 1, @iMaxOrderCharts);

    --
    -- export
    --
    SET @iMaxOrderExports = (SELECT MAX(`order`)+1 FROM `sys_objects_exports`);
    INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
    ('bx_facebook', '_sys_module_facebook_connect', 'BxFaceBookConnectExport', 'modules/boonex/facebook_connect/classes/BxFaceBookConnectExport.php', @iMaxOrderExports, 1);

