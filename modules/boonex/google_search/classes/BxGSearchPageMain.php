<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxGSearchPageMain extends BxDolPageView
{
    var $_oTemplate;
    var $_oConfig;

    function __construct(&$oModule)
    {
        $this->_oTemplate = $oModule->_oTemplate;
        $this->_oConfig = $oModule->_oConfig;
        parent::__construct('bx_gsearch');
    }

    function getBlockCode_SearchForm()
    {
        $aVars = array (
            'suffix' => 'adv',
            'empty' => MsgBox(_t('_Empty')),
        );
        return array($this->_oTemplate->parseHtmlByName('search_form', $aVars));
    }

    function getBlockCode_SearchResults()
    {
        $sProto = bx_proto();
        $this->_oTemplate->addJs ($sProto . '://www.google.com/jsapi');

        $a = parse_url ($GLOBALS['site']['url']);
        $aVars = array (
            'is_image_search' => 'on' == getParam('bx_gsearch_separate_images') ? 1 : 0,
            'is_tabbed_search' => 'on' == getParam('bx_gsearch_separate_tabbed') ? 1 : 0,
            'domain' => $a['host'],
            'keyword' => str_replace('"', '\\"', stripslashes($_GET['keyword'])),
            'suffix' => 'adv',
            'separate_search_form' => 1,
        );
        return array($this->_oTemplate->parseHtmlByName('search', $aVars));
    }
}
