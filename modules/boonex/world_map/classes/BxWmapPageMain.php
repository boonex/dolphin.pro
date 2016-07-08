<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxWmapPageMain extends BxDolPageView
{
    var $_oMain;
    var $_oTemplate;
    var $_oConfig;
    var $_oDb;

    function __construct(&$oModule)
    {
        $this->_oMain = &$oModule;
        $this->_oTemplate = $oModule->_oTemplate;
        $this->_oConfig = $oModule->_oConfig;
        $this->_oDb = $oModule->_oDb;
        parent::__construct('bx_wmap');
    }

    function getBlockCode_Map()
    {
        $fLat = false;
        $fLng = false;
        $iZoom = false;
        $sParts = '';
        return $this->_oMain->serviceSeparatePageBlock ($fLat, $fLng, $iZoom, $sParts);
    }
}
