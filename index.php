<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_INDEX_PAGE', 1);

if (!file_exists("inc/header.inc.php")) {
    $now = gmdate('D, d M Y H:i:s') . ' GMT';
    header("Expires: $now");
    header("Last-Modified: $now");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

    echo "It seems to be script is <b>not</b> installed.<br />\n";
    if ( file_exists( "install/index.php" ) ) {
        echo "Please, wait. Redirecting you to installation form...<br />\n";
        echo "<script language=\"Javascript\">location.href = 'install/index.php';</script>\n";
    }
    exit;
}

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'prof.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'membership_levels.inc.php' );

bx_import('BxDolPageView');
bx_import('BxDolProfileFields');
bx_import('BxTemplFormView');
bx_import('BxTemplVotingView');
bx_import("BxTemplIndexPageView");

//-- registration by invitation only --//;
if (!empty($_GET['idFriend']) && (int)$_GET['idFriend'] && getParam('reg_by_inv_only') == 'on') {
    setcookie('idFriend', (int)$_GET['idFriend'], 0, '/');
}

check_logged();

$_page['name_index'] = 1;

$oSysTemplate->setPageTitle($site['title']);
$oSysTemplate->setPageDescription(getParam("MetaDescription"));
$oSysTemplate->setPageMainBoxTitle($site['title']);
$oSysTemplate->addPageKeywords(getParam("MetaKeyWords"));
$oSysTemplate->addCss(array('index.css'));

$oIPV = new BxTemplIndexPageView();

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = $oIPV -> getCode();

PageCode();
