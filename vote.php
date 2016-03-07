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

$sSys = bx_get('sys');
$iId = (int)bx_get('id');

bx_import ('BxDolVoting');

if($sSys && $iId && ($oVoting = BxDolVoting::getObjectInstance($sSys, $iId))) {
    header('Content-Type: text/html; charset=utf-8');
    echo $oVoting->actionVote();
}
