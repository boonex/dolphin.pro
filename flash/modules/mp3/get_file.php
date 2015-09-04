<?php
require_once("../../../inc/header.inc.php");
require_once($sIncPath . "customFunctions.inc.php");

$bResult = false;
$sId = (int)$_GET["id"];
$sToken = process_db_input($_GET["token"]);
$sFile = "files/" . $sId . "." . (isset($_GET["ext"]) && preg_match('/^[0-9a-z]+$/', $_GET["ext"]) ? $_GET["ext"] : "mp3");
$sType = "audio/" . (isset($_GET["ext"]) && preg_match('/^[0-9a-z]+$/', $_GET["ext"]) ? $_GET["ext"] : "mpeg");

if(!empty($sId) && !empty($sToken) && file_exists($sFile)) {
    require_once($sIncPath . "db.inc.php");
    $sId = getValue("SELECT `ID` FROM `RayMp3Tokens` WHERE `ID`='" . $sId . "' AND `Token`='" . $sToken . "' LIMIT 1");
    $bResult = !empty($sId);
}

if($bResult) {
    require_once($sIncPath . "functions.inc.php");
    smartReadFile($sFile, $sFile, $sType);
} else
    readfile($sFileErrorPath);
