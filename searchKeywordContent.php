<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'languages.inc.php');
bx_import('BxDolSearch');

$bAjaxMode = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ? true : false;
$aChoice = bx_get('section');
$oZ = new BxDolSearch($aChoice);
$sCode = $oZ->response();
if (mb_strlen($sCode) > 0)
    echo $sCode;
else
    echo $oZ->getEmptyResult();
