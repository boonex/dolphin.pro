<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

if(!isset($sRayHeaderPath)) $sRayHeaderPath = "modules/global/inc/header.inc.php";
if(!file_exists($sRayHeaderPath)) {
    header("Location:install/index.php");
    exit;
}

$sModule = isset($sModule) ? $sModule : $_REQUEST['module'];
$sApp = isset($sApp) ? $sApp : $_REQUEST['app'];

require_once('../inc/header.inc.php');
require_once($sIncPath . 'functions.inc.php');

if(secureCheckWidgetName($sModule) && file_exists($sRayHeaderPath) && !empty($sModule) && !empty($sApp) && secureCheckWidgetName($sApp)) {
    require_once(BX_DIRECTORY_PATH_INC . "db.inc.php");
    require_once(BX_DIRECTORY_PATH_INC . "utils.inc.php");
    require_once($sRayHeaderPath);
    require_once($sIncPath . "content.inc.php");
    require_once($sModulesPath . $sModule . "/inc/header.inc.php");
    require_once($sModulesPath . $sModule . "/inc/constants.inc.php");
} else exit;

$aParameters = Array();
foreach($aModules[$sApp]['parameters'] as $sParameter)
    $aParameters[$sParameter] = isset($$sParameter) ? $$sParameter : $_REQUEST[$sParameter];

echo getApplicationContent($sModule, $sApp, $aParameters);
