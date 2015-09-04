<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

    class BxFaceBookConnectInstaller extends BxDolInstaller
    {
        function BxFaceBookConnectInstaller(&$aConfig)
        {
            parent::BxDolInstaller($aConfig);
        }

        function actionCheckRequirements()
        {
            $bError = (int) phpversion() >= 5
                ? BX_DOL_INSTALLER_SUCCESS
                : BX_DOL_INSTALLER_FAILED;

            return $bError;
        }

        function actionCheckRequirementsFailed()
        {
            return '
            <div style="border:1px solid red; padding:10px;">
                You need <u>PHP 5</u> or higher!
            </div>';
        }
    }
