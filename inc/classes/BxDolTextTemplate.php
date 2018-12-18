<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

class BxDolTextTemplate extends BxDolModuleTemplate
{
    var $_oModule;

    var $oPaginate;
    var $sCssPrefix;

    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
        $this->_aTemplates = array('comments');

        $this->_oModule = null;
        $this->oPaginate = null;
        $this->sCssPrefix = '';
    }

    function setModule(&$oModule)
    {
        $this->_oModule = $oModule;

        $this->oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'start' => 0,
            'per_page' => $this->_oConfig->getPerPage(),
            'per_page_step' => 2,
            'per_page_interval' => 3,
            'on_change_page' => $this->_oConfig->getJsObject() . '.changePage({start}, {per_page})'
        ));
    }

    function displayAdminBlock($aParams)
    {
        $oSearchResult = $aParams['search_result_object'];
        unset($aParams['search_result_object']);

        $sModuleUri = $this->_oConfig->getUri();
        $aButtons = array(
            $sModuleUri . '-publish' => _t('_' . $sModuleUri . '_lcaption_publish'),
            $sModuleUri . '-unpublish' => _t('_' . $sModuleUri . '_lcaption_unpublish'),
            $sModuleUri . '-featured' => _t('_' . $sModuleUri . '_lcaption_featured'),
            $sModuleUri . '-unfeatured' => _t('_' . $sModuleUri . '_lcaption_unfeatured'),
            $sModuleUri . '-delete' => _t('_' . $sModuleUri . '_lcaption_delete')
        );

        $aResult = array(
            'include_css' => $this->addCss(array('view.css', 'cmts.css'), true),
            'include_js_content' => $this->getViewJs(),
            'filter' => $oSearchResult->showAdminFilterPanel($this->_oDb->unescape($aParams['filter_value']), $sModuleUri . '-filter-txt', $sModuleUri . '-filter-chb', $sModuleUri . '-filter'),
            'content' => $this->displayList($aParams),
            'control' => $oSearchResult->showAdminActionsPanel($this->sCssPrefix . '-view-admin', $aButtons, $sModuleUri . '-ids')
        );

        return $this->addJs(array('main.js'), true) . $this->parseHtmlByName('admin.html', $aResult);
    }
    function displayBlockInfo($aEntry, $sFields = '')
    {
        $aAuthor = getProfileInfo($aEntry['author_id']);

        return $this->parseHtmlByName('entry_info.html', array (
            'author_unit' => get_member_thumbnail($aAuthor['ID'], 'none', true),
            'date' => getLocaleDate($aEntry['date'], BX_DOL_LOCALE_DATE_SHORT),
            'date_ago' => defineTimeInterval($aEntry['date'], false),
            'cats' => $this->parseCategories($aEntry['categories']),
            'tags' => $this->parseTags($aEntry['tags']),
            'fields' => $sFields,
        )); 
    }
    function displayBlock($aParams)
    {
        $bShowEmpty = isset($aParams['show_empty']) ? $aParams['show_empty'] : true;

        $aResult = array(
            'include_js_content' => $this->getViewJs(),
            'content' => $this->displayList($aParams),
        );

        if(!$bShowEmpty && empty($aResult['content']))
            return "";

        $this->addJs(array('main.js'));
        $this->addCss(array('view.css'));
        return $this->parseHtmlByName('view.html', $aResult);
    }
    function displayList($aParams)
    {
        $sSampleType = $aParams['sample_type'];
        $iViewerType = $aParams['viewer_type'];
        $iStart = isset($aParams['start']) ? (int)$aParams['start'] : -1;
        $iPerPage = isset($aParams['count']) ? (int)$aParams['count'] : -1;
        $bShowEmpty = isset($aParams['show_empty']) ? $aParams['show_empty'] : true;

        $sModuleUri = $this->_oConfig->getUri();
        $aEntries = $this->_oDb->getEntries($aParams);
        if(empty($aEntries))
            return $bShowEmpty ? MsgBox(_t('_' . $sModuleUri . '_msg_no_results')) : "";

        $sBaseUri = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
        $sJsMainObject = $this->_oConfig->getJsObject();

        $sList = '';
        foreach($aEntries as $aEntry)
            $sList .= $this->displayItem($aParams, $aEntry);

        $sPaginate = '';
        if(!in_array($sSampleType, array('id', 'uri', 'view', 'search_unit'))) {
            if(!empty($sSampleType))
                $this->_updatePaginate($aParams);

            $sPaginate = $this->oPaginate->getPaginate($iStart, $iPerPage);
        }

        return $this->parseHtmlByName('list.html', array(
            'sample' => $sSampleType,
            'list' => $sList,
            'paginate' => $sPaginate,
            'loading' => LoadingBox($sModuleUri . '-' . $sSampleType . '-loading')
        ));
    }
    function displayItem($aParams, &$aEntry)
    {
        $sSampleType = $aParams['sample_type'];
        $iViewerType = $aParams['viewer_type'];
        $bAdminPanel = $iViewerType == BX_TD_VIEWER_TYPE_ADMIN && ((isset($aParams['admin_panel']) && $aParams['admin_panel']) || $sSampleType == 'admin');

        $sModuleUri = $this->_oConfig->getUri();
        $sLKLinkEdit = _t('_' . $sModuleUri . '_lcaption_edit');

        $aTmplVars = array(
            'id' => $this->_oConfig->getSystemPrefix() . $aEntry['id'],
            'caption' => str_replace("$", "&#36;", $aEntry['caption']),
            'class' => !in_array($sSampleType, array('view')) ? ' ' . $this->sCssPrefix . '-text-snippet ' : '',
            'date' => defineTimeInterval($aEntry['when_uts']),
            'content' => str_replace("$", "&#36;", $aEntry['content']),
            'link' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aEntry['uri'],
            'bx_if:checkbox' => array(
                'condition' => $bAdminPanel,
                'content' => array(
                    'id' => $aEntry['id']
                ),
            ),
            'bx_if:status' => array(
                'condition' => $iViewerType == BX_TD_VIEWER_TYPE_ADMIN,
                'content' => array(
                    'status' => _t('_' . $sModuleUri . '_status_' . $aEntry['status'])
                ),
            ),
            'bx_if:featured' => array(
                'condition' => $iViewerType == BX_TD_VIEWER_TYPE_ADMIN && (int)$aEntry['featured'] == 1,
                'content' => array(),
            ),
            'bx_if:edit_link' => array (
                'condition' => $iViewerType == BX_TD_VIEWER_TYPE_ADMIN,
                'content' => array(
                    'edit_link_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'admin/' . $aEntry['uri'],
                    'edit_link_caption' => $sLKLinkEdit,
                )
            )
        );

        return $this->parseHtmlByName('item.html', $aTmplVars);
    }
    function getViewJs($bWrap = false)
    {
        $sJsMainClass = $this->_oConfig->getJsClass();
        $sJsMainObject = $this->_oConfig->getJsObject();
        ob_start();
?>
        var <?=$sJsMainObject; ?> = new <?=$sJsMainClass; ?>({
            sSystem: '<?=$this->_oConfig->getSystemPrefix(); ?>',
            sActionUrl: '<?=BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(); ?>',
            sObjName: '<?=$sJsMainObject; ?>',
            sAnimationEffect: '<?=$this->_oConfig->getAnimationEffect(); ?>',
            iAnimationSpeed: '<?=$this->_oConfig->getAnimationSpeed(); ?>'
        });
<?php
        $sContent = ob_get_clean();
        return $bWrap ? $this->_wrapInTagJsCode($sContent) : $sContent;
    }
    function getPageCode(&$aParams)
    {
        global $_page;
        global $_page_cont;

        $iIndex = isset($aParams['index']) ? (int)$aParams['index'] : 0;
        $_page['name_index'] = $iIndex;
        $_page['js_name'] = isset($aParams['js']) ? $aParams['js'] : '';
        $_page['css_name'] = isset($aParams['css']) ? $aParams['css'] : '';
        $_page['extra_js'] = isset($aParams['extra_js']) ? $aParams['extra_js'] : '';

        check_logged();

        if(isset($aParams['content']))
            foreach($aParams['content'] as $sKey => $sValue)
                $_page_cont[$iIndex][$sKey] = $sValue;

        if(isset($aParams['title']['page']))
            $this->setPageTitle($aParams['title']['page']);
        if(isset($aParams['title']['block']))
            $this->setPageMainBoxTitle($aParams['title']['block']);

        if(isset($aParams['breadcrumb']))
            $GLOBALS['oTopMenu']->setCustomBreadcrumbs($aParams['breadcrumb']);

        PageCode($this);
    }
    function getPageCodeAdmin(&$aParams)
    {
        global $_page;
        global $_page_cont;

        $iIndex = isset($aParams['index']) ? (int)$aParams['index'] : 9;
        $_page['name_index'] = $iIndex;
        $_page['js_name'] = isset($aParams['js']) ? $aParams['js'] : '';
        $_page['css_name'] = isset($aParams['css']) ? $aParams['css'] : '';
        $_page['header'] = isset($aParams['title']['page']) ? $aParams['title']['page'] : '';

        if(isset($aParams['content']))
            foreach($aParams['content'] as $sKey => $sValue)
                $_page_cont[$iIndex][$sKey] = $sValue;

        PageCodeAdmin();
    }

    // ======================= tags/cat parsing functions

    function parseTags ($s)
    {
        return $this->_parseAnything ($s, ',', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'tag/');
    }

    function parseCategories ($s)
    {
        bx_import ('BxDolCategories');
        return $this->_parseAnything ($s, CATEGORIES_DIVIDER, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'category/');
    }

    protected function _updatePaginate($aParams)
    {
        switch($aParams['sample_type']) {
            default:
                $this->oPaginate->setCount($this->_oDb->getCount($aParams));
                $this->oPaginate->setOnChangePage($this->_oConfig->getJsObject() . '.changePage({start}, {per_page}, \'' . $aParams['sample_type'] . '\')');
        }
    }
}
