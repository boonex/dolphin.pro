<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

// --------------- page variables and login
$_page['name_index'] 	= 18;

check_logged();

$_page['header'] = _t( "_TERMS_OF_USE_H" );
$_page['header_text'] = _t( "_TERMS_OF_USE_H1" );
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
    global $oTemplConfig, $site;
    return DesignBoxContent( _t( "_TERMS_OF_USE_H1" ), _t("_TERMS_OF_USE", $site['title'], BX_DOL_URL_ROOT), $oTemplConfig -> PageCompThird_db_num);
}
