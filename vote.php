<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

check_logged();

bx_import("BxDolVoting");
$aSystems =& BxDolVoting::getSystems ();
$sSys = isset($_GET['sys']) ? $_GET['sys'] : false;
if ($sSys && isset($aSystems[$sSys])) {

    if ($aSystems[$sSys]['override_class_name']) {
        require_once (BX_DIRECTORY_PATH_ROOT . $aSystems[$sSys]['override_class_file']);
        $sClassName = $aSystems[$sSys]['override_class_name'];
        new $sClassName($_GET['sys'], (int)$_GET['id']);
    } else {
        new BxDolVoting($_GET['sys'], (int)$_GET['id']);
    }

}
