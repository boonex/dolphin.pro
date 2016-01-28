<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

    class BxSimpleMessengerInstaller extends BxDolInstaller
    {
        function __construct( $aConfig )
        {
            parent::__construct($aConfig);
        }

        function actionCheckMemberMenu()
        {
            return getParam('ext_nav_menu_enabled') == 'on' ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
        }

        function actionCheckMemberMenuFailed()
        {
            return _t('_simple_messenger_error_member_menu');
        }
    }
