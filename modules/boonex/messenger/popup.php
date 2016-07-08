<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../../../inc/header.inc.php');
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php');
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/messenger/classes/BxMsgModule.php');

$iSndId = isset($_COOKIE['memberID']) ? (int)$_COOKIE['memberID'] : 0;
$sSndPassword = isset($_COOKIE['memberPassword']) ? $_COOKIE['memberPassword'] : '';
$iRspId = isset($_GET['rspId']) ? (int)$_GET['rspId'] : 0;

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('messenger');

$oMessenger = new BxMsgModule($aModule);
echo $oMessenger->getMessenger($iSndId, $sSndPassword, $iRspId);
