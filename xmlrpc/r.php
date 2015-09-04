<?php

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_ROOT . 'xmlrpc/BxDolXMLRPCUtil.php' );

$sUser = bx_get('user');
$sPwd = bx_get('pwd');
$sUrl = rawurldecode(bx_get('url'));
$iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd);

if ($iId) {
    bx_login($iId);
    header("HTTP/1.1 301 Moved Permanently");     
    header("Location: " . BX_DOL_URL_ROOT . $sUrl);
    exit;
} else {
    $GLOBALS['oSysTemplate']->addCss('mobile.css');
    $aVars = array ('content' => $_page_cont[$_ni]['page_main_code']);
    $sOutput = $GLOBALS['oSysTemplate']->parseHtmlByName('mobile_box.html', $aVars); 
    $iNameIndex = 11;
    $_page['name_index'] = $iNameIndex;     
    $_page_cont[$iNameIndex]['page_main_code'] = '<div style="text-align:center;" class="bx-sys-mobile-padding">Access Denied</div>';
}

PageCode();

