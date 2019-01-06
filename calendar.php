<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('./inc/header.inc.php');
require_once( BX_DIRECTORY_PATH_INC  . 'design.inc.php' );
require_once(BX_DIRECTORY_PATH_INC . 'admin.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');

bx_import('BxDolProfileFields');
bx_import('BxDolProfilesController');
bx_import("BxTemplProfileView");
bx_import("BxTemplProfileView");
bx_import("BxTemplSearchProfile");

check_logged();

$_page['name_index'] = 7;
$_page['css_name']   = 'browse.css';

$_page['header'] = _t('_People_Calendar');
$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = getBlockCode_Results(100);

PageCode();

function getBlockCode_Results($iBlockID)
{
    $sAction = strip_tags($_GET['action']);
    switch ($sAction) {
        case 'browse':
            $sCode = getProfilesByDate($_GET['date']);
            break;
        default:
            $sCode = getCalendar();
    }
    return $sCode;
}

function getProfilesByDate ($sDate)
{
    $sDate = strip_tags($sDate);
    $aDateParams = explode('/', $sDate);
    $oSearch = new BxTemplSearchProfile('calendar', (int)$aDateParams[0], (int)$aDateParams[1], (int)$aDateParams[2]);
    $oSearch -> aConstants['linksTempl']['browseAll'] = 'calendar.php?';

    $sCode = $oSearch->displayResultBlock();
    return $oSearch->displaySearchBox('<div class="search_container">'
        . $sCode . '</div>', $oSearch->showPagination(false, false, false));
}

function getCalendar ()
{
    bx_import("BxTemplProfileGenerator");
    $oProfile = new BxTemplProfileGenerator(getLoggedId());
    $mSearchRes = $oProfile->GenProfilesCalendarBlock();
    list($sResults, $aDBTopMenu, $sPagination, $sTopFilter) = $mSearchRes;
    return DesignBoxContent(_t('_People_Calendar'), $sResults, 1);
}
