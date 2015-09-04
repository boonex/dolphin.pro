<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( './inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );

require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php' );

check_logged();

$_page['name_index'] 	= 81;

$sPageName = process_pass_data( $_GET['ID'] );

$oIPV = new BxDolPageView($sPageName);
if ($oIPV->isLoaded()) {
    $sPageTitle = htmlspecialchars($oIPV->getPageTitle());
    $_page['header'] 		= $sPageTitle;
    $_page['header_text'] 	= $sPageTitle;

    $_ni = $_page['name_index'];
    $_page_cont[$_ni]['page_main_code'] = $oIPV -> getCode();

    PageCode();
} else {
    $oSysTemplate->displayPageNotFound();
}
