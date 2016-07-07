<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

if(empty($GLOBALS['sModule'])) $GLOBALS['sModule'] = "mp3";
$GLOBALS['sModuleUrl'] = $GLOBALS['sModulesUrl'] . $GLOBALS['sModule'] . "/";
$GLOBALS['sFilesDir'] = "files/";
$GLOBALS['sFilesUrl'] = $GLOBALS['sModuleUrl'] . $GLOBALS['sFilesDir'];
$GLOBALS['sFilesPathMp3'] = $GLOBALS['sModulesPath'] . $GLOBALS['sModule'] . "/" . $GLOBALS['sFilesDir'];
$sServerApp = "video";
$sStreamsFolder = "streams/";
