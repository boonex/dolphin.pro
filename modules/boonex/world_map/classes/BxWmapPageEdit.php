<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxWmapPageEdit extends BxDolPageView
{
    var $_oMain;
    var $_oTemplate;
    var $_oConfig;
    var $_oDb;
    var $_sUrlStart;
    var $_aLocation;

    function __construct(&$oModule, $aLocation)
    {
        $this->_oMain = &$oModule;
        $this->_oTemplate = $oModule->_oTemplate;
        $this->_oConfig = $oModule->_oConfig;
        $this->_oDb = $oModule->_oDb;
        $this->_aLocation = $aLocation;
        parent::__construct('bx_wmap_edit');
    }

    function getBlockCode_MapEdit()
    {
        return $this->_oMain->serviceEditLocation ($this->_aLocation['part'], $this->_aLocation['id']);
    }
}
