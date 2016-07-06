<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

class BxFaceBookConnectInstaller extends BxDolInstaller
{
    function __construct(&$aConfig)
    {
        parent::__construct($aConfig);

		$this->_aActions['check_requirements'] = array(
			'title' => 'Check requirements:',
		);
    }

    function actionCheckRequirements()
    {
        $bError = version_compare(PHP_VERSION, '5.4.0') >= 0
            ? BX_DOL_INSTALLER_SUCCESS
            : BX_DOL_INSTALLER_FAILED;

        return $bError;
    }

    function actionCheckRequirementsFailed()
    {
        return '
            <div style="border:1px solid red; padding:10px;">
                <u>PHP 5.4</u> or higher is required!
            </div>';
    }
}
