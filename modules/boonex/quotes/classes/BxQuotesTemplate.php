<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

/*
 * Quotes module View
 */
class BxQuotesTemplate extends BxDolModuleTemplate
{
    /**
    * Constructor
    */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);

        $this->_aTemplates = array('unit', 'adm_unit');
    }

    function loadTemplates()
    {
        parent::loadTemplates();
    }

    function parseHtmlByName ($sName, $aVars, $mixedKeyWrapperHtml = NULL, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return parent::parseHtmlByName ($sName.'.html', $aVars, $mixedKeyWrapperHtml, $sCheckIn);
    }
}
