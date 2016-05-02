<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function prepareCommand($sTemplate, $aOptions)
{
    foreach($aOptions as $sKey => $sValue)
        $sTemplate = str_replace("#" . $sKey . "#", $sValue, $sTemplate);
    return $sTemplate;
}

function usex264()
{
    global $sModule;
    return getSettingValue($sModule, "usex264") == TRUE_VAL;
}

function getEmbedThumbnail($sUserId, $sImageUrl, $aFilesConfig = array())
{
    global $sFilesPath;
    global $sFilesUrl;

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sFileName = $sUserId . TEMP_FILE_NAME;
    $sFilePath = $sFilesPath . $sFileName;
    copy($sImageUrl, $sFilePath . IMAGE_EXTENSION);
    @chmod($sFilePath, 0666);

    // generate tmp images
    if(!grabImages($sFilePath . IMAGE_EXTENSION, $sFilePath, 0, false, $aFilesConfig))
        return false;

    return $sFilesUrl . $sFileName . THUMB_FILE_NAME . IMAGE_EXTENSION;
}

function getRecordThumbnail($sUserId)
{
    global $sFilesPath;
    global $sFilesUrl;

    $sFileName = $sUserId . TEMP_FILE_NAME . THUMB_FILE_NAME . IMAGE_EXTENSION;
    if(file_exists($sFilesPath . $sFileName))
        return $sFilesUrl . $sFileName;
    else
        return false;
}

function embedVideo($sUserId, $sVideoId, $iDuration, $aFilesConfig = array())
{
    global $sFilesPath;
    global $sModule;

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    $sStatus = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? STATUS_APPROVED : STATUS_DISAPPROVED;
    getResult("INSERT INTO `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . $sStatus . "', `Source`='youtube', `Video`='" . $sVideoId . "', `Time`='" . ($iDuration * 1000) . "'");

    $sFileId = getLastInsertId();

    // rename tmp images into real ones
    foreach ($aFilesConfig as $a)
        if (isset($a['image']) && $a['image'])
            @rename($sFilesPath . $sUserId . TEMP_FILE_NAME . $a['postfix'], $sFilesPath . $sFileId . $a['postfix']);

    return $sFileId;
}

function recordVideo($sUserId, $aFilesConfig = array())
{
    global $sFilesPath;
    global $sModule;

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    getResult("INSERT INTO `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . STATUS_PENDING . "'");
    $sFileId = getLastInsertId();

    @rename($sFilesPath . $sUserId . TEMP_FILE_NAME . FLV_EXTENSION, $sFilesPath . $sFileId . FLV_EXTENSION);

    // rename tmp images into real ones
    foreach ($aFilesConfig as $a)
        if (isset($a['image']) && $a['image'])
            @rename($sFilesPath . $sUserId . TEMP_FILE_NAME . $a['postfix'], $sFilesPath . $sFileId . $a['postfix']);

    return $sFileId;
}

function uploadVideo($sFilePath, $sUserId, $isMoveUploadedFile = false, $sImageFilePath = '', $sFileName = '', $aFilesConfig = array())
{
    global $sModule;
    global $sFilesPath;

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sTempFileName = $sFilesPath . $sUserId . TEMP_FILE_NAME;
    @unlink($sTempFileName);
    if(file_exists($sFilePath) && filesize($sFilePath) > 0) {
        if(is_uploaded_file($sFilePath)) {
            move_uploaded_file($sFilePath, $sTempFileName);
        } else {
            @rename($sFilePath, $sTempFileName);
        }
        @chmod($sTempFileName, 0666);
        if(file_exists($sTempFileName) && filesize($sTempFileName)>0) {

            if(!grabImages($sTempFileName, $sTempFileName, 0, false, $aFilesConfig))
                return false;

            $sDBModule = DB_PREFIX . ucfirst($sModule);
            $sUri = video_genUri($sFileName);
            $sUriPart = empty($sUri) ? "" : "`Uri`='" . $sUri . "', ";

            getResult("INSERT INTO `" . $sDBModule . "Files` SET `Title`='" . $sFileName . "', " . $sUriPart .  "`Description`='" . $sFileName . "', `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . STATUS_PENDING . "'");
            $sFileId = getLastInsertId();
            rename($sTempFileName, $sFilesPath . $sFileId);

            foreach ($aFilesConfig as $a)
                if (isset($a['image']) && $a['image'])
                    @rename($sFilesPath . $sUserId . TEMP_FILE_NAME . $a['postfix'], $sFilesPath . $sFileId . $a['postfix']);

            return $sFileId;
        }
    }
    return false;
}

function publishRecordedVideo($sUserId, $sTitle, $sCategory, $sTags, $sDesc, $aFilesConfig = array())
{
    global $sModule;
    global $sFilesPath;

    $sPlayFile = $sFilesPath . $sUserId . TEMP_FILE_NAME . FLV_EXTENSION;
    if(file_exists($sPlayFile) && filesize($sPlayFile)>0) {

        if (!$aFilesConfig)
            $aFilesConfig = BxDolService::call('videos', 'get_files_config');

        $sDBModule = DB_PREFIX . ucfirst($sModule);
        $sUri = video_genUri($sTitle);
        $sUriPart = empty($sUri) ? "" : "`Uri`='" . $sUri . "', ";
        getResult("INSERT INTO `" . $sDBModule . "Files` SET `Categories`='" . $sCategory . "', `Title`='" . $sTitle . "', " . $sUriPart . "`Tags`='" . $sTags . "', `Description`='" . $sDesc . "', `Date`='" . time() . "', `Owner`='" . $sUserId . "', `Status`='" . STATUS_PENDING . "'");
        $sFileId = getLastInsertId();

        rename($sPlayFile, $sFilesPath . $sFileId);

        foreach ($aFilesConfig as $a)
            if (isset($a['image']) && $a['image'])
                @rename($sFilesPath . $sUserId . TEMP_FILE_NAME . $a['postfix'], $sFilesPath . $sFileId . $a['postfix']);

        return $sFileId;
    } 

    return false;
}

function initVideo($sId, $sTitle, $sCategory, $sTags, $sDesc)
{
    global $sModule;

    $oDb = BxDolDb::getInstance();

    $sUri = video_genUri($sTitle);
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

function getVideoSize($sInputFile)
{
    global $sFilesPath;

    if(!file_exists($sInputFile) || filesize($sInputFile)==0) {
        if(strpos($sInputFile, $sFilesPath) === FALSE) return $sInputFile;
        else return VIDEO_SIZE_16_9;
    }

    $sFile = $sFilesPath . time() . IMAGE_EXTENSION;
    $sTmpl = prepareCommand($GLOBALS['aConvertTmpls']['image'], array("input" => $sInputFile, "size" => "", "second" => 0, "output" => $sFile));
    if(convertVideoFile($sFile, $sTmpl)) {
        $aSize = getimagesize($sFile);
        @unlink($sFile);
        $iRelation = $aSize[0]/$aSize[1];
        $i169Dif = abs($iRelation - 16/9);
        $i916Dif = abs($iRelation - 9/16);
        $i43Dif = abs($iRelation - 4/3);

        if($i43Dif > $i916Dif) return VIDEO_SIZE_9_16;
        else if($i169Dif > $i43Dif) return VIDEO_SIZE_4_3;
        else return VIDEO_SIZE_16_9;
    }
    return VIDEO_SIZE_16_9;
}

function getConverterTmpl($sInputFile, $sSize, $bSound = true, $bRecorded = false)
{
    global $sModule;
    $bUsex264 = usex264();
    if($bSound)
        $sSound = $bUsex264 ? " -acodec aac -strict experimental -b:a 128k -ar 44100 " : " -acodec libmp3lame -b:a 128k -ar 44100 ";
    else
        $sSound = " -an ";

    return prepareCommand($GLOBALS['aConvertTmpls'][$bUsex264 ? 'playX264' : 'play'], array("input" => $sInputFile, "bitrate" => getVideoBitrate(), "size" => getVideoSize($sSize), "audio_options" => $sSound));
}

function getVideoBitrate()
{
    global $sModule;

    $iBitrate = (int)getSettingValue($sModule, "bitrate");
    if(!$iBitrate)
        $iBitrate = 512;

    return $iBitrate;
}

function convertVideoFile($sFile, $sCommand)
{
    popen($sCommand, "r");
    if(file_exists($sFile))
        @chmod($sFile, 0666);
    return file_exists($sFile) && filesize($sFile) > 0;
}

function convertMainVideo($sId, $sTmpl = "", $bRecorded = false)
{
    global $sFilesPath;
    global $sModule;

    $sTempFile = $sFilesPath . $sId;
    $sResultFile = $sTempFile . (usex264() ? M4V_EXTENSION : FLV_EXTENSION);

    $bResult = true;
    if(!file_exists($sResultFile) || filesize($sResultFile)==0) {
        if(empty($sTmpl))
            $sTmpl = getConverterTmpl($sTempFile, $sTempFile, true, $bRecorded);
        $sTmpl = prepareCommand($sTmpl, array("output" => $sResultFile));
        $bResult = convertVideoFile($sResultFile, $sTmpl);
        if(!$bResult) {
            $sTmpl = getConverterTmpl($sTempFile, $sTempFile, false);
            $sTmpl = prepareCommand($sTmpl, array("output" => $sResultFile));
            $bResult = convertVideoFile($sResultFile, $sTmpl);
        }
    }
    if($bResult && usex264())
        $bResult = moveMp4Meta($sResultFile);

    return $bResult && grabImages($sResultFile, $sTempFile);
}

function convertVideo($sId)
{
    global $sModule;
    global $sFilesPath;

    $sTempFile = $sFilesPath . $sId;
    $sSourceFile = $sTempFile;

    $bUseX264 = usex264();
    $sTmpl = prepareCommand($GLOBALS['aConvertTmpls'][$bUseX264 ? "playX264" : "play"], array("bitrate" => getVideoBitrate(), "audio_options" => $bUseX264 ? " -acodec aac -strict experimental -b:a 128k -ar 44100 " : "-acodec libmp3lame -b:a 128k -ar 44100 "));

    if(file_exists($sTempFile) && filesize($sTempFile)>0)
        $sTmpl = prepareCommand($sTmpl, array("input" => $sTempFile, "size" => getVideoSize($sTempFile)));
    else {
        $sSourceFile .= FLV_EXTENSION;
        if(file_exists($sSourceFile) && filesize($sSourceFile)>0)
            $sTmpl = prepareCommand($sTmpl, array("input" => $sSourceFile, "size" => getVideoSize($sSourceFile)));
    }
    if(empty($sTmpl)) return false;

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . STATUS_PROCESSING . "' WHERE `ID`='" . $sId . "'");

    $bResult = convertMainVideo($sId, $sTmpl);
    if(!$bResult) return false;

    $oAlert = new BxDolAlerts('bx_videos', 'convert', $sId, getLoggedId(), array(
        'result' => &$bResult,
        'ffmpeg' => $GLOBALS['sFfmpegPath'],
        'tmp_file' => $sTempFile,
        'bitrate' => getVideoBitrate(),
        'size' => getVideoSize($sTempFile),
    ));
    $oAlert->alert();

    if($bResult) {
        $sAutoApprove = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? STATUS_APPROVED : STATUS_DISAPPROVED;
        getResult("UPDATE `" . $sDBModule . "Files` SET `Date`='" . time() . "', `Status`='" . $sAutoApprove . "' WHERE `ID`='" . $sId . "'");
    } else {
        getResult("UPDATE `" . $sDBModule . "Files` SET `Status`='" . STATUS_FAILED . "' WHERE `ID`='" . $sId . "'");
    }
    deleteTempFiles($sId);
    return $bResult;
}

function grabImages($sInputFile, $sOutputFile, $iSecond = 0, $bForse = false, $aFilesConfig = array())
{
    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $fRatio = 0;
    foreach ($aFilesConfig as $a) {
        if (!isset($a['image']) || !$a['image'])
            continue;

        $sResize = '';
        if ($fRatio && isset($a['square']) && $a['square'] && isset($a['w']) && $a['w'] && $fRatio < 1)
            $sResize = "-vf crop=out_h=in_w -s {$a['w']}x{$a['w']}";
        elseif ($fRatio && isset($a['square']) && $a['square'] && isset($a['w']) && $a['w'])
            $sResize = "-vf crop=out_w=in_h -s {$a['w']}x{$a['w']}";
        elseif (isset($a['w']) && isset($a['h']) && $a['w'] && $a['h'])
            $sResize = "-s {$a['w']}x{$a['h']}";

        if ($sInputFile != $sOutputFile . $a['postfix'])
            @unlink($sOutputFile . $a['postfix']);

        if (!grabImage($sInputFile, $sOutputFile . $a['postfix'], $sResize, $iSecond, $bForse))
            return false;

        if (!$fRatio) {
            $aSize = getimagesize($sOutputFile . $a['postfix']);
            $fRatio = $aSize[0]/$aSize[1];
        }
    }

    return true;
}

function grabImage($sInputFile, $sOutputFile, $sSize = "", $iSecond = 0, $bForse = false)
{
	if(!$bForse && file_exists($sOutputFile) && filesize($sOutputFile) > 0)
		return true;

	bx_import('BxDolImageResize');
	$oImage = BxDolImageResize::instance();

	$bResult = true; 
	$aSeconds = $iSecond != 0 ? array($iSecond) : array(0, 3, 5, 0);
	foreach($aSeconds as $iSecond) {
		$bResult = convertVideoFile($sOutputFile, getGrabImageTmpl($sInputFile, $sOutputFile, $sSize, $iSecond));
		if(!$bResult)
			continue;

		$aRgb = $oImage->getAverageColor($sOutputFile);
		$fRgb = ($aRgb['r'] + $aRgb['g'] + $aRgb['b']) / 3;
		if($fRgb > 32 && $fRgb < 224)
			break;
	}

	return $bResult;
}

function getGrabImageTmpl($sInputFile, $sOutputFile, $sSize = "", $iSecond = 0)
{
    global $aConvertTmpls;

    return prepareCommand($aConvertTmpls["image"], array("input" => $sInputFile, "second" => $iSecond, "size" => (empty($sSize) ? "" : $sSize), "output" => $sOutputFile));
}

function deleteTempFiles($sUserId, $bSourceOnly = false, $aFilesConfig = array())
{
    global $sFilesPath;

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sTempFile = $sUserId . TEMP_FILE_NAME;
    @unlink($sFilesPath . $sUserId);
    @unlink($sFilesPath . $sTempFile);
    if($bSourceOnly) return;

    foreach ($aFilesConfig as $a)
        @unlink($sFilesPath . $sTempFile . $a['postfix']);
}

/**
 * Delete file
 * @param $sFile - file identificator
 * @return $bResult - result of operation (true/false)
 */
function deleteVideo($sFile, $aFilesConfig = array())
{
    global $sFilesPath;
    global $sModule;

    $oDb = BxDolDb::getInstance();

    if (!$aFilesConfig)
        $aFilesConfig = BxDolService::call('videos', 'get_files_config');

    $sDBModule = DB_PREFIX . ucfirst($sModule);
    getResult("DELETE FROM `" . $sDBModule . "Files` WHERE `ID`='" . $sFile . "'");
    if($oDb->getAffectedRows())
        video_parseTags($sFile);
    $sFileName = $sFilesPath . $sFile;
    @unlink($sFileName);

    $bResult = false;
    foreach ($aFilesConfig as $a)
        $bResult |= @unlink($sFileName . $a['postfix']);

    return $bResult;
}

function getToken($sId)
{
    global $sFilesPath;
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);

    if(file_exists($sFilesPath . $sId . FLV_EXTENSION) || file_exists($sFilesPath . $sId . M4V_EXTENSION)) {
        $iCurrentTime = time();
        $sToken = md5($iCurrentTime);
        getResult("INSERT INTO `" . $sDBModule . "Tokens`(`ID`, `Token`, `Date`) VALUES('" . $sId . "', '" . $sToken . "', '" . $iCurrentTime . "')");
        return $sToken;
    }
    return "";
}
