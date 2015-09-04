<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

check_logged();

bx_import('BxDolPayments');
$sUrl = $oPayment = BxDolPayments::getInstance()->getCartUrl();

if(empty($sUrl))
	$oSysTemplate->displayPageNotFound();

header('Location: ' . $sUrl);
exit;