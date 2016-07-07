<?php
require_once("../../../inc/header.inc.php");
require_once($sIncPath . "customFunctions.inc.php");

$bResult = false;
$sId = (int)$_GET["id"];
$sToken = process_db_input($_GET["token"]);
$sExt = isset($_GET["ext"]) && preg_match('/^[0-9a-z]+$/', $_GET["ext"]) ? $_GET["ext"] : (file_exists("files/" . $sId . ".m4v") ? "m4v" : "flv");
$sFile = "files/" . $sId . "." . $sExt;

if(!empty($sId) && !empty($sToken) && file_exists($sFile)) {
    require_once($sIncPath . "db.inc.php");
    $sId = getValue("SELECT `ID` FROM `RayVideoTokens` WHERE `ID`='" . $sId . "' AND `Token`='" . $sToken . "' LIMIT 1");
    $bResult = !empty($sId);
}

if($bResult) {
    require_once($sIncPath . "functions.inc.php");
    smartReadFile($sFile, $sFile, "video/" . ('m4v' == $sExt ? 'x-'.$sExt : $sExt));
} else
    readfile($sFileErrorPath);
