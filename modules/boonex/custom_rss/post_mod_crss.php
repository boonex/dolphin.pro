<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('../../../inc/header.inc.php');
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
bx_import('BxDolPaginate');

$logged['admin'] = member_auth( 1, true, true );

// $_page['css_name']	= 'browse.css';
// $_page['header'] = "Browse custom RSS";

if (isset($_POST['crsss'])  && is_array($_POST['crsss'])) {
    foreach($_POST['crsss'] as $iCrss) {
         switch (true) {
            case isset($_POST['action_delete']):
                 $iOldID = (int)$iCrss;
                $sRSSSQL = "DELETE FROM `bx_crss_main` WHERE `ID`='{$iOldID}'";
                db_res($sRSSSQL);
                break;
            case isset($_POST['action_approve']):
                $iOldID = (int)$iCrss;
                $sRSSSQL = "UPDATE `bx_crss_main` SET `status`='active' WHERE `ID`='{$iOldID}'";
                db_res($sRSSSQL);
                break;
            case isset($_POST['action_disapprove']):
                $iOldID = (int)$iCrss;
                $sRSSSQL = "UPDATE `bx_crss_main` SET `status`='passive' WHERE `ID`='{$iOldID}'";
                db_res($sRSSSQL);
                break;
        }
     }
}

///////////////pagination/////////////////////
$iTotalNum = db_value( "SELECT COUNT(*) FROM `bx_crss_main` WHERE `ProfileID`>0" );
if( !$iTotalNum )
    $sRSSs .= MsgBox(_t('_Empty'));
$iPerPage = (int)bx_get('per_page');
if (!$iPerPage)
    $iPerPage = 10;
$iCurPage = (int)bx_get('page');
if( $iCurPage < 1 )
    $iCurPage = 1;
$sLimitFrom = ( $iCurPage - 1 ) * $iPerPage;
$aSqlQuery = "LIMIT {$sLimitFrom}, {$iPerPage}";
///////////////eof pagination/////////////////////

$aManage = array('medID', 'medProfId', 'medTitle', 'medUri', 'medDate', 'medViews', 'medExt', 'Approved');

if ($iTotalNum > 0) {
    $sMemberRSSSQL = "SELECT * FROM `bx_crss_main` {$aSqlQuery}";
    $vMemberRSS = db_res($sMemberRSSSQL);

    while( $aRSSInfo = $vMemberRSS->fetch() ) {
        $iRssID = (int)$aRSSInfo['ID'];
        $sRssUrl = process_line_output($aRSSInfo['RSSUrl']);
        $sRssDesc = process_line_output($aRSSInfo['Description']);
        $sRssStatus = _t('_crss_status_' . process_line_output($aRSSInfo['Status']));
        $sRssUrlJS = addslashes(htmlspecialchars($sRssUrl));
        $sStatusColor = ($aRSSInfo['Status']=='active') ? 'green' : 'red';

        $sRSSs .= <<<EOF
<div class="bx-def-margin-sec-top-auto" style="position:relative; padding-left:30px;">
    <div class="bx_sys_unit_checkbox bx-def-round-corners">
        <input id="ch{$iRssID}" type="checkbox" value="{$iRssID}" name="crsss[]" />
    </div>
    <div class="bx-def-font-h3" >
        {$sRssUrl}
    </div>
    <div>
        <span style="color:$sStatusColor;">$sRssStatus</span> <span class="sys-bullet"></span> {$sRssDesc}
    </div>
</div>
EOF;
    }

    $sRequest = bx_html_attribute($_SERVER['PHP_SELF']) . '?page={page}&per_page={per_page}';

    ///////////////pagination/////////////////////
    // gen pagination block ;
    $oPaginate = new BxDolPaginate
    (
        array
        (
            'page_url'	=> $sRequest,
            'count'		=> $iTotalNum,
            'per_page'	=> $iPerPage,
            'page'		=> $iCurPage,
        )
    );
    $sPagination = $oPaginate -> getPaginate();
    ///////////////eof pagination/////////////////////

    bx_import('BxTemplSearchResult');
    $oSearchResult = new BxTemplSearchResult();
    $sAdmPanel = $oSearchResult->showAdminActionsPanel('crss_box', array('action_approve' => '_Approve', 'action_disapprove' => '_Disapprove', 'action_delete' => '_Delete'), 'crsss');
    $sUrl = bx_html_attribute($_SERVER['PHP_SELF']);
    $sCode .= <<<EOF
<form action="{$sUrl}" method="post" name="ads_moderation">
    <div id="crss_box" class="bx-def-bc-padding">
        {$sRSSs}
    </div>
    {$sPagination}
    {$sAdmPanel}
</form>
EOF;
}

$sHeaderValue = _t('_crss_Manager');
$sCode = ($sCode == '') ? MsgBox(_t('_Empty')) : $sCode;
$sResult = DesignBoxAdmin($sHeaderValue, $sCode);

$iNameIndex = 9;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('common.css', 'forms_adv.css'),
    'header' => $sHeaderValue,
    'header_text' => $sHeaderValue
);
$_page_cont[$iNameIndex]['page_main_code'] = $sResult;
PageCodeAdmin();
