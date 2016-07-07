<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function removeFiles($sId)
{
    global $sFilesPath;
    @unlink($sFilesPath . $sId . IMAGE_EXTENSION);
    @unlink($sFilesPath . $sId . THUMB_FILE_NAME . IMAGE_EXTENSION);
}

function photo_getEmbedThumbnail($sUserId, $sImageUrl)
{
    global $sFilesPath;
    global $sFilesUrl;
    global $sFfmpegPath;

    $sFilePath = $sFilesPath . $sUserId . IMAGE_EXTENSION;
    @copy($sImageUrl, $sFilePath);
    @chmod($sFilePath, 0666);
    $sCommand = $sFfmpegPath . " -y -i " . $sFilePath . " -ss 0 -vframes 1 -an -f image2 " . $sFilePath;
    @popen($sCommand, "r");
    if(file_exists($sFilePath) && filesize($sFilePath) > 0)
        return photo_getRecordThumbnail($sUserId);
    else
        return false;
}

function photo_getRecordThumbnail($sUserId)
{
    global $sFilesPath;
    global $sFilesUrl;
    global $sFfmpegPath;

    $sFileName = $sUserId . THUMB_FILE_NAME . IMAGE_EXTENSION;
    @unlink($sFilesPath . $sFileName);
    $sCommand = $sFfmpegPath . " -y -i " . $sFilesPath . $sUserId . IMAGE_EXTENSION . " -s 64x64 -ss 0 -vframes 1 -an -f image2 " . $sFilesPath . $sFileName;
    @popen($sCommand, "r");
    if(file_exists($sFilesPath . $sFileName))
        return $sFilesUrl . $sFileName;
    else
        return false;
}
