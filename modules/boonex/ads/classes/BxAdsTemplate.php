<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolModuleTemplate');

class BxAdsTemplate extends BxDolModuleTemplate
{
    /*
    * Constructor.
    */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);

        $this->_aTemplates = array('unit_ads', 'category', 'filter_form', 'ad_of_day', 'wall_outline_extra_info');
    }

    function loadTemplates()
    {
        parent::loadTemplates();
    }

    function parseHtmlByTemplateName($sName, $aVariables, $mixedKeyWrapperHtml = null)
    {
        return $this->parseHtmlByContent($this->_aTemplates[$sName], $aVariables);
    }

    function displayAccessDenied ()
    {
        return MsgBox(_t('_bx_ads_msg_access_denied'));
    }

    function pageCode($aPage = array(), $aPageCont = array(), $aCss = array(), $aJs = array(), $bAdminMode = false, $isSubActions = true)
    {
        if (!empty($aPage)) {
            foreach ($aPage as $sKey => $sValue)
                $GLOBALS['_page'][$sKey] = $sValue;
        }
        if (!empty($aPageCont)) {
            foreach ($aPageCont as $sKey => $sValue)
                $GLOBALS['_page_cont'][$aPage['name_index']][$sKey] = $sValue;
        }
        if (!empty($aCss))
            $this->addCss($aCss);
        if (!empty($aJs))
            $this->addJs($aJs);

        if (!$bAdminMode)
            PageCode($this);
        else
            PageCodeAdmin();
    }

}
