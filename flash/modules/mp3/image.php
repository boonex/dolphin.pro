<?php
$sFile = "files/" . (int)$_GET['id'] . ".jpg";
if(!file_exists($sFile))
    $sFile = "files/default.png";

header("Content-type: image/jpeg");
readfile($sFile);
