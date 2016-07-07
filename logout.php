<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

ob_start();
require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
ob_end_clean();

if (isset( $_COOKIE['memberID']) && isset($_COOKIE['memberPassword']))
    bx_logout();

$_page['name_index'] = 150;
$_page['css_name'] = '';

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = MsgBox(_t('_Please Wait'));
$_page_cont[$_ni]['url_relocate'] = $site['url'];

send_headers_page_changed();
PageCode();
