<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( './inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'match.inc.php');
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolProfileFields.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolProfilesController.php' );

bx_import('BxDolDb');
bx_import('BxDolPageView');
bx_import('BxTemplSearchProfile');
bx_import('BxTemplProfileGenerator');

class BxDolSearchPageView extends BxDolPageView
{
    var $oPF;

    var $aFilterSortSettings;

    function __construct()
    {
        parent::__construct('search');

        // get search mode
        switch( $_REQUEST['search_mode'] ) {
            case 'quick': $iPFArea = 10; break;
            case 'adv':   $iPFArea = 11; break;
            default:      $iPFArea = 9; // simple search (default)
        }

        $this->oPF = new BxDolProfileFields($iPFArea);

        $this->aFilterSortSettings = array();
    }

    function collectFilteredSettings()
    {
        $this->aFilterSortSettings = array (
            //'f_photos' => (isset($_GET['photos_only'])) ? $_GET['photos_only'] : null,
            //'f_online' => (isset($_GET['online_only'])) ? $_GET['online_only'] : null,
            'sort' => (isset($_GET['sort'])) ? process_db_input($_GET['sort'], BX_TAGS_STRIP) : 'activity',
            //'s_mode' => (isset($_GET['search_result_mode']) && $_GET['search_result_mode'] == 'ext') ? 'ext' : 'sim',
        );
    }

    function getBlockCode_SearchForm()
    {
        global $logged;

        $aProfile = $logged['member'] ? getProfileInfo(getLoggedId()) : array();

        // default params for search form
        $aDefaultParams = array();
        
        $sSrmKey = 'search_result_mode';
        if(bx_get($sSrmKey) !== false)
            $aDefaultParams[$sSrmKey] = process_db_input(bx_get($sSrmKey));

        $aForms = $this->oPF->getFormsSearch($aDefaultParams, true);
        foreach($aForms as $aForm) {
            if(empty($aForm['inputs']) || !is_array($aForm['inputs']))
                continue;

            foreach($aForm['inputs'] as $aInput) {
                $sName = $aInput['name'];
                $mValue = bx_get($sName);

                switch($sName) {
                    case 'LookingFor':
                        $aDefaultParams[$sName] = $mValue !== false ? $mValue : ($aProfile['Sex'] ? $aProfile['Sex'] : 'male');
                        break;

                    case 'Sex':
                        $aDefaultParams[$sName] = $mValue !== false ? $mValue : ($aProfile['LookingFor'] ? $aProfile['LookingFor'] : 'female');
                        break;

                    case 'Country':
                        $aDefaultParams[$sName] = $mValue !== false && isset($mValue[0]) ? $mValue[0] : ($aProfile['Country'] ? $aProfile['Country'] : getParam('default_country'));
                        break;

                    case 'DateOfBirth':
                        $aDefaultParams[$sName] = $mValue !== false ? $mValue : getParam('search_start_age') . '-' . getParam('search_end_age');
                        break;

                    default:
                        if($mValue !== false)
                            $aDefaultParams[$sName] = $mValue;
                }
            }
            
        }

        $sForms = $this->oPF->getFormCode(array('default_params' => $aDefaultParams));

        $bSimAct = ($this->oPF->iAreaID == 9) ? true : false;
        $bAdvAct = ($this->oPF->iAreaID == 11) ? true : false;
        $bQuiAct = ($this->oPF->iAreaID == 10) ? true : false;
        $sUrl = BX_DOL_URL_ROOT . 'search.php';

        $aLinks = array(
            _t('_search_tab_simple') => array('href' => $sUrl . '?search_mode=sim', 'active' => $bSimAct),
            _t('_search_tab_Adv') => array('href' => $sUrl . '?search_mode=adv', 'active' => $bAdvAct),
            _t('_search_tab_quick') => array('href' => $sUrl . '?search_mode=quick', 'active' => $bQuiAct),
        );

        return array($sForms, $aLinks, array(), false);
    }

    function showMatchProfiles($iBlockID)
    {
        $iProfileId = getLoggedId();

        if (!$iProfileId)
            return array('', MsgBox(_t('_Empty')));

        $sSort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'none';
        $aProfiles = getMatchProfiles($iProfileId, false, $sSort);

        if (empty($aProfiles))
            return array('', MsgBox(_t('_Empty')));

        $sBaseUri = 'search.php?show=match';
        $sTopLinksUri = '';
        $sPaginateUri = '';

        foreach ($_REQUEST as $sKey => $sVal) {
            switch ($sKey) {
                case 'page':
                    $sPaginateUri .= '&page=' . $sVal;
                    break;
                case 'per_page':
                    $sPaginateUri .= '&per_page=' . $sVal;
                    break;
                case 'sort':
                    $sPaginateUri .= '&sort=' . $sVal;
                    break;
                case 'mode':
                    $sTopLinksUri .= '&mode=' . $sVal;
                    break;
            }
        }

        $aPaginate = array(
            'page_url' => $sBaseUri . $sTopLinksUri . '&page={page}&per_page={per_page}&sort={sorting}',
            'info' => true,
            'page_links' => true,
            'per_page' => isset($_REQUEST['per_page']) ? (int)$_REQUEST['per_page'] : 25,
            'sorting' => $sSort,
            'count' => count($aProfiles),
            'page' => isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1,
        );
        $sMode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'simple';

        $oPaginate = new BxDolPaginate($aPaginate);

        $oSearchProfile = new BxTemplSearchProfile();
        $aExtendedCss = array( 'ext_css_class' => 'search_filled_block');
        $sTemplateName = $sMode == 'extended' ? 'search_profiles_ext.html' : 'search_profiles_sim.html';
        $iIndex = 0;
        $sOutputHtml = '';
        $sOutputMode = isset($_REQUEST['search_result_mode']) && $_REQUEST['search_result_mode'] == 'ext' ? 'ext' : 'sim';

        for ($i = ($aPaginate['page'] - 1) * $aPaginate['per_page'];
            $i < $aPaginate['page'] * $aPaginate['per_page'] && $i < $aPaginate['count']; $i++)
        {
            $aProfile = getProfileInfo($aProfiles[$i]);

            if ($aProfile['Couple']) {
                $aCoupleInfo = getProfileInfo($aProfile['Couple']);
                if (!($iIndex % 2))
                    $sOutputHtml .= $oSearchProfile->PrintSearhResult($aProfile, $aCoupleInfo, null, $sTemplateName);
                else
                    $sOutputHtml .= $oSearchProfile->PrintSearhResult($aProfile, $aCoupleInfo, $aExtendedCss, $sTemplateName);
            } else {
                if (!($iIndex % 2))
                    $sOutputHtml .= $oSearchProfile->PrintSearhResult($aProfile, '', null, $sTemplateName);
                else
                    $sOutputHtml .= $oSearchProfile->PrintSearhResult($aProfile, '', $aExtendedCss, $sTemplateName);
            }

            $iIndex++;
        }

        // gen sorting block ( type of : drop down );
        $sSortBlock = $oPaginate->getSorting(array(
            'none' => _t('_None'),
            'activity' => _t('_Latest activity'),
            'date_reg' => _t('_FieldCaption_DateReg_View'),
        ));
        $sSortBlock = '<div class="ordered_block">' . $sSortBlock . '</div><div class="clear_both"></div>';

        $sContent = $GLOBALS['oSysTemplate']->parseHtmlByName('designbox_top_controls.html', array(
            'top_controls' => $sSortBlock
        ));
        $sContent .= $GLOBALS['oSysTemplate']->parseHtmlByName('view_profiles.html', array(
            'margin_type' => $sOutputMode == 'sim' ? '-thd' : '',
            'content' => $sOutputHtml
        )) . $oPaginate->getPaginate();

        $aLinks = array(
             _t('_Simple') => array(
                 'href' => $sBaseUri . $sPaginateUri . '&mode=simple',
                 'dynamic' => true,
                 'active' => $sMode == 'simple'
             ),
             _t('_Extended') => array(
                 'href' => $sBaseUri . $sPaginateUri . '&mode=extended',
                 'dynamic' => true,
                 'active' => $sMode == 'extended'
             )
        );

        return array(
            $aLinks,
            $sContent
        );
    }

    function getBlockCode_Results($iBlockID)
    {
        //collect inputs
        $aRequestParams = $this->oPF->collectSearchRequestParams();

        if( isset( $_REQUEST['Tags'] ) and trim( $_REQUEST['Tags'] ) )
            $aRequestParams['Tags'] = trim( process_pass_data( $_REQUEST['Tags'] ) );

        if( isset( $_REQUEST['distance'] ) and (int)$_REQUEST['distance'] )
            $aRequestParams['distance'] = (int)$_REQUEST['distance'];

        // start page generation
        $oProfile = new BxTemplProfileGenerator(getLoggedId());

        switch($_REQUEST['show']) {
            case 'match':
                list($aDBTopMenu, $sResults) = $this->showMatchProfiles($iBlockID);
                break;
            case 'calendar':
                list($sResults, $aDBTopMenu, $sPagination, $sTopFilter) = $oProfile->GenProfilesCalendarBlock();
                break;
            default:
                $this->collectFilteredSettings();
                list($sResults, $aDBTopMenu, $sPagination, $sTopFilter) = $oProfile->GenSearchResultBlock($this->oPF->aBlocks, $aRequestParams, $this->aFilterSortSettings, 'search.php');
                break;
        }

        return array($sTopFilter . $sResults . $sPagination, $aDBTopMenu, array(), $this->getTitle());
    }

    function getTitle()
    {
        $sHeaderTitle =  _t('_Search profiles');
        switch($_REQUEST['show']) {
            case 'match':
                $sHeaderTitle = _t('_Match');
                break;
            /*case 'online':
                $sHeaderTitle = _t('_Online');
                break;*/
            case 'featured':
                $sHeaderTitle = _t('_Featured');
                break;
            case 'top_rated':
                $sHeaderTitle = _t('_Top Rated');
                break;
            case 'popular':
                $sHeaderTitle = _t('_Popular');
                break;
            case 'birthdays':
                $sHeaderTitle = _t('_Birthdays');
                break;
            case 'world_map':
                $sHeaderTitle = _t('_World_Map');
                break;
            case 'calendar':
                $sHeaderTitle = _t('_People_Calendar');
                break;
        }
        return $sHeaderTitle;
    }
}

check_logged();

$_page['name_index'] = 81;
$_page['css_name']   = 'search.css';

$oSearchView = new BxDolSearchPageView();
$sHeaderTitle = $oSearchView->getTitle();
$_page['header'] = $sHeaderTitle;
$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = $oSearchView->getCode();

PageCode();
exit;
