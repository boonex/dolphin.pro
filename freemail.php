<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php' );

// --------------- page variables and login

$_page['name_index'] 	= 44;
$_page['css_name']		= 'freemail.css';

$_page['header'] = _t( "_FREEMAIL_H" );

$logged['member'] = member_auth(0, false);

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = DesignBoxContent( $_page['header'], PageCompPageMainCode(), 1);
$_page_cont[$_ni]['body_onload'] = '';

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode()
{
    global $_page;

    //define ajax mode
    $bAjxMod = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        &&  $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

    $member['ID'] = getLoggedId();
    if (!isset($_POST['ID']))
        return _t_err( "_No member specified" );

    $ID = getID($_POST['ID'], 0);
    if(!$ID)
        return _t_err("_PROFILE_NOT_AVAILABLE");

    $profile = getProfileInfo( $ID );

    // Check if member can get email ADD CART CHECK HERE
    $check_res = checkAction( $member['ID'], ACTION_ID_GET_EMAIL );
    if($check_res[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED) {
        if($bAjxMod)
            showAjaxModeResult($check_res[CHECK_ACTION_MESSAGE], $ID);

        return '<div class="soundPop">' . $check_res[CHECK_ACTION_MESSAGE] . '</div>';
    }

    // Check if profile found

    if( !$profile ) {
        $ret = _t_err("_PROFILE_NOT_AVAILABLE");
        return $ret;
    }

    $action_result = "";
    $get_result = MemberFreeEmail( $member['ID'], $profile );

    switch ( $get_result ) {
        case 7:
            $action_result = _t_err( "_PROFILE_NOT_AVAILABLE" );
            break;
        case 13:
            $action_result = _t_err( "_YOUR PROFILE_IS_NOT_ACTIVE" );
            break;
        case 20:
            $action_result = _t_err( "_FREEMAIL_NOT_ALLOWED" );
            break;
        case 21:
            $action_result = _t_err( "_FREEMAIL_ALREADY_SENT", $ID );
            break;
        case 25:
            $action_result = _t_err( "_FREEMAIL_BLOCK", $ID );
            break;
        case 44:
            $action_result = _t_err( "_FREEMAIL_NOT_KISSED", $ID );
            break;
        case 45:
            $action_result = _t_err("_FREEMAIL_ERROR");
            break;
        default:
            $action_result = _t( "_FREEMAIL_SENT", $profile['NickName'] );
            break;
    }

    if ( $get_result ) {
        $_page['header_text'] = _t( "_Contact information not sent" );
    } else {
        $_page['header_text'] = _t( "_Contact information sent" );
    }

    $ret = '<div class="soundPop">' . $action_result . '</div>' . "\n";

    if($bAjxMod)
        showAjaxModeResult($action_result, $ID);

    return $ret;
}
function showAjaxModeResult($sMessage, $iId)
{
    header('Content-Type: text/html; charset=utf-8');
    echo MsgBox($sMessage) . genAjaxyPopupJS($iId);
    exit;
}
function MemberFreeEmail( $recipientID, $profile )
{
    global $site;
    $anon_mode = getParam('anon_mode');

    $recipientID = (int)$recipientID;
    $aRecipientArr = db_arr( "SELECT `Email` FROM `Profiles` WHERE `ID` = '$recipientID' AND `Status` = 'Active'", 0 );

    if (isBlocked($profile['ID'], $recipientID)) {
        return 25;
    }

    if ( !db_arr( "SELECT `ID` FROM `Profiles` WHERE `ID` = '{$profile['ID']}' AND `Status` = 'Active'", 0 ) ) {
        return 7;
    }

    if ($anon_mode) {
        return 20;
    }

    $rEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $rEmailTemplate -> getTemplate( 't_FreeEmail', $recipientID ) ;

    if ( $recipientID ) {
        $recipient = $aRecipientArr['Email'];
    } else {
        if ( $_GET['Email'] )
            $recipient = $_GET['Email'];
        else
            return 45;
    }

    $contact_info = "Email: {$profile['Email']}";
    if ( strlen( $profile['Phone'] ) )
        $contact_info .= "\nPhone: {$profile['Phone']}";
    if ( strlen( $profile['HomeAddress'] ) )
        $contact_info .= "\nHomeAddress: {$profile['HomeAddress']}";
    if ( strlen( $profile['HomePage'] ) )
        $contact_info .= "\nHomePage: {$profile['HomePage']}";
    if ( strlen( $profile['IcqUIN'] ) )
        $contact_info .= "\nICQ: {$profile['IcqUIN']}";

    $aPlus = array();
    $aPlus['profileContactInfo'] = $contact_info;
    $aPlus['profileNickName'] = getNickName($profile['ID']);
    $aPlus['profileID'] = $profile['ID'];

    $mail_ret = sendMail( $aRecipientArr['Email'], $aTemplate['Subject'], $aTemplate['Body'], $recipientID, $aPlus, 'html', false, true );

    if ( $mail_ret )
        // Perform action
        checkAction( $memberID, ACTION_ID_GET_EMAIL, true );
    else
        return 10;

    return 0;
}
