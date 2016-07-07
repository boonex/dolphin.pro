<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageMain');

class BxSitesPageMain extends BxDolTwigPageMain
{
    var $_oSites;
    var $_oTemplate;
    var $_oConfig;
    var $_oDb;

    function __construct(&$oSites)
    {
        parent::__construct('bx_sites_main', $oSites);

        $this->_oSites = &$oSites;
        $this->_oTemplate = $oSites->_oTemplate;
        $this->_oConfig = $oSites->_oConfig;
        $this->_oDb = $oSites->_oDb;
    }

    function getBlockCode_ViewFeature()
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult('featuredshort');

        if ($s = $oSearchResult->displayResultBlock(true, true))
            return $s;
        else
            return '';
    }

    function getBlockCode_ViewRecent()
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult('featuredlast');

        if ($s = $oSearchResult->displayResultBlock())
            return $s;
        else
            return '';
    }

    function getBlockCode_ViewAll()
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult('home');

        if ($s = $oSearchResult->displayResultBlock(true, true)) {
            return array(
                $s,
                array(
                    _t('RSS') => array(
                        'href' => $this->_oConfig->getBaseUri() . 'browse/all?rss=1',
                        'target' => '_blank',
                        'icon' => 'rss',
                    )
                ),
                array(),
                true
            );
        } else
            return MsgBox(_t('_Empty'));
    }

}
