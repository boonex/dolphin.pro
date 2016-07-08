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
        $sContents = str_replace("#filesUrl#", $sModuleUrl, $sContents);
        $sContents = str_replace("#serverUrl#", getRMSUrl($sServerApp), $sContents);
        break;

    case 'getFile':
        $aFile = getArray("SELECT * FROM `" . MODULE_DB_PREFIX . "Files` WHERE `ID` = '" . $sId . "' LIMIT 1");
        $sExt = file_exists($sFilesPath . $sId . VC_M4V_EXTENSION) ? VC_M4V_EXTENSION : VC_FLV_EXTENSION;
        $sPlayFile = $sId . $sExt;
        $sGetFile = "get_file.php?id=" . $sId . "&token=" . _getToken($sId);
        $sSaveName = $aFile['Title'] . $sExt;
        $sImageFile = $GLOBALS['sFilesDir'] . $sId . VC_IMAGE_EXTENSION;

        $sMessage = "";
        $sStatus = FAILED_VAL;
        switch($aFile['Status']) {
            case VC_STATUS_DISAPPROVED:
                $sMessage = "msgFileNotApproved";
                break;
            case VC_STATUS_PENDING:
            case VC_STATUS_PROCESSING:
                $sMessage = "msgFileNotProcessed";
                break;
            case VC_STATUS_APPROVED:
                if(file_exists($sFilesPath . $sPlayFile)) {
                    $sStatus = SUCCESS_VAL;
                    break;
                }
            case VC_STATUS_FAILED:
            default:
                $sMessage = "msgFileNotFound";
                break;
        }

        $sContents = parseXml($aXmlTemplates['result'], $sMessage, $sStatus);
        if($sStatus == SUCCESS_VAL)
            $sContents .= parseXml($aXmlTemplates['file'], $sId, $sGetFile, $sGetFile, $sImageFile, $aFile['Time'], $sSaveName);
        break;

    /**
    * Get user's playlist by ID
    */
    case 'getList':
        $sContents = makeGroup("", "files");
        break;

    case 'processFile':
        $sTempFileName = $sId . VC_TEMP_FILE_NAME;
        $sTempFile = $sFilesPath . $sTempFileName . VC_FLV_EXTENSION;
        @unlink($sTempFile);
        _deleteTempFiles($sId);
        $sRecordedFileUrl = getRMSUrl($sServerApp, true) . $sStreamsFolder . $sFile . VC_FLV_EXTENSION;

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
        @chmod($sTempFile, 0666);

        if(file_exists($sTempFile) && filesize($sTempFile) > 0 && _grabImages($sTempFile, $sFilesPath . $sTempFileName)) {
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
            $sContents .= parseXml($aXmlTemplates['file'], "0", $GLOBALS['sFilesDir'] . $sTempFileName . VC_FLV_EXTENSION, "", $GLOBALS['sFilesDir'] . $sTempFileName . VC_IMAGE_EXTENSION, 0);
        } else _deleteTempFiles($sId);
        break;

    /**
    * Delete files (reported files)
    */
    case 'removeFile':
        if($sFile == "")
            $sContents = parseXml($aXmlTemplates['result'], "msgErrorDelete", FAILED_VAL);
        elseif($sFile != "") {
            $aFiles = explode(",", $sFile);
            if(count($aFiles) > 0) {
                for($i=0; $i<count($aFiles); $i++)
                    $bResult = _deleteFile($aFiles[$i]);
            }
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        }
        break;

    /**
    * Upload user's file
    */
    case 'uploadFile':
        $sContents = uploadFile($_FILES['Filedata']['tmp_name'], $sId);
        $sContentsType = "other";
        break;

    case 'initFile':
        $sContents = initVideoFile($sId, $sTitle, $sCategory, $sTags, $sDesc);
        $sContentsType = "other";
        break;

    case 'publishRecordedFile':
        $sContents = publishRecordedVideoFile($sId, $sTitle, $sCategory, $sTags, $sDesc);
        $sContentsType = "other";
        break;

    case 'removeTempFiles':
        _deleteTempFiles($sId);
        break;

    case 'screenshot':
        $sPlayFile = $sFilesPath . $sId . (file_exists($sFilesPath . $sId . VC_M4V_EXTENSION) ? VC_M4V_EXTENSION : VC_FLV_EXTENSION);
        if(_grabImages($sPlayFile, $sFilesPath . $sId, $sTime, true))
             $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        else $sContents = parseXml($aXmlTemplates['result'], "msgErrorScreenshot", FAILED_VAL);
        break;

    case 'screenshotRecorder':
        $sFile = $sFilesPath . $sId . VC_TEMP_FILE_NAME;
        $sPlayFile = $sFile . (file_exists($sFile . VC_M4V_EXTENSION) ? VC_M4V_EXTENSION : VC_FLV_EXTENSION);
        if(_grabImages($sPlayFile, $sFile, $sTime, true))
             $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        else $sContents = parseXml($aXmlTemplates['result'], "msgErrorScreenshot", FAILED_VAL);
        break;

    case 'updateFile':
        $sCategoryUpdate = $sCategory == "0" ? "" : ", `Categories`='" . $sCategory . "'";
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Files` SET `Title`='" . $sTitle . "', `Tags`='" . $sTags . "', `Description`='" . $sDesc . "'" . $sCategoryUpdate . " WHERE `ID`='" . $sId . "' LIMIT 1");
        break;

    /**
    * set user's uploaded file time
    */
    case 'updateFileTime':
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Files` SET `Time`='" . $sTime . "' WHERE `ID`='" . $sId . "'");
        $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        break;

    case 'post':
        $sSystem = isset($_REQUEST['system']) ? process_db_input($_REQUEST['system']) : "";
        $sAuthor = isset($_REQUEST['author']) ? process_db_input($_REQUEST['author']) : "";
        $sParent = isset($_REQUEST['parent']) ? process_db_input($_REQUEST['parent']) : "";
        $sMood = isset($_REQUEST['mood']) ? process_db_input($_REQUEST['mood']) : "";

        $sContents = "";
        $sResult = publishRecordedVideoFile($sAuthor);
        if($sResult)
            $sContents = post($sSystem, $sId, $sAuthor, $sParent, $sMood, $sResult);
        $sContentsType = "text";
        break;

    case 'getToken':
        $sToken = _getToken($sId);
        if(empty($sToken))
            $sContents = parseXml($aXmlTemplates['result'], "msgFileNotFound", FAILED_VAL);
        else
            $sContents = parseXml($aXmlTemplates['result'], $sToken, SUCCESS_VAL);
        break;
}
