<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

// --------------- page variables and login

$_page['name_index'] 	= 12;
$_page['css_name']		= 'services.css';

check_logged();

$_page['header'] = _t( "_OUR_SERV" );
$_page['header_text'] = _t("_services");

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
    global $oTemplConfig;
    $ret = _t( "_SERV_DESC" );
    return DesignBoxContent( _t("_services"),$ret, $oTemplConfig -> PageCompThird_db_num );
}
