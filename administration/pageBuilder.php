<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array(
    'POST.Content',
    'REQUEST.Content',
);

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'images.inc.php' );
bx_import('BxDolPageViewAdmin');

$logged['admin'] = member_auth( 1, true, true );

$GLOBALS['oAdmTemplate']->addJsTranslation(array(
    '_adm_btn_Column', '_Are_you_sure', '_Empty'
));

$oPVAdm = new BxDolPageViewAdmin( 'sys_page_compose', 'sys_page_compose.inc' );
