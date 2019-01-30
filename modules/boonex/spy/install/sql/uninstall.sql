
    DROP TABLE IF EXISTS `[db_prefix]handlers`;
    DROP TABLE IF EXISTS `[db_prefix]data`;
    DROP TABLE IF EXISTS `[db_prefix]friends_data`;

    SET @iHandlerId:= (SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='bx_spy_content_activity' LIMIT 1);
    DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandlerId;
    DELETE FROM `sys_alerts` WHERE `handler_id`= @iHandlerId;

    SET @iHandlerId:= (SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='bx_spy_profiles_activity' LIMIT 1);
    DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandlerId;
    DELETE FROM `sys_alerts` WHERE `handler_id`= @iHandlerId;

    DELETE FROM `sys_menu_top` WHERE `Name` IN ('Spy Personal');
    DELETE FROM `sys_menu_member` WHERE `Caption`='_bx_spy_notifications';
    DELETE FROM `sys_menu_admin` WHERE `name`='Spy' AND `title`='_bx_spy';

  	DELETE FROM `sys_page_compose` WHERE `Page`='index' AND `Desc`='Spy General';
  	DELETE FROM `sys_page_compose` WHERE `Page`='member' AND `Desc`='Spy Personal';
  	DELETE FROM `sys_page_compose` WHERE `Page`='friends' AND `Desc`='Spy Friends';

    DELETE FROM `sys_cron_jobs` WHERE `name` = 'bx_spy';

    SET @iKategId = (SELECT `id` FROM `sys_options_cats` WHERE `name` = 'Spy' LIMIT 1);
    DELETE FROM `sys_options_cats` WHERE `id` = @iKategId;
    DELETE FROM `sys_options` WHERE `kateg` = @iKategId OR `Name` = 'bx_spy_permalinks';

    DELETE FROM `sys_permalinks` WHERE `check`='bx_spy_permalinks';

    --
    -- chart
    --

    DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_spy';

    --
    -- export
    --

    DELETE FROM `sys_objects_exports` WHERE `object` = 'bx_spy';

