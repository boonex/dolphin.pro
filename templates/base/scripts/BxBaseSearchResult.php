<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolSearch.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPaginate.php');

class BxBaseSearchResult extends BxDolSearchResult
{
    var $aPermalinks;
    var $aConstants;

    function __construct()
    {
        parent::__construct();
    }

    function isPermalinkEnabled()
    {
       return isset($this->_isPermalinkEnabled) ? $this->_isPermalinkEnabled : ($this->_isPermalinkEnabled = (getParam($this->aPermalinks['param']) == 'on'));
    }

       function getCurrentUrl ($sType, $iId, $sUri, $aOwner = '')
       {
           $sLink = $this->aConstants['linksTempl'][$sType];
        $sLink = str_replace('{id}', $iId, $sLink);
        $sLink = str_replace('{uri}', $sUri, $sLink);
        if (is_array($aOwner) && !empty($aOwner)) {
            $sLink = str_replace('{ownerName}', $aOwner['ownerName'], $sLink);
            $sLink = str_replace('{ownerId}', $aOwner['ownerId'], $sLink);
        }
        return $GLOBALS['site']['url'] . $sLink;
    }

    function displayResultBlock ()
    {
        $sCode = '';
        $aData = $this->getSearchData();
        if ($this->aCurrent['paginate']['totalNum'] > 0) {
            $sCode .= $this->addCustomParts();
            foreach ($aData as $aValue) {
                $sCode .= $this->displaySearchUnit($aValue);
            }
            $sCode = '<div class="result_block">' . $sCode . '<div class="clear_both"></div></div>';
        }
        return $sCode;
    }

    function displaySearchBox ($sCode, $sPaginate = '', $bAdminBox = false)
    {
        $sMenu = '';
        if (isset($this->aCurrent['rss']) && $this->aCurrent['rss']['link']) {
            bx_import('BxDolPageView');
            $sMenu = BxDolPageView::getBlockCaptionItemCode(time(), array(_t('RSS') => array('href' => $this->aCurrent['rss']['link'] . (false === strpos($this->aCurrent['rss']['link'], '?') ? '?' : '&') . 'rss=1', 'icon' => 'rss')));
        }
        $sTitle = _t($this->aCurrent['title']);
        if (!$bAdminBox) {
            $sCode = DesignBoxContent($sTitle, $sCode. $sPaginate, 1, $sMenu);
        } else {
            $sCode = DesignBoxAdmin($sTitle, $sCode, '', $sPaginate, 1);
        }
        if (!isset($_GET['searchMode']))
            $sCode = '<div id="page_block_'.$this->id.'">' . $sCode . '<div class="clear_both"></div></div>';
        return $sCode;
    }

    function _transformData ($aUnit, $sTempl, $sCssHeader = '')
    {
        foreach ($aUnit as $sKey => $sValue)
            $sTempl = str_replace('{'.$sKey.'}', $sValue, $sTempl);

        $sCssHeader = strlen($sCssHeader) > 0 ?  $sCssHeader : 'text_Unit';
        $sTempl =  str_replace('{unitClass}', $sCssHeader, $sTempl);
        return $sTempl;
    }

    public static function showAdminActionsPanel($sWrapperId, $aButtons, $sCheckboxName = 'entry', $bSelectAll = true, $bSelectAllChecked = false, $sCustomHtml = '')
    {
        $aBtns = array();
        foreach ($aButtons as $k => $v) {
            if(is_array($v)) {
                $aBtns[] = $v;
                continue;
            }
            $aBtns[] = array(
                'type' => 'submit',
                'name' => $k,
                'value' => '_' == $v[0] ? _t($v) : $v,
                'onclick' => '',
            );
        }

        $aUnit = array(
            'bx_if:selectAll' => array(
                'condition' => $bSelectAll,
                'content' => array(
                    'wrapperId' => $sWrapperId,
                    'checkboxName' => $sCheckboxName,
                    'checked' => ($bSelectAll && $bSelectAllChecked ? 'checked="checked"' : '')
                )
            ),
            'bx_if:actionButtons' => array(
                'condition' => !empty($aBtns),
                'content' => array(
                    'class' => $sCustomHtml ? 'admin-actions-buttons-with-custom-html' : '',
                    'bx_repeat:buttons' => $aBtns,
                )
            ),
            'bx_if:customHTML' => array(
                'condition' => $sCustomHtml,
                'content' => array(
                    'custom_HTML' => $sCustomHtml,
                )
            )
        );

        return $GLOBALS['oSysTemplate']->parseHtmlByName('adminActionsPanel.html', $aUnit, array('{','}'));
    }

    public static function showAdminFilterPanel($sFilterValue, $sInputId = 'filter_input_id', $sCheckboxId = 'filter_checkbox_id', $sFilterName = 'filter', $sOnApply = '')
    {
        $sFilter = _t('_sys_admin_filter');
        $sApply = _t('_sys_admin_apply');

        $sFilterValue = bx_html_attribute($sFilterValue);
        $isChecked = $sFilterValue ? ' checked="checked" ' : '';

        if(empty($sOnApply))
            $sOnApply = "on_filter_apply(this, '" . $sInputId . "', '" . $sFilterName . "')";

        $sContent = <<<EOF
                <table>
                    <tr>
                        <td>{$sFilter}</td>
                        <td>
                            <div class="input_wrapper input_wrapper_text bx-def-round-corners-with-border">
                                <input type="text" id="{$sInputId}" value="{$sFilterValue}" class="form_input_text bx-def-font" onkeypress="return on_filter_key_up(event, '{$sCheckboxId}')" />
                            </div>
                        </td>
                        <td><input type="checkbox" id="{$sCheckboxId}" $isChecked onclick="{$sOnApply}" /></td>
                        <td><label for="{$sCheckboxId}">{$sApply}</label></td>
                    </tr>
                </table>
EOF;

        return $GLOBALS['oSysTemplate']->parseHtmlByName('designbox_top_controls.html', array(
            'top_controls' => $sContent
        ));
    }

    function showPagination($aParams = array())
    {
        $bChangePage = !isset($aParams['change_page']) || $aParams['change_page'] === true;
        $bPageReload = !isset($aParams['page_reload']) || $aParams['page_reload'] === true;

        $sPageLink = $this->getCurrentUrl('browseAll', 0, '');
        $aLinkAddon = $this->getLinkAddByPrams();

        if ($aLinkAddon) {
           foreach($aLinkAddon as $sValue)
                $sPageLink .= $sValue;
        }

        if(!$this->id)
            $this->id = 0;

        $sLoadDynamicUrl = $this->id .', \'searchKeywordContent.php?searchMode=ajax&section[]=' . $this->aCurrent['name'] . $aLinkAddon['params'];
        $sKeyword = bx_get('keyword');
        if ($sKeyword !== false && mb_strlen($sKeyword) > 0)
            $sLoadDynamicUrl .= '&keyword=' . rawurlencode($sKeyword);
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sPageLink,
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => !$bPageReload ? '' : 'return !loadDynamicBlock(' . $sLoadDynamicUrl . $aLinkAddon['paginate'].'\');',
            'on_change_per_page' => !$bChangePage ? '' : 'return !loadDynamicBlock(' . $sLoadDynamicUrl .'&page=1&per_page=\' + this.value);',
        ));
        return '<div class="clear_both"></div>' . $oPaginate->getPaginate();
    }

    function getLinkAddByPrams ($aExclude = array())
    {
        $aExclude[] = '_r';
        $aExclude[] = 'pageBlock';
        $aExclude[] = 'searchMode';
        $aExclude[] = 'section';
        $aExclude[] = 'keyword';
        $aLinks = array();
        $aCurrParams = array();
        $aParams = array();

        foreach ($this->aCurrent['restriction'] as $sKey => $aValue) {
            if (isset($aValue['paramName'])) {
                if (is_array($aValue['value']))
                    $aCurrParams[$aValue['paramName']] = $aValue['value'];
                elseif (mb_strlen($aValue['value']) > 0)
                    $aCurrParams[$aValue['paramName']] = $aValue['value'];
            }
        }

        // add get params
        foreach ($_GET as $sKey => $sValue) {
            if (!in_array($sKey, $aExclude))
                $aParams[rawurlencode($sKey)] = rawurlencode($sValue);
        }
        $aParams = array_merge($aParams, $aCurrParams);
        $aLinks = array('params'=>'', 'paginate'=>'');
        foreach ($aParams as $sKey => $sValue) {
            if ($sKey != 'page' && $sKey != 'per_page')
                $aLinks['params'] .= '&'.$sKey.'='.$sValue;
        }
        //paginate
        $aLinks['paginate'] .= '&page={page}';
        $aLinks['paginate'] .= '&per_page={per_page}';
        return $aLinks;
    }

    function clearFilters ($aPassParams = array(), $aPassJoins = array())
    {
        //clear sorting
        $this->aCurrent['sorting'] = 'last';
        //clear restrictions
        foreach ($this->aCurrent['restriction'] as $sKey => $aValue) {
            if (!in_array($sKey, $aPassParams))
                $this->aCurrent['restriction'][$sKey]['value'] = '';
        }
        //clear unnecessary joins (remains only profile join)
        $aPassJoins[] = 'profile';
        $aTemp = array();
        foreach ($aPassJoins as $sValue) {
            if (isset($this->aCurrent['join'][$sValue]) && is_array($this->aCurrent['join'][$sValue]))
                $aTemp[$sValue] = $this->aCurrent['join'][$sValue];
        }
        $this->aCurrent['join'] = $aTemp;
    }

    function fillFilters ($aParams)
    {
        // transform all given values to fields values
        if (is_array($aParams)) {
            foreach ($aParams as $sKey => $mixedValue) {
                if (isset($this->aCurrent['restriction'][$sKey]))
                    $this->aCurrent['restriction'][$sKey]['value'] = $mixedValue;
            }
        }
    }

    function getTopMenu ($aExclude = array())
    {
    }

    function getBottomMenu ($sAllLinkType = 'browseAll', $iId = 0, $sUri = '', $aExclude = array(), $bPgnSim = TRUE)
    {
        if (strpos($sAllLinkType, 'http') === false) {
            if (isset($this->aConstants['linksTempl'][$sAllLinkType]))
                $sAllUrl = $this->getCurrentUrl($sAllLinkType, $iId, $sUri);
            else
                $sAllUrl = $this->getCurrentUrl('browseAll', 0, '');
        } else
            $sAllUrl = $sAllLinkType;
        $sModeName = $this->aCurrent['name'] . '_mode';
        $sMode = isset($_GET[$sModeName]) ? '&' . $sModeName . '=' . rawurlencode($_GET[$sModeName]) : $sModeName . '=' . $this->aCurrent['sorting'];
        $aLinkAddon = $this->getLinkAddByPrams($aExclude);
        $sLink = bx_html_attribute($_SERVER['PHP_SELF']);
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sAllUrl,
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $sLink . '?' . $sMode . $aLinkAddon['params'] . $aLinkAddon['paginate'] . '\');',
            'on_change_per_page' => 'return !loadDynamicBlock({id}, \'' . $sLink . '?' . $sMode . $aLinkAddon['params'] . '&page=1&per_page=\' + this.value);',
        ));
        return $bPgnSim ? $oPaginate->getSimplePaginate($sAllUrl) : $oPaginate->getPaginate();
    }

    function getBrowseBlock ($aParams, $aCustom = array(), $sMainUrl = '', $bClearJoins = true)
    {
        $aJoins = $bClearJoins ? array('albumsObjects', 'albums') : array_keys($this->aCurrent['join']);
        $this->clearFilters(array('activeStatus', 'albumType', 'album_status', 'ownerStatus'), $aJoins);
        $this->addCustomParts();
        $aCustomTmpl = array(
            'enable_center' => true,
            'unit_css_class' => ' > div:not(.clear_both)',
            'page' => 1,
            'per_page' => 10,
            'sorting' => 'last',
            'simple_paginate' => true,
            'dynamic_paginate' => true,
            'menu_top' => false,
            'menu_bottom' => true,
            'menu_bottom_type' => 'browseAll',
            'menu_bottom_param'=> ''
        );
        $aCustom = array_merge($aCustomTmpl, $aCustom);
        $this->aCurrent['paginate']['perPage'] = (int)$aCustom['per_page'];
        $this->aCurrent['paginate']['page'] = (int)$aCustom['page'];
        $this->aCurrent['sorting'] = $aCustom['sorting'];
        foreach ($aParams as $sKey => $mixedValues) {
            if (isset($this->aCurrent['restriction'][$sKey]))
                $this->aCurrent['restriction'][$sKey]['value'] = $mixedValues;
        }
        $aList = $this->getSearchData();
        $bWrapDisable = true;
        if ($this->aCurrent['paginate']['totalNum'] > 0) {
            foreach ($aList as $aData)
                $sCode .= $this->displaySearchUnit($aData);
            $sCode .= '<div class="clear_both"></div>';

            if($aCustom['wrapper_class'])
                $sCode = '<div class="' . $aCustom['wrapper_class'] . '">' . $sCode . '</div>';

            if($aCustom['enable_center'])
                $sCode = $GLOBALS['oFunctions']->centerContent($sCode, $aCustom['unit_css_class']);

            $sCode = $GLOBALS['oSysTemplate']->parseHtmlByName('default_margin_thd.html', array(
                'content' => $sCode
            ));

            if ($aCustom['dynamic_paginate']) {
                $aExclude = array($this->aCurrent['name'] . '_mode', 'r');
                $aLinkAddon = $this->getLinkAddByPrams($aExclude);
                $sOnChange = 'return !loadDynamicBlock({id}, \'' . $sMainUrl . $aLinkAddon['params'] . $aLinkAddon['paginate'] . '\');';
            }
        }
        $aMenuTop = $aCustom['menu_top'] ? $this->getTopMenu($aExclude): array();
        $mixedMenuBottom = array();
        if ($aCustom['menu_bottom'])
            $mixedMenuBottom = $this->getBottomMenu($aCustom['menu_bottom_type'], 0, $this->aCurrent['restriction'][$aCustom['menu_bottom_param']]['value'], array(), $aCustom['simple_paginate']);
        return array('code' => $sCode, 'menu_top'=> $aMenuTop, 'menu_bottom' => $mixedMenuBottom, 'wrapper' => $bWrapDisable);
    }

    function serviceGetBrowseBlock ($aParams, $sMainUrl = '', $aCustom = array())
    {
        $aCode = $this->getBrowseBlock($aParams, $aCustom, $sMainUrl);
        return $aCode['code'] . $aCode['menu_bottom'];
    }

    /*
     * Get number of all elements under specified search criterias
     * @param array $aFilter - search criteria like 'restriction key'=>'rest. value'
     * @param array $aJoin   - list of joins elements from $this->aCurrent['join'] field which shouldn't be cleared,
       if empty then all current joins will be left
     * @return integer number of found elements
     */
    function serviceGetAllCount ($aFilter, $aJoin = array())
    {
        if (is_array($aFilter)) {
            // collect all current joins, but clear almost all search values
            if (!is_array($aJoin) || empty($aJoin))
                $aCurrJoins = array_keys($this->aCurrent['join']);
            else
                $aCurrJoins = $aJoin;
            $this->clearFilters(array('activeStatus'), $aCurrJoins);
            foreach ($aFilter as $sKey => $mixedValue) {
                if (isset($this->aCurrent['restriction'][$sKey]))
                    $this->aCurrent['restriction'][$sKey]['value'] = $mixedValue;
            }
            return $this->getCount();
        }
    }
}
