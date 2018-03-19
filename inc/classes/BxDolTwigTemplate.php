<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolModuleTemplate');

/**
 * Base template class for modules like events/groups/store
 */
class BxDolTwigTemplate extends BxDolModuleTemplate
{
    var $_iPageIndex = 13;
    var $_oMain = null;

    function __construct(&$oConfig, &$oDb, $sRootPath = BX_DIRECTORY_PATH_ROOT, $sRootUrl = BX_DOL_URL_ROOT)
    {
        parent::__construct($oConfig, $oDb, $sRootPath, $sRootUrl);

        if (isset($GLOBALS['oAdmTemplate']))
            $GLOBALS['oAdmTemplate']->addDynamicLocation($this->_oConfig->getHomePath(), $this->_oConfig->getHomeUrl());
    }

    // ======================= common functions

    function addCssAdmin ($sName)
    {
        if (empty($GLOBALS['oAdmTemplate']))
            return;
        $GLOBALS['oAdmTemplate']->addCss ($sName);
    }

    function addJsAdmin ($sName)
    {
        if (empty($GLOBALS['oAdmTemplate']))
            return;
        $GLOBALS['oAdmTemplate']->addJs ($sName);
    }

    function parseHtmlByName ($sName, $aVariables, $mixedKeyWrapperHtml = null, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return parent::parseHtmlByName ($sName . (strlen($sName) < 6 || substr_compare($sName, '.html', -5, 5) !== 0 ? '.html' : ''), $aVariables);
    }

    // ======================= page generation functions

    function pageCode ($sTitle, $isDesignBox = true, $isWrap = true)
    {
        global $_page;
        global $_page_cont;

        $_page['name_index'] = $isDesignBox ? 0 : $this->_iPageIndex;

        $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
        $_page['header_text'] = $sTitle;

        $_page_cont[$_page['name_index']]['page_main_code'] = $this->pageEnd();
        if ($isWrap) {
            $aVars = array (
                'content' => $_page_cont[$_page['name_index']]['page_main_code'],
            );
            $_page_cont[$_page['name_index']]['page_main_code'] = $this->parseHtmlByName('default_padding', $aVars);
        }

        $GLOBALS['oSysTemplate']->addDynamicLocation($this->_oConfig->getHomePath(), $this->_oConfig->getHomeUrl());
        PageCode($GLOBALS['oSysTemplate']);
    }

    function adminBlock ($sContent, $sTitle, $aMenu = array(), $sBottomItems = '', $iIndex = 1)
    {
        return DesignBoxAdmin($sTitle, $sContent, $aMenu, $sBottomItems, $iIndex);
    }

    function pageCodeAdmin ($sTitle)
    {
        global $_page;
        global $_page_cont;

        $_page['name_index'] = 9;

        $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
        $_page['header_text'] = $sTitle;

        $_page_cont[$_page['name_index']]['page_main_code'] = $this->pageEnd();

        PageCodeAdmin();
    }

    // ======================= tags/cat parsing functions

    function parseTags ($s)
    {
        return $this->_parseAnything ($s, ',', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/tag/');
    }

    function parseCategories ($s)
    {
        bx_import ('BxDolCategories');
        return $this->_parseAnything ($s, CATEGORIES_DIVIDER, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/category/');
    }

    // ======================= display standard pages functions

    function displayAccessDenied ()
    {
        $this->pageStart();
        echo MsgBox(_t('_Access denied'));
        $this->pageCode (_t('_Access denied'), true, false);
    }

    function displayNoData ()
    {
        $this->pageStart();
        echo MsgBox(_t('_Empty'));
        $this->pageCode (_t('_Empty'), true, false);
    }

    function displayErrorOccured ()
    {
        $this->pageStart();
        echo MsgBox(_t('_Error Occured'));
        $this->pageCode (_t('_Error Occured'), true, false);
    }

    function displayPageNotFound ()
    {
        header("HTTP/1.0 404 Not Found");
        $this->pageStart();
        echo MsgBox(_t('_sys_request_page_not_found_cpt'));
        $this->pageCode (_t('_sys_request_page_not_found_cpt'), true, false);
    }

    function displayMsg ($s, $isTranslate = false)
    {
        $this->pageStart();
        echo MsgBox($isTranslate ? _t($s) : $s);
        $this->pageCode ($isTranslate ? _t($s) : $s, true);
    }

}
