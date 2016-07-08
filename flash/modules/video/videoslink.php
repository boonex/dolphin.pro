<?php
$sGlobalHeader = "../global/inc/header.inc.php";
require_once("../../../inc/header.inc.php");
require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolPermalinks.php");
require_once($sGlobalHeader);
require_once($sIncPath . "db.inc.php");
require_once($sIncPath . "customFunctions.inc.php");

$sId = (int)$_GET["id"];
$oDolPermalinks = new BxDolPermalinks();
$sNick = getValue("SELECT `NickName` FROM `Profiles` WHERE `ID`=" . $sId);
header("Location: " . $sRootURL . $oDolPermalinks->permalink("modules?r=videos/") . "albums/browse/owner/" . $sNick);
