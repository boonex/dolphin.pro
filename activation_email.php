<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

// --------------- page variables and login

$_page['name_index']	= 35;
$_page['css_name']		= 'activation_email.css';
$logged['member'] = member_auth(0);

$_page['header'] = _t( "_ACTIVATION_EMAIL_H" );
$_page['header_text'] = _t( "_ACTIVATION_EMAIL_H1" );

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode();

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode()
{
    $memberID = getLoggedId();
    $p_arr = getProfileInfo( $memberID ); //db_assoc_arr( "SELECT `Status` FROM `Profiles` WHERE `ID` = '$memberID'" );

    if ( $p_arr['Status'] != 'Unconfirmed' )
        return _t( "_NO_NEED_TO_CONFIRM_EMAIL" );
    else
        return activation_mail( $memberID );
}
