<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');

$iSndId = ( isset($_COOKIE['memberID']) && ($GLOBALS['logged']['member'] || $GLOBALS['logged']['admin']) ) ? (int) $_COOKIE['memberID'] : 0;
$sSndPassword = isset($_COOKIE['memberPassword']) ? $_COOKIE['memberPassword'] : '';
$iRspId = count($aRequest) >= 1 ? array_shift($aRequest) : 0;

$oMessenger = new BxMsgModule($aModule);
echo $oMessenger->getMessenger($iSndId, $sSndPassword, $iRspId);
