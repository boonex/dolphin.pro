<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');

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
    var $_sProto = 'http';

    function __construct(&$aModule)
    {
        parent::__construct($aModule);
        $GLOBALS['aModule'] = $aModule;
        $this->_iProfileId = getLoggedId();
        $this->_sProto = bx_proto();
    }

    function actionHome ()
    {
        bx_import ('PageMain', $this->_aModule);
        $oPage = new BxGSearchPageMain ($this);
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
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

        echo $this->_oTemplate->adminBlock (_t('_bx_gsearch_help_text', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri()), _t('_bx_gsearch_help_title'), false, false, 11);

        echo $this->_oTemplate->adminBlock ($sResult, _t('_bx_gsearch_administration'), false, false, 11);

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
        $aVars = array (
            'msg' => !getParam('bx_gsearch_id') ? MsgBox(_t('_bx_gsearch_no_search_engine_id')) : '',
            'cx' => getParam('bx_gsearch_id'),
        );
        return array($this->_oTemplate->parseHtmlByName('search', $aVars));
    }

    // ================================== other functions

    function isAdmin ()
    {
        return $GLOBALS['logged']['admin'] || $GLOBALS['logged']['moderator'];
    }
}
