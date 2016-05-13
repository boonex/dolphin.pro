<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function vcPrepareCommand($sTemplate, $aOptions)
{
    foreach($aOptions as $sKey => $sValue)
        $sTemplate = str_replace("#" . $sKey . "#", $sValue, $sTemplate);
    return $sTemplate;
}

function vcUsex264()
{
    global $sModule;
    return getSettingValue($sModule, "usex264") == TRUE_VAL;
}

function uploadFile($sFilePath, $sUserId)
{
    global $sModule;
    global $sFilesPath;

    $sTempFileName = $sFilesPath . $sUserId . VC_TEMP_FILE_NAME;
    @unlink($sTempFileName);

    if(is_uploaded_file($sFilePath)) {
        move_uploaded_file($sFilePath, $sTempFileName);
        @chmod($sTempFileName, 0666);
        if(file_exists($sTempFileName) && filesize($sTempFileName)>0) {
            $sDBModule = DB_PREFIX . ucfirst($sModule);
            getResult("INSERT INTO `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . VC_STATUS_PENDING . "'");
            $sFileId = getLastInsertId();
            rename($sTempFileName, $sFilesPath . $sFileId);
            return $sFileId;
        }
    }
    return false;
}

function publishRecordedVideoFile($sUserId)
{
    global $sModule;
    global $sFilesPath;

    $sPlayFile = $sFilesPath . $sUserId . VC_TEMP_FILE_NAME . VC_FLV_EXTENSION;
    if(file_exists($sPlayFile) && filesize($sPlayFile)>0) {
        $sDBModule = DB_PREFIX . ucfirst($sModule);
        getResult("INSERT INTO `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Owner`='" . $sUserId .
"', `Status`='" . VC_STATUS_PENDING . "'");
        $sFileId = getLastInsertId();
        rename($sPlayFile, $sFilesPath . $sFileId);
        @rename($sFilesPath . $sUserId . VC_TEMP_FILE_NAME . VC_IMAGE_EXTENSION, $sFilesPath . $sFileId . VC_IMAGE_EXTENSION);
        @rename($sFilesPath . $sUserId . VC_TEMP_FILE_NAME . VC_THUMB_FILE_NAME . VC_IMAGE_EXTENSION, $sFilesPath . $sFileId . VC_THUMB_FILE_NAME . VC_IMAGE_EXTENSION);
        return $sFileId;
    } else return false;
}

function initVideoFile($sId, $sTitle, $sCategory, $sTags, $sDesc)
{
    global $sModule;
    $oDb = BxDolDb::getInstance();

    $sDBModule = DB_PREFIX . ucfirst($sModule);

    getResult("UPDATE `" . $sDBModule . "Files` SET `Categories`='" . $sCategory . "', `Title`='" . $sTitle . "', `Tags`='" . $sTags . "', `Description`='" . $sDesc . "' WHERE `ID`='" . $sId . "'");
    return $oDb->getAffectedRows() > 0 ? true : false;
}

function _getVideoSize($sInputFile)
{
    global $sFilesPath;

    if(!file_exists($sInputFile) || filesize($sInputFile)==0) {
        if(strpos($sInputFile, $sFilesPath) === FALSE) return $sInputFile;
        else return VC_VIDEO_SIZE_16_9;
    }

    $sFile = $sFilesPath . time() . VC_IMAGE_EXTENSION;
    $sTmpl = vcPrepareCommand($GLOBALS['aConvertTmpls']['image'], array("input" => $sInputFile, "size" => "", "second" => 0, "output" => $sFile));
    if(convertFile($sFile, $sTmpl)) {
        $aSize = getimagesize($sFile);
        @unlink($sFile);
        $iRelation = $aSize[0]/$aSize[1];
        $i169Dif = abs($iRelation - 16/9);
        $i43Dif = abs($iRelation - 4/3);

        if($i169Dif > $i43Dif) return VC_VIDEO_SIZE_4_3;
        else return VC_VIDEO_SIZE_16_9;
    }
    return VC_VIDEO_SIZE_16_9;
}

function _getConverterTmpl($sInputFile, $sSize, $bSound = true)
{
    global $sModule;

    $bUsex264 = vcUsex264();
    if($bSound)
        $sSound = $bUsex264 ? " -acodec aac -strict experimental -b:a 128k -ar 44100 " : " -acodec libmp3lame -b:a 128k -ar 44100 ";
    else
        $sSound = " -an ";

    return vcPrepareCommand($GLOBALS['aConvertTmpls'][$bUsex264 ? 'playX264' : 'play'], array("input" => $sInputFile, "bitrate" => _getVideoBitrate(), "size" => _getVideoSize($sSize), "audio_options" => $sSound));
}

function _getVideoBitrate()
{
    global $sModule;

    $iBitrate = (int)getSettingValue($sModule, "bitrate");
    if(!$iBitrate)
        $iBitrate = 512;

    return $iBitrate;
}

function convertFile($sFile, $sCommand)
{
    popen($sCommand, "r");
    if(file_exists($sFile))
        @chmod($sFile, 0666);
    return file_exists($sFile) && filesize($sFile) > 0;
}

function _convertMain($sId, $sTmpl = "")
{
    global $sFilesPath;
    global $sModule;

    $sTempFile = $sFilesPath . $sId;
    $sResultFile = $sTempFile . (vcUsex264() ? VC_M4V_EXTENSION : VC_FLV_EXTENSION);

    $bResult = true;
    if(!file_exists($sResultFile) || filesize($sResultFile)==0) {
        if(empty($sTmpl))
            $sTmpl = _getConverterTmpl($sTempFile, $sTempFile, true);
        $sTmpl = vcPrepareCommand($sTmpl, array("output" => $sResultFile));
        $bResult = convertFile($sResultFile, $sTmpl);
        if(!$bResult) {
            $sTmpl = _getConverterTmpl($sTempFile, $sTempFile, false);
            $sTmpl = vcPrepareCommand($sTmpl, array("output" => $sResultFile));
            $bResult = convertFile($sResultFile, $sTmpl);
        }
    }
    if($bResult && vcUsex264())
        $bResult = moveMp4Meta($sResultFile);
    @chmod($sResultFile, 0666);
    return $bResult && _grabImages($sResultFile, $sTempFile);
}

function _convert($sId)
{
    global $sModule;
    global $sFilesPath;

    $sTempFile = $sFilesPath . $sId;
    $sSourceFile = $sTempFile;

    $bUseX264 = vcUsex264();
    $sTmpl = vcPrepareCommand($GLOBALS['aConvertTmpls'][$bUseX264 ? "playX264" : "play"], array("bitrate" => _getVideoBitrate(), "audio_options" => $bUseX264 ? " -acodec aac -strict experimental -b:a 128k -ar 44100 " : " -acodec libmp3lame -b:a 128k -ar 44100 "));
    if(file_exists($sTempFile) && filesize($sTempFile)>0)
        $sTmpl = vcPrepareCommand($sTmpl, array("input" => $sTempFile, "size" => _getVideoSize($sTempFile)));
    else {
        $sSourceFile .= VC_FLV_EXTENSION;
        if(file_exists($sSourceFile) && filesize($sSourceFile)>0)
            $sTmpl = vcPrepareCommand($sTmpl, array("input" => $sSourceFile, "size" => _getVideoSize($sSourceFile)));
    }
    if(empty($sTmpl)) return false;

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . VC_STATUS_PROCESSING . "' WHERE `ID`='" . $sId . "'");

    $bResult = _convertMain($sId, $sTmpl);
    if(!$bResult) return false;

    $oAlert = new BxDolAlerts('bx_video_comments', 'convert', $sId, getLoggedId(), array(
        'result' => &$bResult,
        'ffmpeg' => $GLOBALS['sFfmpegPath'],
        'tmp_file' => $sTempFile,
        'bitrate' => _getVideoBitrate(),
        'size' => _getVideoSize($sTempFile),
    ));
    $oAlert->alert();

    if($bResult) {
        $sAutoApprove = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? VC_STATUS_APPROVED : VC_STATUS_DISAPPROVED;
        getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . $sAutoApprove . "' WHERE `ID`='" . $sId . "'");
    } else {
        getResult("UPDATE `" . $sDBModule . "Files` SET `Status`='" . VC_STATUS_FAILED . "' WHERE `ID`='" . $sId . "'");
    }
    _deleteTempFiles($sId);
    return $bResult;
}

function _grabImages($sInputFile, $sOutputFile, $iSecond = 0, $bForse = false)
{
    $sImageFile = $sOutputFile . VC_IMAGE_EXTENSION;
    $sThumbFile = $sOutputFile . VC_THUMB_FILE_NAME . VC_IMAGE_EXTENSION;

    if(!$bForse && file_exists($sImageFile) && filesize($sImageFile)>0) $bResult = true;
    else $bResult = convertFile($sImageFile, _getGrabImageTmpl($sInputFile, $sImageFile, "", $iSecond));
    if(!$bResult) return false;

    if(!$bForse && file_exists($sThumbFile) && filesize($sThumbFile)>0) $bResult = true;
    else $bResult = convertFile($sThumbFile, _getGrabImageTmpl($sInputFile, $sThumbFile, "-s " . VC_THUMB_SIZE, $iSecond));
    return $bResult;
}

function _getGrabImageTmpl($sInputFile, $sOutputFile, $sSize = "", $iSecond = 0)
{
    global $aConvertTmpls;

    return vcPrepareCommand($aConvertTmpls["image"], array("input" => $sInputFile, "second" => $iSecond, "size" => (empty($sSize) ? "" : $sSize), "output" => $sOutputFile));
}

function _deleteTempFiles($sUserId, $bSourceOnly = false)
{
    global $sFilesPath;

    $sTempFile = $sUserId . VC_TEMP_FILE_NAME;
    @unlink($sFilesPath . $sTempFile);
    if($bSourceOnly) return;
    @unlink($sFilesPath . $sTempFile . VC_IMAGE_EXTENSION);
    @unlink($sFilesPath . $sTempFile . VC_THUMB_FILE_NAME . VC_IMAGE_EXTENSION);
    @unlink($sFilesPath . $sTempFile . VC_FLV_EXTENSION);
    @unlink($sFilesPath . $sTempFile . VC_M4V_EXTENSION);
}

/**
* Delete file
* @param $sFile - file identificator
* @return $bResult - result of operation (true/false)
*/
function _deleteFile($sFile)
{
    global $sModule;
    global $sFilesPath;
    $sDBModule = DB_PREFIX . ucfirst($sModule);

    getResult("DELETE FROM `" . $sDBModule . "Files` WHERE `ID`='" . $sFile . "'");

    $sFileName = $sFilesPath . $sFile;
    @unlink($sFileName);
    @unlink($sFileName . VC_MOBILE_EXTENSION);
    $bResult =  (@unlink($sFileName . VC_FLV_EXTENSION) || @unlink($sFileName . VC_M4V_EXTENSION)) &&
                @unlink($sFileName . VC_IMAGE_EXTENSION) &&
                @unlink($sFileName . VC_THUMB_FILE_NAME . VC_IMAGE_EXTENSION);

    $oAlert = new BxDolAlerts('bx_video_comments', 'delete', $sFile, getLoggedId(), array(
        'result' => &$bResult,
        'files_path' => $sFilesPath,
    ));
    $oAlert->alert();

    return $bResult;
}

function _getToken($sId)
{
    global $sFilesPath;

    if(file_exists($sFilesPath . $sId . VC_FLV_EXTENSION) || file_exists($sFilesPath . $sId . VC_M4V_EXTENSION)) {
        $iCurrentTime = time();
        $sToken = md5($iCurrentTime);
        getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Tokens`(`ID`, `Token`, `Date`) VALUES('" . $sId . "', '" . $sToken . "', '" . $iCurrentTime . "')");
        return $sToken;
    }
    return "";
}
