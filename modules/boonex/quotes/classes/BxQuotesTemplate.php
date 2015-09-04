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
    function BxQuotesTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolModuleTemplate($oConfig, $oDb);

        $this->_aTemplates = array('unit', 'adm_unit');
    }

    function loadTemplates()
    {
        parent::loadTemplates();
    }

    function parseHtmlByName ($sName, &$aVars)
    {
        return parent::parseHtmlByName ($sName.'.html', $aVars);
    }
}
