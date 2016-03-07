<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

class BxCRSSTemplate extends BxDolModuleTemplate
{
    /*
    * Constructor.
    */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);

        $this->_aTemplates = array('crss_unit', 'view', 'member_rss_list_loaded');
    }

    function loadTemplates()
    {
        parent::loadTemplates();
    }

    function parseHtmlByTemplateName($sName, $aVariables, $mixedKeyWrapperHtml = null)
    {
        return $this->parseHtmlByContent($this->_aTemplates[$sName], $aVariables);
    }
}
