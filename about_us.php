<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

// --------------- page variables and login

$_page['name_index']	= 0;
$_page['css_name']		= 'about_us.css';

check_logged();

$_page['header'] = _t( "_ABOUT_US_H" );
$_page['header_text'] = _t('_About Us');

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompMainCode();

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompMainCode()
{
    global $oTemplConfig;
    $ret = _t( "_ABOUT_US" );
    return DesignBoxContent( _t("_About Us"), $ret, $oTemplConfig -> PageCompThird_db_num );
}
