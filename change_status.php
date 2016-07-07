<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'tags.inc.php' );

check_logged();
$iLoggedID = getLoggedId();

if (isset($_GET['action']) && $_GET['action']=='get_prof_status_mess') {
    if ($iLoggedID) {
        bx_import( 'BxDolUserStatusView' );

        header('Content-Type: text/html; charset=utf-8');
        echo BxDolUserStatusView::getStatusPageLight($iLoggedID);
    }
    exit;
}
$sAction = bx_get('action');
if ($sAction!== false && $sAction=='get_prof_comment_block') {
    $iProfileID = (int)bx_get('id');
    if ($iProfileID) {
        $sCloseC = _t('_Close');
        bx_import( 'BxTemplCmtsView' );
        $oCmtsView = new BxTemplCmtsView ('profile', $iProfileID);
        if (!$oCmtsView->isEnabled()) exit;

        $sCloseImg = getTemplateImage('close.gif');
        $sCaptionItem = <<<BLAH
<div class="dbTopMenu">
    <i class="login_ajx_close sys-icon times"></i>
</div>
BLAH;
        $sCommentsBlock = $GLOBALS['oFunctions']->transBox(
            DesignBoxContent(_t('_Comments'), $oCmtsView->_getPostReplyBox(), 1, $sCaptionItem), false
        );

        echo <<<EOF
<style>
    div.cmt-post-reply {
        position: relative;
    }
</style>
{$sCommentsBlock}
EOF;
    }
    exit;
}

// --------------- page variables and login

$_page['name_index']	= 36;

$logged['member'] = member_auth(0);

$_page['header'] = _t( "_CHANGE_STATUS_H" );
$_page['header_text'] = _t( "_CHANGE_STATUS_H1", $site['title'] );

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode($iLoggedID);

// --------------- [END] page components
$GLOBALS['oTopMenu']->setCustomSubHeader(_t( "_CHANGE_STATUS_H" ));
PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode($iLoggedID)
{
    $member['ID'] = (int)$iLoggedID;
    $p_arr = getProfileInfo( $member['ID'] );

    if ( $_POST['CHANGE_STATUS'] ) {
        $sStatus = "";
        switch( $_POST['CHANGE_STATUS'] ) {
            case 'SUSPEND':
                if ( $p_arr['Status'] == 'Active' )
                    $sStatus = "Suspended";
            break;

            case 'ACTIVATE':
                if ( $p_arr['Status'] == 'Suspended' )
                    $sStatus = "Active";
            break;
        }

        if (!empty($sStatus))
            bx_admin_profile_change_status ($member['ID'], $sStatus);

        $p_arr = getProfileInfo( $member['ID'] );
    }

    $aData = array(
        'profile_status_caption' => _t("_Profile status"),
        'status' => $p_arr['Status'],
        'status_lang_key' => _t('__' . $p_arr['Status']),
    );
    $aForm = array(
        'form_attrs' => array (
            'action' =>  BX_DOL_URL_ROOT . 'change_status.php',
            'method' => 'post',
            'name' => 'form_change_status'
        ),

        'inputs' => array(
            'status' => array (
                'type'     => 'hidden',
                'name'     => 'CHANGE_STATUS',
                'value'    => '',
            ),
            'subscribe' => array (
                'type'     => 'submit',
                'name'     => 'subscribe',
                'value'    => '',
            ),
        ),
    );
    switch ($p_arr['Status']) {
        case 'Active':
            $aForm['inputs']['status']['value'] = 'SUSPEND';
            $aForm['inputs']['subscribe']['value'] = _t('_Suspend account');
            $oForm = new BxTemplFormView($aForm);
            $aData['form'] = $oForm->getCode();
            $aData['message'] = _t("_PROFILE_CAN_SUSPEND");
            break;
        case 'Suspended':
            $aForm['inputs']['status']['value'] = 'ACTIVATE';
            $aForm['inputs']['subscribe']['value'] = _t('_Activate account');
            $oForm = new BxTemplFormView($aForm);
            $aData['form'] = $oForm->getCode();
            $aData['message'] = _t("_PROFILE_CAN_ACTIVATE");
            break;
        default:
            $aData['message'] = _t("_PROFILE_CANT_ACTIVATE/SUSPEND");
            $aData['form'] = '';
            break;
    }
    return $GLOBALS['oSysTemplate']->parseHtmlByName('change_status.html', $aData);
}
