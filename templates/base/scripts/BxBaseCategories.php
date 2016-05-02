<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolCategories.php');

class BxBaseCategories extends BxDolCategories
{
    var $_sCategTmplName;
    var $_sCategTmplContent;
    var $_aOrderValues;

    function __construct ()
    {
        parent::__construct();

        $this->_sCategTmplName = 'view_categ.html';
        $this->_sCategTmplContent = '';
        $this->_aOrderValues = array('none' => _t('_categ_order_none'), 'popular' => _t('_categ_order_popular'), 'recent' => _t('_categ_order_recent'));
    }

    function getCategoriesView ($aTotalCategories, $sHrefTempl, $iColumns)
    {
        global $oSysTemplate;

        if (empty($aTotalCategories))
            return MsgBox(_t( '_Empty' ));

        if (!$iColumns)
            $iColumns = 1;

        $iCount = count($aTotalCategories);
        $iRowCount = floor($iCount / $iColumns) + (($iCount % $iColumns) ? 1 : 0);
        $iWidthPr = floor(100 / $iColumns);
        $i = 0;
        $sCode = '<div class="categories_wrapper bx-def-bc-margin bx-def-font-large">';

        foreach( $aTotalCategories as $sCategory => $iCatCount ) {
            if (!($i % $iRowCount)) {
                if ($i)
                    $sCode .= '</div>';
                $sCode .= '<div class="categories_col" style="width: ' . $iWidthPr . '%">';
            }

            $aUnit['catHref'] = str_replace( '{tag}', rawurlencode(title2uri($sCategory)), $sHrefTempl);
            $aUnit['category'] = htmlspecialchars_adv($sCategory );
            $aUnit['count'] = $iCatCount;

            if ($this->_sCategTmplContent)
                $sCode .= $oSysTemplate->parseHtmlByContent($this->_sCategTmplContent, $aUnit);
            else
                $sCode .= $oSysTemplate->parseHtmlByName($this->_sCategTmplName, $aUnit);

            $i++;
        }

        $sCode .= '</div></div>';

        return $sCode;
    }

    function getCategTopMenu ($aParam, $sAction = '')
    {
        $aTopMenu = array();
        $aParamTmp = $aParam;

        foreach ($this->aTagObjects as $sKey => $aTagUnit) {
            $sName = _t($aTagUnit['LangKey']);
            $sHref = bx_html_attribute($_SERVER['PHP_SELF']) . "?tags_mode=$sKey" . ($sAction ? '&action=' . $sAction : '');

            if (isset($aParam['filter']) && $aParam['filter']) {
                $aParamTmp['type'] = $sKey;
                $sName .= '(' . $this->getTagsCount($aParamTmp) . ')';
                $sHref .= '&filter=' . $aParam['filter'];
            }

            if (isset($aParam['date']) && $aParam['date'])
                $sHref .= '&year=' . $aParam['date']['year'] .
                    '&month=' . $aParam['date']['month'] .
                    '&day=' . $aParam['date']['day'];

            $aTopMenu[$sName] = array('href' => $sHref, 'dynamic' => true, 'active' => ( $sKey == $aParam['type']));
        }

        return $aTopMenu;
    }

    function getCategTopMenuHtml($aParam, $iBoxId, $sAction = '')
    {
        $aItems = array();

        $aTopMenu = $this->getCategTopMenu($aParam, $sAction);
        foreach ($aTopMenu as $sName => $aItem) {
            $aItems[$sName] = array(
                'dynamic' => true,
                'active' => $aItem['active'],
                'href' => $aItem['href']
            );
        }
        return BxDolPageView::getBlockCaptionItemCode($iBoxId, $aItems);
    }

    function display($aParam, $iBoxId, $sAction = '', $bOrderPanel = false, $iColumns, $sUrl = '')
    {
        if (!isset($aParam['type']) || !$aParam['type'])
            return MsgBox(_t( '_Empty' ));

        $sPaginate = '';
        $sCode = '';
        $sPageUrl = $sUrl ? $sUrl : bx_html_attribute($_SERVER['PHP_SELF']);
        $sPageUrl .= '?tags_mode=' . $aParam['type'];

        if (!isset($aParam['orderby']) && isset($_REQUEST['orderby']) && $_REQUEST['orderby'] && isset($this->_aOrderValues[$_REQUEST['orderby']]))
            $aParam['orderby'] = $_REQUEST['orderby'];

        if (isset($aParam['filter']) && $aParam['filter'])
            $sPageUrl .= '&filter=' . $aParam['filter'];
        if (isset($aParam['orderby']) && $aParam['orderby'])
            $sPageUrl .= '&orderby=' . $aParam['orderby'];
        if ($sAction)
            $sPageUrl .= '&action=' . $sAction;
        if (isset($aParam['date']) && $aParam['date']) {
            $sPageUrl .= '&year=' . $aParam['date']['year'] .
                '&month=' . $aParam['date']['month'] .
                '&day=' . $aParam['date']['day'];
        }

        if (isset($aParam['pagination']) && $aParam['pagination']) {
            bx_import('BxDolPaginate');

            $aPaginate = array(
                'page_url' => $sPageUrl . '&page={page}&per_page={per_page}',
                'info' => true,
                'page_links' => true,
                'on_change_page' => "!loadDynamicBlock($iBoxId, this.href)"
            );

            $aParam['limit'] = $aPaginate['per_page'] = $aParam['pagination'];
            $aPaginate['count'] = $this->getTagsCount($aParam);
            $aPaginate['page'] = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
            $aParam['start'] = $aParam['limit'] * ($aPaginate['page'] - 1);
            if ($aParam['start'] <=0)
                $aParam['start'] = 0;

            $oPaginate = new BxDolPaginate($aPaginate);
            $sPaginate = '<div class="clear_both"></div>'.$oPaginate->getPaginate();
        }

        $sHrefTmpl = $this->getHrefWithType($aParam['type']);
        $aTotalTags = $this->getTagList($aParam);

        if ($aTotalTags) {
            if ($bOrderPanel)
                $sCode .= $this->_getTopBox($aParam, $iBoxId, $sPageUrl);
            $sCode .= $this->getCategoriesView($aTotalTags, $sHrefTmpl, $iColumns) . $sPaginate;
        } else
            return MsgBox(_t( '_Empty' ));

        return $sCode;
    }

    function setTemplateName($sTmplName)
    {
        $this->_sCategTmplName = $sTmplName;
    }

    function setTemplateContent($sTmplContent)
    {
        $this->_sCategTmplContent = $sTmplContent;
    }

    function _getTopBox($aParam, $iBoxId, $sPageUrl)
    {
        global $oSysTemplate;

        $aTmplVars = array(
            'block_id' => $iBoxId,
            'page_url' => $sPageUrl,
            'bx_repeat:options' => array()
        );

        foreach ($this->_aOrderValues as $sKey => $sVal)
            $aTmplVars['bx_repeat:options'][] = array(
                'key' => $sKey,
                'bx_if:show_selected' => array(
                    'condition' => $sKey == $aParam['orderby'],
                    'content' => array()
                ),
                'value' => $sVal
            );
        $sTopControls = $oSysTemplate->parseHtmlByName('categories_top_controls.html', $aTmplVars);

        return $oSysTemplate->parseHtmlByName('designbox_top_controls.html', array('top_controls' => $sTopControls));
    }
}
