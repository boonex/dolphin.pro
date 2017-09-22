<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( './inc/header.inc.php' );

$sUrl = base64_decode(urldecode($_GET['url']));
$sProtoSite = bx_proto();
$sProtoImage = bx_proto($sUrl);

$sProtoHttp = 'http';
$sProtoHttps = 'https';

if($sProtoSite == $sProtoHttp || ($sProtoSite == $sProtoHttps && $sProtoImage == $sProtoHttps)) {
    header("Location: " . $sUrl);
    exit;
}

$sExt = strtolower(substr($sUrl, strripos($sUrl, '.') + 1));
switch ($sExt) {
    case 'png':
        $sType = 'image/x-png';
        break;
    case 'gif':
        $sType = 'image/gif';
        break;
    default:
        $sType = 'image/jpeg';
}


$sContent = bx_file_get_contents($sUrl);
header("Cache-Control: max-age=2592000");
header("Content-Type:" . $sType);
header("Content-Length: " . strlen($sContent));
echo $sContent;