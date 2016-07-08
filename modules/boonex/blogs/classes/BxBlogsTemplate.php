<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

class BxBlogsTemplate extends BxDolModuleTemplate
{
    /*
    * Constructor.
    */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);

        $this->_aTemplates = array('blog_unit', 'blog', 'blogpost_unit', 'admin_page', 'blogpost_unit_mobile', 'browse_unit_private_mobile');
    }

    function loadTemplates()
    {
        parent::loadTemplates();
    }

    function parseHtmlByTemplateName($sName, $aVariables, $mixedKeyWrapperHtml = null)
    {
        return $this->parseHtmlByContent($this->_aTemplates[$sName], $aVariables);
    }

    function displayAccessDenied()
    {
        return MsgBox(_t('_bx_blog_msg_access_denied'));
    }
}
