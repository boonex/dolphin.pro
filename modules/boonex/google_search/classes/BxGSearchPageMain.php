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
        $aVars = array ();
        return array($this->_oTemplate->parseHtmlByName('search_form', $aVars));
    }

    function getBlockCode_SearchResults()
    {
        $aVars = array (
            'msg' => !getParam('bx_gsearch_id') ? MsgBox(_t('_bx_gsearch_no_search_engine_id')) : '',
            'cx' => getParam('bx_gsearch_id'),
        );
        return array($this->_oTemplate->parseHtmlByName('search', $aVars));
    }
}
