<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

if(empty($GLOBALS['sModule'])) $GLOBALS['sModule'] = "video_comments";
$GLOBALS['sModuleUrl'] = $GLOBALS['sModulesUrl'] . $GLOBALS['sModule'] . "/";
$GLOBALS['sFilesDir'] = "files/";
$GLOBALS['sFilesUrl'] = $GLOBALS['sModuleUrl'] . $GLOBALS['sFilesDir'];
$GLOBALS['sFilesPath'] = $GLOBALS['sModulesPath'] . $GLOBALS['sModule'] . "/" . $GLOBALS['sFilesDir'];
$GLOBALS['sServerApp'] = "video";
$GLOBALS['sStreamsFolder'] = "streams/";
$GLOBALS['aConvertTmpls'] = array(
    "playX264" => $GLOBALS['sFfmpegPath'] . " -y -i #input# -b:v #bitrate#k -vcodec libx264 -s #size# #audio_options# #output#",
    "play" => $GLOBALS['sFfmpegPath'] . " -y -i #input# -r 25 -b:v #bitrate#k -s #size# #audio_options# #output#",
    "image" => $GLOBALS['sFfmpegPath'] . " -y -i #input# #size# -ss #second# -vframes 1 -an -f image2 #output#",
);
