<?php
$bResult = false;
$sId = (int)$_GET["id"];
$sFile = "files/" . $sId . ".m4v";

require_once("../../../inc/header.inc.php");

if(!empty($sId) && file_exists($sFile)) {
    require_once($sIncPath . "constants.inc.php");
    require_once($sIncPath . "xml.inc.php");
    require_once($sIncPath . "functions.inc.php");
    require_once($sIncPath . "apiFunctions.inc.php");
    $bResult = getSettingValue("video_comments", "saveMobile") == TRUE_VAL;
}

if($bResult) {
    require_once($sIncPath . "functions.inc.php");
    smartReadFile($sFile, $sFile, "video/mp4");
} else
    readfile($sFileErrorPath);
