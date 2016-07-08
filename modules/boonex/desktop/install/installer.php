<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

class BxDskInstaller extends BxDolInstaller
{
    var $sGetDesktopUrl = "http://air.boonex.com/desktop/";
    var $sDesktopFile = "file/desktop.air";

    function __construct($aConfig)
    {
        parent::__construct($aConfig);
        $this->_aActions['get_desktop'] = array('title' => 'Getting Desktop downloadable from boonex.com:');
        $this->_aActions['remove_desktop'] = array('title' => 'Removing Desktop downloadable:');
    }

    function actionGetDesktop($bInstall = true)
    {
        global $sHomeUrl;

        $sTempFile = BX_DIRECTORY_PATH_MODULES . $this->_aConfig['home_dir'] . $this->sDesktopFile;

        $sData = $this->readUrl($this->sGetDesktopUrl . "index.php", array('url' => $sHomeUrl . 'XML.php'));
        if(empty($sData)) return BX_DOL_INSTALLER_FAILED;

        $fp = @fopen($sTempFile, "w");
        @fwrite($fp, $this->readUrl($this->sGetDesktopUrl . $sData));
        @fclose($fp);

        $this->readUrl($this->sGetDesktopUrl . "index.php", array("delete" => $sData));

        if(!file_exists($sTempFile) || filesize($sTempFile) == 0) return BX_DOL_INSTALLER_FAILED;
        return BX_DOL_INSTALLER_SUCCESS;
    }

    function actionRemoveDesktop($bInstall = true)
    {
        @unlink(BX_DIRECTORY_PATH_MODULES . $this->_aConfig['home_dir'] . $this->sDesktopFile);
        return BX_DOL_INSTALLER_SUCCESS;
    }

    function readUrl($sUrl, $aParams = array())
    {
        return bx_file_get_contents($sUrl, $aParams);
    }
}
