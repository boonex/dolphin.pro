<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

function bx_gsearch_import ($sClassPostfix, $aModuleOverwright = array())
{
    global $aModule;
    $a = $aModuleOverwright ? $aModuleOverwright : $aModule;
    if (!$a || $a['uri'] != 'google_search') {
        $oMain = BxDolModule::getInstance('BxGSearchModule');
        $a = $oMain->_aModule;
    }
    bx_import ($sClassPostfix, $a) ;
}

bx_import('BxDolModule');
bx_import('BxDolPaginate');
bx_import('BxDolAlerts');

/**
 * Google Site Search module by BoonEx
 *
 * This module allow user to search the site using Google Site Search
 *
 *
 *
 * Profile's Wall:
 * no wall events
 *
 *
 *
 * Spy:
 * no spy events
 *
 *
 *
 * Memberships/ACL:
 * no acl's  - everybody can use it
 *
 *
 *
 * Service methods:
 *
 * Get search control html
 * @see BxGSearchModule::serviceGetSearchControl
 * BxDolService::call('google_search', 'get_search_control', array());
 *
 *
 *
 * Alerts:
 * no alerts
 *
 */
class BxGSearchModule extends BxDolModule
{
    var $_iProfileId;

    function BxGSearchModule(&$aModule)
    {
        parent::BxDolModule($aModule);
        $GLOBALS['aModule'] = $aModule;
        $this->_iProfileId = getLoggedId();
        $GLOBALS['oBxGSearchModule'] = &$this;
    }

    function actionHome ()
    {
        bx_gsearch_import ('PageMain');
        $oPage = new BxGSearchPageMain ($this);
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
        $this->_oTemplate->addJs ('http://www.google.com/jsapi');
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->pageCode(_t('_bx_gsearch'), false, false);
    }

    function actionAdministration ()
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $iId = $this->_oDb->getSettingsCategory('Google Search');
        if(empty($iId)) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        $this->_oTemplate->pageStart();

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        echo $this->_oTemplate->adminBlock ($sResult, _t('_bx_gsearch_administration'), false, false, 11);

        $this->_oTemplate->addCssAdmin ('main.css');
        $this->_oTemplate->addCssAdmin ('forms_adv.css');
        $this->_oTemplate->pageCodeAdmin (_t('_bx_gsearch_administration'));
    }

    // ================================== service actions

    /**
     * Get search control html.
     * @return html with google search control
     */
    function serviceGetSearchControl ()
    {
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addJs ('http://www.google.com/jsapi');
        $a = parse_url ($GLOBALS['site']['url']);
        $aVars = array (
            'is_image_search' => 'on' == getParam('bx_gsearch_block_images') ? 1 : 0,
            'is_tabbed_search' => 'on' == getParam('bx_gsearch_block_tabbed') ? 1 : 0,
            'domain' => $a['host'],
            'keyword' => '',
            'suffix' => 'simple',
            'separate_search_form' => 0,
        );
        return array($this->_oTemplate->parseHtmlByName('search', $aVars));
    }

    // ================================== other functions

    function isAdmin ()
    {
        return $GLOBALS['logged']['admin'] || $GLOBALS['logged']['moderator'];
    }
}
