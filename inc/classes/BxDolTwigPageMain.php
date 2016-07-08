<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

/**
 * Base module homepage class for modules like events/groups/store
 */
class BxDolTwigPageMain extends BxDolPageView
{
    var $oMain;
    var $oTemplate;
    var $oConfig;
    var $oDb;
    var $sUrlStart;
    var $sSearchResultClassName;
    var $sFilterName;

    function __construct($sName, &$oMain)
    {
        $this->oMain = &$oMain;
        $this->oTemplate = $oMain->_oTemplate;
        $this->oConfig = $oMain->_oConfig;
        $this->oDb = $oMain->_oDb;
        $this->sUrlStart = BX_DOL_URL_ROOT . $this->oMain->_oConfig->getBaseUri();
        $this->sUrlStart .= (false === strpos($this->sUrlStart, '?') ? '?' : '&');
        parent::__construct($sName);
    }

    function ajaxBrowse($sMode, $iPerPage, $aMenu = array(), $sValue = '', $isDisableRss = false, $isPublicOnly = true)
    {
        bx_import ('SearchResult', $this->oMain->_aModule);
        $sClassName = $this->sSearchResultClassName;
        $o = new $sClassName($sMode, $sValue);
        $o->aCurrent['paginate']['perPage'] = $iPerPage;
        $o->setPublicUnitsOnly($isPublicOnly);

        if (!$aMenu)
            $aMenu = ($isDisableRss ? '' : array(_t('_RSS') => array('href' => $o->aCurrent['rss']['link'] . (false === strpos($o->aCurrent['rss']['link'], '?') ? '?' : '&') . 'rss=1', 'icon' => 'rss')));

        if ($o->isError)
            return array(MsgBox(_t('_Error Occured')), $aMenu);

        if (!($s = $o->displayResultBlock()))
            return $isPublicOnly ? array(MsgBox(_t('_Empty')), $aMenu) : '';

        $sFilter = (false !== bx_get($this->sFilterName)) ? $this->sFilterName . '=' . bx_get($this->sFilterName) . '&' : '';
        $oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'count' => $o->aCurrent['paginate']['totalNum'],
            'per_page' => $o->aCurrent['paginate']['perPage'],
            'page' => $o->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $this->sUrlStart . $sFilter . 'page={page}&per_page={per_page}\');',
        ));
        $sAjaxPaginate = $oPaginate->getSimplePaginate($this->oConfig->getBaseUri() . $o->sBrowseUrl);

        return array(
            $s,
            $aMenu,
            $sAjaxPaginate,
            '');
    }

    function getBlockCode_Calendar($iBlockID, $sContent)
    {
        $aDateParams = array(0, 0);
        $sDate = bx_get('date');
        if ($sDate)
            $aDateParams = explode('/', $sDate);

        bx_import ('Calendar', $this->oMain->_aModule);
        $oCalendar = bx_instance ($this->oMain->_aModule['class_prefix'] . 'Calendar', array ((int)$aDateParams[0], (int)$aDateParams[1], $this->oDb, $this->oConfig, $this->oTemplate));

        $oCalendar->setBlockId($iBlockID);
        $oCalendar->setDynamicUrl($this->oConfig->getBaseUri() . 'home/');

        return $oCalendar->display(true);
    }

    function getBlockCode_Categories($iBlockID, $sContent)
    {
        bx_import('BxTemplCategoriesModule');
        $aParam = array('type' => $this->oMain->_sPrefix);
        $oCateg = new BxTemplCategoriesModule($aParam, _t('_categ_users'), BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'categories');
        return $oCateg->getBlockCode_Common($iBlockId, true);
    }

    function getBlockCode_Tags($iBlockID, $sContent)
    {
        bx_import('BxTemplTagsModule');
        $aParam = array('type' => $this->oMain->_sPrefix, 'orderby' => 'popular');
        $oTags = new BxTemplTagsModule($aParam, '', BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'tags');
        $aResult = $oTags->getBlockCode_All($iBlockId);
        return $aResult[0];
    }

}
