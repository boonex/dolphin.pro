<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('BxDolTemplate.php');

class BxDolModuleTemplate extends BxDolTemplate
{
    var $_oDb;
    var $_oConfig;
    var $_bObStarted = 0;

    /*
     * Constructor.
     */
    function BxDolModuleTemplate(&$oConfig, &$oDb, $sRootPath = BX_DIRECTORY_PATH_ROOT, $sRootUrl = BX_DOL_URL_ROOT)
    {
        parent::BxDolTemplate($sRootPath, $sRootUrl);

        $this->_oDb = &$oDb;
        $this->_oConfig = &$oConfig;

        $sClassPrefix = $oConfig->getClassPrefix();
        $sHomePath = $oConfig->getHomePath();
        $sHomeUrl = $oConfig->getHomeUrl();

        $this->addLocation($sClassPrefix, $sHomePath, $sHomeUrl);
        $this->addLocationJs($sClassPrefix, $sHomePath . 'js/', $sHomeUrl . 'js/');
    }
    function addAdminCss($mixedFiles, $bDynamic = false)
    {
        global $oAdmTemplate;

        $sLocationKey = $oAdmTemplate->addDynamicLocation($this->_oConfig->getHomePath(), $this->_oConfig->getHomeUrl());
        $mixedResult = $oAdmTemplate->addCss($mixedFiles, $bDynamic);
        $oAdmTemplate->removeLocation($sLocationKey);

        return $mixedResult;
    }
    function addAdminJs($mixedFiles, $bDynamic = false)
    {
        global $oAdmTemplate;

        $sLocationKey = $oAdmTemplate->addDynamicLocationJs($this->_oConfig->getHomePath() . 'js/', $this->_oConfig->getHomeUrl() . 'js/');
        $mixedResult = $oAdmTemplate->addJs($mixedFiles, $bDynamic);
        $oAdmTemplate->removeLocationJs($sLocationKey);

        return $mixedResult;
    }
    function pageStart ()
    {
        if (0 == $this->_bObStarted)  {
            ob_start ();
            $this->_bObStarted = 1;
        }
    }
    function pageEnd ($isGetContent = true)
    {
        if (1 == $this->_bObStarted)  {
            $sRet = '';
            if ($isGetContent)
                $sRet = ob_get_clean();
            else
                ob_end_clean();
            $this->_bObStarted = 0;
            return $sRet;
        }
    }
}
