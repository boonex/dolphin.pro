<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
$sModule = isset($_REQUEST['module']) ? $_REQUEST['module'] : "";
$sAction = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";

if(($sModule == "mp3" && $sAction == "screenshot")
|| ($sModule == "board" && $sAction == "transmit")
|| ($sModule == "games" && $sAction == "makeShot")
|| ($sModule == "livecam" && $sAction == "channelShot")
|| ($sModule == "photo" && $sAction == "transmit"))
{
    define('BX_SECURITY_EXCEPTIONS', true);
    $aBxSecurityExceptions = array(
        'POST.data',
        'REQUEST.data',
    );
}

$sGlobalHeader = "modules/global/inc/header.inc.php";
if(!file_exists($sGlobalHeader)){ header("Location:install/index.php"); exit;}

require_once('../inc/header.inc.php');
require_once($sGlobalHeader);
require_once($sIncPath . "constants.inc.php");
require_once($sIncPath . "db.inc.php");
require_once($sIncPath . "xml.inc.php");
require_once($sIncPath . "functions.inc.php");
require_once($sIncPath . "apiFunctions.inc.php");
require_once($sIncPath . "customFunctions.inc.php");

$sModule = empty($sModule) || !secureCheckWidgetName($sModule) ? GLOBAL_MODULE : $sModule;

$sContents = "";
$sContentsType = CONTENTS_TYPE_XML;

if($sModule == GLOBAL_MODULE) {
    require_once($sIncPath . "xmlTemplates.inc.php");
    require_once($sIncPath . "actions.inc.php");
} else {
    $sModuleIncPath = $sModulesPath . $sModule . "/inc/";
    require_once($sModuleIncPath . "header.inc.php");
    require_once($sModuleIncPath . "constants.inc.php");
    require_once($sModuleIncPath . "xmlTemplates.inc.php");
    require_once($sModuleIncPath . "customFunctions.inc.php");
    require_once($sModuleIncPath . "functions.inc.php");
    require_once($sModuleIncPath . "actions.inc.php");
}

switch($sContentsType) {
    case CONTENTS_TYPE_XML:
        //--- Print Results in XML Format ---//
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header ('Content-Type: application/xml');
        $sContents = "<?xml version='1.0' encoding='UTF-8'?>" . makeGroup($sContents);
        break;
    case CONTENTS_TYPE_SWF:
        header("Content-Type: application/x-shockwave-flash");
        break;
    default:
        break;
}
echo $sContents;
