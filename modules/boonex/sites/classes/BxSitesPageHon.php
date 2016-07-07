<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxSitesPageHon extends BxDolPageView
{
    var $_oSites;
    var $_oTemplate;
    var $_oDb;

    function __construct(&$oSites)
    {
        parent::__construct('bx_sites_hon');

        $this->_oSites = &$oSites;
        $this->_oTemplate = $oSites->_oTemplate;
        $this->_oDb = $oSites->_oDb;
    }

    function getBlockCode_ViewPreviously()
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult('hon_prev_rate');
        $oSearchResult->sUnitTemplate = 'block_prev_hon';

        if ($s = $oSearchResult->displayResultBlock())
            return $s;
        else
            return MsgBox(_t('_Empty'));
    }

    function getBlockCode_ViewRate()
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult('hon_rate');
        $oSearchResult->sUnitName = 'hon';
        $oSearchResult->sUnitTemplate = 'block_hon';

        if ($s = $oSearchResult->displayResultBlock())
            return $s;
        else
            return MsgBox(_t('_Empty'));
    }
}
