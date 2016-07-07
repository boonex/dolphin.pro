<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function uploadMusic($sFilePath, $sUserId, $sFileName, $bUploaded = true)
{
    global $sModule;
    global $sFilesPathMp3;

    $bMp3 = strtolower(substr($sFileName, -4)) == MP3_EXTENSION;
    $sTempFileName = $sFilesPathMp3 . $sUserId . TEMP_FILE_NAME;
    @unlink($sTempFileName);

    if(file_exists($sFilePath)) {
        if(is_uploaded_file($sFilePath))
            move_uploaded_file($sFilePath, $sTempFileName);
        else copy($sFilePath, $sTempFileName);
        @chmod($sTempFileName, 0644);
        if(file_exists($sTempFileName) && filesize($sTempFileName)>0) {
            $sDBModule = DB_PREFIX . ucfirst($sModule);
            $sStatus = STATUS_PENDING;
            $sExtension = "";
            /*
            if($bMp3) {
                $sStatus = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? STATUS_APPROVED : STATUS_DISAPPROVED;
                $sExtension = MP3_EXTENSION;
            }
             */
            $sUri = mp3_genUri($sFileName);
            $sUriPart = empty($sUri) ? "" : "`Uri`='" . $sUri . "', ";

            getResult("INSERT INTO `" . $sDBModule . "Files` SET `Title`='" . $sFileName . "', " . $sUriPart . "`Description`='" . $sFileName . "', `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . $sStatus . "'");
            $sFileId = getLastInsertId();
            rename($sTempFileName, $sFilesPathMp3 . $sFileId . $sExtension);
            return $sFileId;
        }
    }
    return false;
}

function checkRecord($sUserId)
{
    global $sFilesPathMp3;
    $sFilePath = $sFilesPathMp3 . $sUserId . TEMP_FILE_NAME . MP3_EXTENSION;
    return file_exists($sFilePath) && filesize($sFilePath) > 0;
}

function publishRecordedFile($sUserId, $sTitle, $sCategory, $sTags, $sDesc)
{
    global $sModule;
    global $sFilesPathMp3;

    $sTempFile = $sFilesPathMp3 . $sUserId . TEMP_FILE_NAME;
    $sPlayFile = $sTempFile . MP3_EXTENSION;
    if(file_exists($sPlayFile) && filesize($sPlayFile)>0) {
        $sDBModule = DB_PREFIX . ucfirst($sModule);
        $sUri = mp3_genUri($sTitle);
        $sUriPart = empty($sUri) ? "" : "`Uri`='" . $sUri . "', ";
        $sAutoApprove = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? STATUS_APPROVED : STATUS_DISAPPROVED;
        getResult("INSERT INTO `" . $sDBModule . "Files` SET `Categories`='" . $sCategory . "', `Title`='" . $sTitle . "', " . $sUriPart . "`Tags`='" . $sTags . "', `Description`='" . $sDesc . "', `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . $sAutoApprove . "'");
        $sFileId = getLastInsertId();
        rename($sPlayFile, $sFilesPathMp3 . $sFileId . MP3_EXTENSION);

        $aFilesConfig = BxDolService::call('sounds', 'get_files_config');
        foreach ($aFilesConfig as $a)
            if (isset($a['image']) && $a['image'])
                @rename($sTempFile . $a['postfix'], $sFilesPathMp3 . $sFileId . $a['postfix']);

        return $sFileId;
    } 

    return false;
}

function initFile($sId, $sTitle, $sCategory, $sTags, $sDesc)
{
    global $sModule;

    $oDb = BxDolDb::getInstance();

    $sUri = mp3_genUri($sTitle);
    $sUriPart = empty($sUri) ? "" : "`Uri`='" . $sUri . "', ";

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    
    getResult("UPDATE `" . $sDBModule . "Files` SET `Categories`= ?, `Title`= ?, " . $sUriPart . "`Tags`= ?, `Description`= ? WHERE `ID`= ?", [
        $sCategory,
        $sTitle,
        $sTags,
        $sDesc,
        $sId
    ]);

    return $oDb->getAffectedRows() > 0 ? true : false;
}

function convertMain($sId)
{
    global $sFilesPathMp3;
    global $sModule;
    global $sFfmpegPath;

    $sTempFile = $sFilesPathMp3 . $sId;

    if(!file_exists($sTempFile)) $sTempFile .= TEMP_FILE_NAME;
    $sPlayFile = $sTempFile . MP3_EXTENSION;

    $aBitrates = array(64, 96, 128, 192, 256);
    $iBitrate = (int)getSettingValue($sModule, "convertBitrate");
    if(!in_array($iBitrate, $aBitrates))
        $iBitrate = 128;

    $sCommand = $sFfmpegPath . " -y -i " . $sTempFile . " -vn -ar 44100 -ab " . $iBitrate . "k " . $sPlayFile;
    popen($sCommand, "r");

    $bResult = file_exists($sPlayFile) && filesize($sPlayFile) > 0;
    if($bResult) @unlink($sTempFile);

    $sOverride = false;
    $oAlert = new BxDolAlerts('bx_sounds', 'convert', $sId, getLoggedId(), array(
        'result' => &$bResult,
        'ffmpeg' => $sFfmpegPath,
        'tmp_file' => $sTempFile,
        'bitrate' => $iBitrate,
    ));
    $oAlert->alert();

    return $bResult;
}

function convert($sId)
{
    global $sModule;
    global $sFfmpegPath;
    global $sFilesPathMp3;

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . STATUS_PROCESSING . "' WHERE `ID`='" . $sId . "'");

    $bResult = convertMain($sId);

    if($bResult) {
        $sAutoApprove = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? STATUS_APPROVED : STATUS_DISAPPROVED;
        getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . $sAutoApprove . "' WHERE `ID`='" . $sId . "'");
    } else {
        getResult("UPDATE `" . $sDBModule . "Files` SET `Status`='" . STATUS_FAILED . "' WHERE `ID`='" . $sId . "'");
    }
    return $bResult;
}

function getDuration($sFile)
{
    return round(round(filesize($sFile) / (1024 * 1024), 3) * 60000, 0);
}

function renameFile($sUserId, $sFileId)
{
    global $sFilesPathMp3;

    $aFilesConfig = BxDolService::call('sounds', 'get_files_config');
    foreach ($aFilesConfig as $a)
        if (isset($a['image']) && $a['image'])
            @rename($sFilesPathMp3 . $sUserId . TEMP_FILE_NAME . $a['postfix'], $sFilesPathMp3 . $sFileId . $a['postfix']);

    $sTempFile = $sFilesPathMp3 . $sUserId . TEMP_FILE_NAME . MP3_EXTENSION;
    return rename($sTempFile, $sFilesPathMp3 . $sFileId . MP3_EXTENSION);
}

function deleteTempMp3s($sUserId, $bSourceOnly = false)
{
    global $sFilesPathMp3;

    $sTempFile = $sUserId . TEMP_FILE_NAME;
    @unlink($sFilesPathMp3 . $sTempFile);
    if($bSourceOnly) return;
    @unlink($sFilesPathMp3 . $sTempFile . MP3_EXTENSION);

    $aFilesConfig = BxDolService::call('sounds', 'get_files_config');
    foreach ($aFilesConfig as $a)
        if (isset($a['image']) && $a['image'])
            @unlink($sFilesPathMp3 . $sTempFile . $a['postfix']);
}

/**
* Delete file
* @param $sFile - file identificator
* @return $bResult - result of operation (true/false)
*/
function deleteFile($sFile)
{
    global $sFilesPathMp3;
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);

    getResult("DELETE FROM `" . $sDBModule . "Files` WHERE `ID`='" . $sFile . "'");
    getResult("DELETE FROM `" . $sDBModule . "PlayLists` WHERE `FileId`='" . $sFile . "'");
    mp3_parseTags($sFile);
    $sFileName = $sFilesPathMp3 . $sFile . MP3_EXTENSION;
    $bResult = @unlink($sFileName);
    return $bResult;
}

function getMp3Token($sId)
{
    global $sFilesPathMp3;
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);

    if(file_exists($sFilesPathMp3 . $sId . MP3_EXTENSION)) {
        $iCurrentTime = time();
        $sToken = md5($iCurrentTime);
        getResult("INSERT INTO `" . $sDBModule . "Tokens`(`ID`, `Token`, `Date`) VALUES('" . $sId . "', '" . $sToken . "', '" . $iCurrentTime . "')");
        return $sToken;
    }
    return "";
}
