<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
$sFile = isset($_REQUEST['file']) ? process_db_input($_REQUEST['file']) : "0";
$sTitle = isset($_REQUEST['title']) ? process_db_input($_REQUEST['title']) : "Untitled";
$sTags = isset($_REQUEST['tags']) ? process_db_input($_REQUEST['tags']) : "";
$sDesc = isset($_REQUEST['desc']) ? process_db_input($_REQUEST['desc']) : "";
$sTime = isset($_REQUEST['time']) ? (int)$_REQUEST['time'] : 0;

$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "";
$sLanguage = isset($_REQUEST['language']) ? process_db_input($_REQUEST['language']) : "english";

switch ($sAction) {
    case 'getPlugins':
        $sFolder = "/plugins/";
        $sContents = "";
        $sPluginsPath = $sModulesPath . $sModule . $sFolder;
        if(is_dir($sPluginsPath)) {
            if($rDirHandle = opendir($sModulesPath . $sModule . $sFolder))
                while(false !== ($sPlugin = readdir($rDirHandle)))
                    if(strpos($sPlugin, ".swf") === strlen($sPlugin)-4)
                        $sContents .= parseXml(array(1 => '<plugin><![CDATA[#1#]]></plugin>'), $sModulesUrl . $sModule . $sFolder . $sPlugin);
            closedir($rDirHandle);
        }
        $sContents = makeGroup($sContents, "plugins");
        break;

    /**
    * gets skins
    */
    case 'getSkins':
        $sContents = printFiles($sModule, "skins", false, true);
        break;

    /**
    * Sets default skin.
    */
    case 'setSkin':
        setCurrentFile($sModule, $sSkin, "skins");
        break;

    /**
    * gets languages
    */
    case 'getLanguages':
        $sContents = printFiles($sModule, "langs", false, true);
        break;

    /**
    * Sets default language.
    */
    case 'setLanguage':
        setCurrentFile($sModule, $sLanguage, "langs");
        break;

    /**
    * Authorize user.
    */
    case 'userAuthorize':
        $sUser = isset($_REQUEST['user']) ? process_db_input($_REQUEST['user']) : "";
        $sOwner = empty($sId) ? $sUser : getValue("SELECT `Owner` FROM `" . MODULE_DB_PREFIX . "Files` WHERE `ID`='" . $sId . "'");

        if($sOwner == $sUser && loginUser($sUser, $sPassword) == TRUE_VAL)
             $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        else $sContents = parseXml($aXmlTemplates['result'], "msgAuthorizingUserError");
        break;

    /**
    * Get config
    */
    case 'config':
        $sFileName = $sModulesPath . $sModule . "/xml/config.xml";
        $rHandle = fopen($sFileName, "rt");
        $sContents = fread($rHandle, filesize($sFileName)) ;
        fclose($rHandle);
        $sContents = str_replace("#screenshotWidth#", SCREENSHOT_WIDTH, $sContents);
        $sContents = str_replace("#screenshotHeight#", SCREENSHOT_HEIGHT, $sContents);
        $sContents = str_replace("#filesUrl#", $sModuleUrl, $sContents);
        $sContents = str_replace("#serverUrl#", getRMSUrl($sServerApp), $sContents);
        break;

    case 'getFile':
        $aFile = getArray("SELECT * FROM `" . MODULE_DB_PREFIX . "Files` WHERE `ID` = '" . $sId . "' LIMIT 1");
        $sPlayFile = $sId . MP3_EXTENSION;
        $sGetFile = "get_file.php?id=" . $sId . "&token=" . getMp3Token($sId);
        $sSaveName = $aFile['Title'] . MP3_EXTENSION;

        $sMessage = "";
        $sStatus = FAILED_VAL;
        switch($aFile['Status']) {
            case STATUS_PENDING:
            case STATUS_PROCESSING:
                $sMessage = "msgFileNotProcessed";
                break;
            case STATUS_DISAPPROVED:
                if (!isAdmin()) {
                    $sMessage = "msgFileNotApproved";
                    break;           
                }     
            case STATUS_APPROVED:
                if(file_exists($sFilesPathMp3 . $sPlayFile)) {
                    $sStatus = SUCCESS_VAL;
                    break;
                }
            case STATUS_FAILED:
            default:
                $sMessage = "msgFileNotFound";
                break;
        }

        $sContents = parseXml($aXmlTemplates['result'], $sMessage, $sStatus);
        if($sStatus == SUCCESS_VAL) {
            $sImageFile = $GLOBALS['sFilesDir'] . $sId . SCREENSHOT_EXT;
            $bScreenshot = file_exists($sModuleUrl . $sImageFile) && filesize($sModuleUrl . $sImageFile) > 0;
            if(!$bScreenshot) {
                $aFilesConfig = BxDolService::call('sounds', 'get_files_config');
                $sImageFile = $GLOBALS['sFilesDir'] . $aFilesConfig['browse']['fallback'];
            }
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
            $sContents .= parseXml($aXmlTemplates['file'], $sId, $sGetFile, $sGetFile, $sImageFile, $aFile['Time'], $bScreenshot ? TRUE_VAL : FALSE_VAL, $sSaveName);
        }
        break;

    case 'getList':
        $sContents = makeGroup(mp3_getList($sId), "files");
        break;

    case 'processFile':
        $sTempFileName = $sId . TEMP_FILE_NAME;
        $sTempFile = $sFilesPathMp3 . $sTempFileName;
        @unlink($sTempFile);
        deleteTempMp3s($sId);
        $sRecordedFileUrl = getRMSUrl($sServerApp, true) . $sStreamsFolder . $sFile . ".flv";

        $sContents = parseXml($aXmlTemplates['result'], "msgProcessingError", FAILED_VAL);
        if(function_exists("curl_init")) {
            $fTemp = fopen($sTempFile, "w");
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $sRecordedFileUrl);
            curl_setopt($curl, CURLOPT_FILE, $fTemp);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_exec($curl);
            curl_close($curl);
            fclose($fTemp);
        } else @copy($sRecordedFileUrl, $sTempFile);

        if(file_exists($sTempFile) && filesize($sTempFile) > 0 && convertMain($sId, false)) {
            @unlink($sTempFile);
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
            $sContents .= parseXml($aXmlTemplates['file'], "0", $sId . TEMP_FILE_NAME . MP3_EXTENSION);
        } else deleteTempMp3s($sId);
        break;

    /**
    * Delete files (reported files)
    */
    case 'removeFiles':
        if($sFile == "")
            $sContents = parseXml($aXmlTemplates['result'], "msgErrorDelete", FAILED_VAL);
        elseif($sFile != "") {
            $aFiles = explode(",", $sFile);
            if(count($aFiles) > 0) {
                for($i=0; $i<count($aFiles); $i++)
                    $bResult = deleteFile($aFiles[$i]);
            }
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        }
        break;

    /**
    * Upload user's file
    */
    case 'uploadFile':
        $sContents = uploadMusic($_FILES['Filedata']['tmp_name'], $sId, $sFile);
        $sContentsType = "other";
        break;

    case 'initFile':
        $sContents = initFile($sId, $sTitle, $sCategory, $sTags, $sDesc);
        $sContentsType = "other";
        break;

    case 'publishRecordedFile':
        $sContents = publishRecordedFile($sId, $sTitle, $sCategory, $sTags, $sDesc);
        $sContentsType = "other";
        break;

    case 'removeTempFiles':
        deleteTempMp3s($sId);
        break;

    /**
    * set user's uploaded file time
    */
    case 'updateFileTime':
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Files` SET `Time`='" . $sTime . "' WHERE `ID`='" . $sId . "'");
        $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        break;

    case 'screenshot':
        //--- Prepare data ---//
        $iWidth = isset($_REQUEST['width']) ? (int)$_REQUEST['width'] : 0;
        $iHeight = isset($_REQUEST['height']) ? (int)$_REQUEST['height'] : 0;
        $sData = isset($_REQUEST['data']) ? process_db_input($_REQUEST['data']) : "";
        $aImageData = explode(',', $sData);
        $iLength = count($aImageData);
        for($i=0; $i<$iLength; $i++)
            $aImageData[$i] = base_convert($aImageData[$i], 36, 10);
        if($iLength != $iWidth * $iHeight || !function_exists("imagecreatetruecolor")) 
            break;

        //--- Create Image Resource ---//
        $rImage = @imagecreatetruecolor($iWidth, $iHeight);
        for ($i = 0, $y = 0; $y < $iHeight; $y++ )
            for ( $x = 0; $x < $iWidth; $x++, $i++)
                @imagesetpixel ($rImage, $x, $y, $aImageData[$i]);

        //--- Save image file ---//
        $sUser = process_db_input($_REQUEST['user']);
        if(empty($sId)) 
            $sId = $sUser . TEMP_FILE_NAME;

        $aFilesConfig = BxDolService::call('sounds', 'get_files_config');
        foreach ($aFilesConfig as $a) {
            if (!isset($a['image']) || !$a['image'])
                continue;
            $sFileName = $sFilesPathMp3 . $sId . $a['postfix'];
            @imagejpeg($rImage, $sFileName, 95);
            if (isset($a['w']) && $a['w'])
                imageResize($sFileName, $sFileName, $a['w'], isset($a['h']) && $a['h'] ? $a['h'] : $a['w'], true, isset($a['square']) && $a['square']);
        }

        break;

    case 'getToken':
        $sToken = getMp3Token($sId);
        if(empty($sToken))
            $sContents = parseXml($aXmlTemplates['result'], "msgFileNotFound", FAILED_VAL);
        else
            $sContents = parseXml($aXmlTemplates['result'], $sToken, SUCCESS_VAL);
        break;
}
