<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTemplate');

define('BX_DOL_PAGINATE_PER_PAGE_TYPE_FLOAT', 'float');
define('BX_DOL_PAGINATE_PER_PAGE_TYPE_STATIC', 'static');
define('BX_DOL_PAGINATE_PER_PAGE_STEP', 5);
define('BX_DOL_PAGINATE_PER_PAGE_INTERVAL', 3);
define('BX_DOL_PAGINATE_PER_PAGE_INTERVAL_MIN', 3);
define('BX_DOL_PAGINATE_PER_PAGE_VALUES', '10,20,50,100,200');
define('BX_DOL_PAGINATE_PER_PAGE_DEFAULT', 10);
define('BX_DOL_PAGINATE_RANGE', 3);

/**
 * Paginage for any content.
 *
 * It is used to create paginate, configuring it via input parameters.
 * The list of available input parameters:
 * --- Main parameters
 * start - position of the first item.
 * count - total number of items.
 * per_page - number of items displayed on the page.
 * sorting - sorting order.
 * page_url - current page URL.
 * view_all_url - URL for 'view all' page.
 * range - number of pages from the left and rigth sides of the current page.
 * page - current page.
 *
 * --- JS mode parameters
 * on_change_page - JavaScript function to be called on change page.
 * on_change_per_page - JavaScript function to be called on change number of results per page.
 * on_change_sorting - JavaScript function to be called on change the sorting order.
 *
 * --- Per page parameters
 * per_page_step - difference between two nearest per page values.
 * per_page_interval - number of values from the left and rigth sides of the current page page value.
 *
 * --- Layout parameters
 * info - display info.
 * view_all - display 'View All' link.
 * page_reloader - display 'Reloader' button.
 * per_page_changer - display 'Per page' dropdown.
 * page_links - display page links.
 *
 * Example of usage:
 * $oPaginate = new BxDolPaginate(array(
 *      'start' => 0,
 *      'count' => 100,
 *      'per_page' => 10,
 *      'on_change_page' => 'changePage({start}, {per_page})'
 * ));
 * $oPaginate->getPaginate();
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolPaginate extends BxDolTemplate
{
    var $_iStart;
    var $_iCount;
    var $_iPerPage;
    var $_sSorting;
    var $_sPageUrl;
    var $_iRange;
    var $_iPage;
    var $_iPages;
    var $_sViewAllUrl;

    var $_sOnChangePage;
    var $_sOnChangePerPage;
    var $_sOnChangeSorting;

    var $_sButtonActiveTmpl;
    var $_sButtonActiveTmplMobile;
    var $_sButtonInactiveTmpl;
    var $_sLinkActiveTmpl;
    var $_sLinkInactiveTmpl;
    var $_sSortingTmpl;

    var $_sPerPageType;
    var $_iPerPageStep;
    var $_iPerPageInterval;
    var $_sPerPageValues;

    var $_bInfo;
    var $_bViewAll;
    var $_bPageReloader;
    var $_bPerPageChanger;
    var $_bPageLinks;
    var $_bPageLinksFirstLast;

    /**
     * Constructor
     */
    function __construct($aParams)
    {
        parent::__construct();

        //--- Main settings ---//
        $this->_iStart = isset($aParams['start']) ? (int)$aParams['start'] : 0;
        $this->_iCount = isset($aParams['count']) ? (int)$aParams['count'] : 0;
        $this->_iPerPage = isset($aParams['per_page']) ? (int)$aParams['per_page'] : BX_DOL_PAGINATE_PER_PAGE_DEFAULT;
        $this->_sSorting = isset($aParams['sorting']) ? $aParams['sorting'] : '';
        $this->_sPageUrl = isset($aParams['page_url']) ? $aParams['page_url'] : BX_DOL_URL_ROOT;
        $this->_iRange = isset($aParams['range']) ? (int)$aParams['range'] : BX_DOL_PAGINATE_RANGE;
        $this->_iPage = isset($aParams['page']) ? (int)$aParams['page'] : 0;
        $this->_iPages = 0;
        $this->_sViewAllUrl = isset($aParams['view_all_url']) ? $aParams['view_all_url'] : BX_DOL_URL_ROOT;

        //--- Check Start values ---//
        if(empty($this->_iStart) && !empty($this->_iPage)) {
            $this->_iStart = ($this->_iPage - 1) * $this->_iPerPage;
            $this->_iPage = 0;
        }

        //--- JS mode settings ---//
        $this->_sOnChangePage = isset($aParams['on_change_page']) ? $aParams['on_change_page'] : '';
        $this->_sOnChangePerPage = isset($aParams['on_change_per_page']) ? $aParams['on_change_per_page'] : '';
        $this->_sOnChangeSorting = isset($aParams['on_change_sorting']) ? $aParams['on_change_sorting'] : '';

        //--- Per page settings ---//
        $this->_sPerPageType = isset($aParams['per_page_type']) ? $aParams['per_page_type'] : BX_DOL_PAGINATE_PER_PAGE_TYPE_STATIC;
        $this->_iPerPageStep = isset($aParams['per_page_step']) ? (int)$aParams['per_page_step'] : BX_DOL_PAGINATE_PER_PAGE_STEP;
        $this->_iPerPageInterval = isset($aParams['per_page_interval']) ? (int)$aParams['per_page_interval'] : BX_DOL_PAGINATE_PER_PAGE_INTERVAL;
        $this->_sPerPageValues = isset($aParams['per_page_values']) ? (int)$aParams['per_page_values'] : '&#8230;,'.BX_DOL_PAGINATE_PER_PAGE_VALUES;

        //--- Paginate's layout ---//
        $this->_bInfo = isset($aParams['info']) && $aParams['info'] === true;
        $this->_bViewAll = isset($aParams['view_all']) && $aParams['view_all'] === true;
        $this->_bPageReloader = isset($aParams['page_reloader']) && $aParams['page_reloader'] === true;
        $this->_bPerPageChanger = isset($aParams['per_page_changer']) && $aParams['per_page_changer'] === true;
        $this->_bPageLinks = !isset($aParams['page_links']) || (isset($aParams['page_links']) && $aParams['page_links'] === true);
        $this->_bPageLinksFirstLast = $this->_bPageLinks && isset($aParams['page_links_first_last']) && $aParams['page_links_first_last'] === true;

        //--- Templates ---//
        $this->_sButtonActiveTmpl = $GLOBALS['oTemplConfig']->sPaginateButtonActiveTmpl;
        $this->_sButtonActiveTmplMobile = $GLOBALS['oTemplConfig']->sPaginateButtonActiveTmplMobile;
        $this->_sButtonInactiveTmpl = $GLOBALS['oTemplConfig']->sPaginateButtonInactiveTmpl;

        $this->_sLinkActiveTmpl = $GLOBALS['oTemplConfig']->sPaginateLinkActiveTmpl;
        $this->_sLinkInactiveTmpl = $GLOBALS['oTemplConfig']->sPaginateLinkInactiveTmpl;
        $this->_sSortingTmpl = $GLOBALS['oTemplConfig']->sPaginateSortingTmpl;
    }
    function setCount($iCount)
    {
        $this->_iCount = $iCount;
    }
    function setOnChangePage($sCode)
    {
        $this->_sOnChangePage = $sCode;
    }
    function setOnChangePerPage($sCode)
    {
        $this->_sOnChangePerPage = $sCode;
    }
    function getSorting($aValues, $sSorting = '')
    {
        if(!empty($sSorting))
            $this->_sSorting = $sSorting;

        //--- Language keys ---//
        $sSortingTitle  = _t('_Order by');

        $sContent = '';
        foreach($aValues as $sKey => $sValue)
            $sContent .= '<option value="' . $sKey . '" ' . ($sKey == $this->_sSorting ? 'selected="selected"' : '') . '>' . _t($sValue) . '</option>';

        $aReplacement = $this->_getReplacement();

        if(!empty($this->_sOnChangeSorting))
            $sOnChangeSorting = $this->parseHtmlByContent($this->_sOnChangeSorting, $aReplacement, array('{', '}'));
        else {
            $aReplacement['page'] = 1;
            $aReplacement['sorting'] = "' + this.value + '";
            $sOnChangeSorting = "window.location='" . $this->parseHtmlByContent($this->_sPageUrl, $aReplacement, array('{', '}')) . "'";
        }
        $aReplacement = array (
            'title' => $sSortingTitle,
            'on_click' => 'onchange="javascript:' . $sOnChangeSorting . '"',
            'content' => $sContent
        );

        return $this->parseHtmlByContent($this->_sSortingTmpl, $aReplacement);
    }
    function getPages($iPerPage = -1)
    {
        return $this->parseHtmlByName('paginate_pages.html', $this->_getPerPageChanger($iPerPage));
    }
    function getPaginate($iStart = -1, $iPerPage = -1, $sTemplate = false)
    {
        if($iStart !== -1)
            $this->_iStart = $iStart;
        if($iPerPage !== -1)
            $this->_iPerPage = $iPerPage;
        $this->_iPages = ceil( $this->_iCount / $this->_iPerPage);

        if( $this->_iPages <= 1)
            return "";

        $this->_iPage = round($this->_iStart/$this->_iPerPage) + 1;

        $this->_iPages = (int)ceil(round($this->_iCount/$this->_iPerPage));
        $this->_iPages = $this->_iPages * $this->_iPerPage < $this->_iCount ? $this->_iPages + 1 : $this->_iPages;

        $iRangeBeg = ($iRangeBeg = $this->_iPage - $this->_iRange) > 1 ? $iRangeBeg : 1;
        $iRangeEnd = ($iRangeEnd = $this->_iPage + $this->_iRange) < $this->_iPages ? $iRangeEnd : $this->_iPages;

        //--- Language keys ---//
        $sPageCpt = _t('_Page');
        $sNextCpt = _t('_Next page');
        $sLastCpt = _t('_Last page');
        $sPrevCpt = _t('_Previous page');
        $sFirstCpt = _t('_First page');
        $sOfCpt = _t('_of');
        $sViewAllCpt = _t('_View All');
        $sReloaderCpt = _t('_Refresh');

        //--- Pagination Content ---//
        $aReplacement = $this->_getReplacement();

        $sContent = "";
        //--- Generate Paginate's main content ---//
        if($this->_iPage > 1) {
            //--- First Page button (Active) ---//
        	if($this->_bPageLinksFirstLast) {
	            $aReplacementLink = array_merge($aReplacement, array('start' => 0, 'page' => 1));
	            $sContent .= $this->parseHtmlByContent($this->_sButtonActiveTmpl, array(
	                'icon' => 'step-backward',
	                'btn_img_src' => $this->getIconUrl('sys_pgt_first.png'),
	                'lnk_url' => $this->_getPageChangeUrl($aReplacementLink),
	                'lnk_on_click' => $this->_getPageChangeOnClick($aReplacementLink),
	                'lnk_title' => $sFirstCpt,
	                'img_title' => $sFirstCpt
	            ));
        	}

            //--- Previous Page button (Active) ---//
            $aReplacementLink = array_merge($aReplacement, array('start' => $this->_iStart - $this->_iPerPage, 'page' => $this->_iPage - 1));
            $sContent .= $this->parseHtmlByContent($this->_sButtonActiveTmpl, array(
                'icon' => 'backward',
                'btn_img_src' => $this->getIconUrl('sys_pgt_prev.png'),
                'lnk_url' => $this->_getPageChangeUrl($aReplacementLink),
                'lnk_on_click' => $this->_getPageChangeOnClick($aReplacementLink),
                'lnk_title' => $sPrevCpt,
                'img_title' => $sPrevCpt
            ));
        } else {
            //--- First Page button (Inactive) ---//
        	if($this->_bPageLinksFirstLast) {
	            $sContent .= $this->parseHtmlByContent($this->_sButtonInactiveTmpl, array(
	                'icon' => 'step-backward',
	                'btn_img_src' => $this->getIconUrl('sys_pgt_first.png'),
	                'img_title' => $sFirstCpt
	            ));
        	}

            //--- Previous Page button (Inactive) ---//
            $sContent .= $this->parseHtmlByContent($this->_sButtonInactiveTmpl, array(
                'icon' => 'backward',
                'btn_img_src' => $this->getIconUrl('sys_pgt_prev.png'),
                'img_title' => $sPrevCpt
            ));
        }

        //--- Page links ---//
        if($this->_bPageLinks)
            for($i = $iRangeBeg; $i <= $iRangeEnd; $i++)
               if($i == $this->_iPage)
                    $sContent .= $this->parseHtmlByContent($this->_sLinkInactiveTmpl, array(
                        'lnk_content' => $i
                    ));
                else {
                    $aReplacementLink = array_merge($aReplacement, array('start' => ($i - 1) * $this->_iPerPage, 'page' => $i));
                    $sContent .= $this->parseHtmlByContent($this->_sLinkActiveTmpl, array(
                        'lnk_url' => $this->_getPageChangeUrl($aReplacementLink),
                        'lnk_on_click' => $this->_getPageChangeOnClick($aReplacementLink),
                        'lnk_title' => '',
                        'lnk_content' => $i
                    ));
                }

        if($this->_iPage < $this->_iPages) {
            //--- Next Page button (Active) ---//
            $aReplacementLink = array_merge($aReplacement, array('start' => $this->_iPage * $this->_iPerPage, 'page' => $this->_iPage + 1));
            $sContent .= $this->parseHtmlByContent($this->_sButtonActiveTmpl, array(
                'icon' => 'forward',
                'btn_img_src' => $this->getIconUrl('sys_pgt_next.png'),
                'lnk_url' => $this->_getPageChangeUrl($aReplacementLink),
                'lnk_on_click' => $this->_getPageChangeOnClick($aReplacementLink),
                'lnk_title' => $sNextCpt,
                'img_title' => $sNextCpt
            ));

            //--- Last Page button (Active) ---//
            if($this->_bPageLinksFirstLast) {
	            $aReplacementLink = array_merge($aReplacement, array('start' => ($this->_iPages - 1) * $this->_iPerPage, 'page' => $this->_iPages));
	            $sContent .= $this->parseHtmlByContent($this->_sButtonActiveTmpl, array(
	                'icon' => 'step-forward',
	                'btn_img_src' => $this->getIconUrl('sys_pgt_last.png'),
	                'lnk_url' => $this->_getPageChangeUrl($aReplacementLink),
	                'lnk_on_click' => $this->_getPageChangeOnClick($aReplacementLink),
	                'lnk_title' => $sLastCpt,
	                'img_title' => $sLastCpt
	            ));
            }
        } else {
            //--- Next Page button (Inactive) ---//
            $sContent .= $this->parseHtmlByContent($this->_sButtonInactiveTmpl, array(
                'icon' => 'forward',
                'btn_img_src' => $this->getIconUrl('sys_pgt_next.png'),
                'img_title' => $sNextCpt
            ));

            //--- Last Page button (Inactive) ---//
            if($this->_bPageLinksFirstLast) {
	            $sContent .= $this->parseHtmlByContent($this->_sButtonInactiveTmpl, array(
	                'icon' => 'step-forward',
	                'btn_img_src' => $this->getIconUrl('sys_pgt_last.png'),
	                'img_title' => $sLastCpt
	            ));
            }
        }

        $bSimple = !$this->_bInfo && !$this->_bViewAll && !$this->_bPageReloader && !$this->_bPerPageChanger;
        $aVariables = array (
        	'class' => $bSimple ? ' paginate_simple' : '',
            'bx_if:info' => array (
                'condition' => $this->_bInfo,
                'content' => array(
                    'from' => $this->_iStart + 1,
                    'to' => ($iTo = $this->_iStart + $this->_iPerPage) < $this->_iCount ? $iTo : $this->_iCount,
                    'of' => $sOfCpt,
                    'total' => $this->_iCount,
                )
            ),
            'bx_if:view_all' => array(
                'condition' => $this->_bViewAll,
                'content' => array(
                    'lnk_url' => $this->_sViewAllUrl,
                    'lnk_title' => $sViewAllCpt,
                    'lnk_content' => $sViewAllCpt . ' (' . $this->_iCount . ')'
                ),
            ),
            'bx_if:reloader' => array(
                'condition' => $this->_bPageReloader,
                'content' => array(
                    'lnk_url' => $this->_getPageChangeUrl($aReplacement),
                    'lnk_on_click' => $this->_getPageChangeOnClick($aReplacement),
                    'lnk_title' => $sReloaderCpt,
                )
            ),
            'bx_if:per_page' => array(
                'condition' => $this->_bPerPageChanger,
                'content' => $this->_getPerPageChanger()
            ),
            'content' => $sContent
        );

        return $this->parseHtmlByName($sTemplate ? $sTemplate : 'paginate.html', $aVariables);
    }
    function getSimplePaginate($sViewAllUrl = '', $iStart = -1, $iPerPage = -1, $bViewAll = true)
    {
        if(!empty($sViewAllUrl))
            $this->_sViewAllUrl = $sViewAllUrl;

        $this->_bInfo = false;
        $this->_bPageReloader = false;
        $this->_bPerPageChanger = false;
        $this->_bPageLinks = false;
        $this->_bViewAll = $bViewAll;

        return $this->getPaginate($iStart, $iPerPage);
    }

    function getMobilePaginate($iStart = -1, $iPerPage = -1)
    {
        $this->_bInfo = false;
        $this->_bPageReloader = false;
        $this->_bPerPageChanger = false;
        $this->_bPageLinks = false;
        $this->_bViewAll = false;

        $sButtonActiveTmpl = $this->_sButtonActiveTmpl;
        $sButtonInactiveTmpl = $this->_sButtonInactiveTmpl;

        $this->_sButtonActiveTmpl = $this->_sButtonActiveTmplMobile;
        $this->_sButtonInactiveTmpl = '';

        $sCode = $this->getPaginate($iStart, $iPerPage, 'paginate_mobile.html');

        $this->_sButtonActiveTmpl = $sButtonActiveTmpl;
        $this->_sButtonInactiveTmpl = $sButtonInactiveTmpl;

        return $sCode;
    }

    function _getReplacement()
    {
        return array(
            'start' => $this->_iStart,
            'count' => $this->_iCount,
            'page' => $this->_iPage,
            'pages' => $this->_iPages,
            'per_page' => $this->_iPerPage,
            'sorting' => $this->_sSorting
        );
    }
    function _getPageChangeUrl($aReplacement)
    {
        return $this->parseHtmlByContent($this->_sPageUrl, $aReplacement, array('{', '}'));
    }
    function _getPageChangeOnClick($aReplacement)
    {
        return !empty($this->_sOnChangePage) ? 'onclick="javascript:' . $this->parseHtmlByContent($this->_sOnChangePage, $aReplacement, array('{', '}')) . '; return false;"' : '';
    }
    function _getPerPageChanger($iPerPage = -1)
    {
        if($iPerPage !== -1)
            $this->_iPerPage = $iPerPage;

        $sMethod = '_getPerPageChanger' . ucfirst($this->_sPerPageType);
        if(!method_exists($this, $sMethod))
            return array();

        return $this->$sMethod();
    }

    function _getPerPageChangerFloat()
    {
        $iInterval = floor($this->_iPerPage / $this->_iPerPageStep);
        $iInterval = $iInterval > $this->_iPerPageInterval ? $this->_iPerPageInterval : $iInterval;
        $iInterval = $iInterval < BX_DOL_PAGINATE_PER_PAGE_INTERVAL_MIN ? BX_DOL_PAGINATE_PER_PAGE_INTERVAL_MIN : $iInterval;

        $iCount = 2 * $iInterval + 1;
        $iValue = $this->_iPerPage - $iInterval * $this->_iPerPageStep;

        $aOptions = array();
        for($i = 0; $i < $iCount; $i++, $iValue += $this->_iPerPageStep)
            if($iValue > 0)
                $aOptions[] = array(
                    'opt_value' => $iValue,
                    'opt_selected' => ($iValue == $this->_iPerPage ? ' selected="selected"' : ''),
                    'opt_caption' => $iValue,
                );

        $aReplacement = $this->_getReplacement();
        if(!empty($this->_sOnChangePerPage))
            $sPerPageOnChange = $this->parseHtmlByContent($this->_sOnChangePerPage, $aReplacement, array('{', '}'));
        else
            $sPerPageOnChange = "window.location='" . $this->_getPageChangeUrl(array_merge($aReplacement, array('page' => 1, 'per_page' => "' + this.value + '"))) . "'";

        return array (
            'per_page_caption' => _t('_Results per page'),
            'per_page_on_change' => $sPerPageOnChange,
            'bx_repeat:options' => $aOptions
        );
    }

    function _getPerPageChangerStatic()
    {
        $aValues = explode(",", $this->_sPerPageValues);

        $aOptions = array();
        foreach($aValues as $iValue)
            if($iValue > 0 || '&#8230;' == $iValue)
                $aOptions[] = array(
                    'opt_value' => '&#8230;' == $iValue ? $this->_iPerPage : $iValue,
                    'opt_selected' => ($iValue == $this->_iPerPage ? ' selected="selected"' : ''),
                    'opt_caption' => $iValue,
                );

        $aReplacement = $this->_getReplacement();
        if(!empty($this->_sOnChangePerPage))
            $sPerPageOnChange = $this->parseHtmlByContent($this->_sOnChangePerPage, $aReplacement, array('{', '}'));
        else
            $sPerPageOnChange = "window.location='" . $this->_getPageChangeUrl(array_merge($aReplacement, array('page' => 1, 'per_page' => "' + this.value + '"))) . "'";

        return array (
            'per_page_caption' => _t('_Results per page'),
            'per_page_on_change' => $sPerPageOnChange,
            'bx_repeat:options' => $aOptions
        );
    }
}
