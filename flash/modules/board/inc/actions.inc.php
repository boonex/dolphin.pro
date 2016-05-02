<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$sNick = isset($_REQUEST['nick']) ? process_db_input($_REQUEST['nick']) : "";
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";

$iBoardId = isset($_REQUEST['boardId']) ? (int)$_REQUEST['boardId'] : 0;
$sTitle = isset($_REQUEST['title']) ? process_db_input(rawurldecode($_REQUEST['title']), BX_TAGS_SPECIAL_CHARS) : "";

$sParamName = isset($_REQUEST['param']) ? process_db_input($_REQUEST['param']) : "";
$sParamValue = isset($_REQUEST['value']) ? process_db_input($_REQUEST['value']) : "";

$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "default";
$sLanguage = isset($_REQUEST['language']) ? process_db_input($_REQUEST['language']) : "english";

switch ($sAction) {
    case 'getPlugins':
        $sFolder = "/plugins/";
        $sContents = "";
        $sFolderPath = $sModulesPath . $sModule . $sFolder;
        if(file_exists($sFolderPath) && is_dir($sFolderPath)) {
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
     * Get config
     */
    case 'config':
        $sFileName = $sModulesPath . $sModule . "/xml/config.xml";
        $rHandle = fopen($sFileName, "rt");
        $sContents = fread($rHandle, filesize($sFileName)) ;
        fclose($rHandle);

        $sContents = str_replace("#soundsUrl#", $sSoundsUrl, $sContents);
        $sContents = str_replace("#filesUrl#", $sFilesUrl, $sContents);
        $sContents = str_replace("#useServer#", useServer() ? TRUE_VAL : FALSE_VAL, $sContents);
        $sContents = str_replace("#serverUrl#", getRMSUrl($sServerApp), $sContents);
        break;

    /**
    * Authorize user
    */
    case 'userAuthorize':
        if(loginUser($sId, $sPassword) == TRUE_VAL) {
            $iCurrentTime = time();
            $aUser = getUserInfo($sId);
            $aUser['sex'] = $aUser['sex'] == 'female' ? "F" : "M";
            getResult("REPLACE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `ID`='" . $sId . "', `Nick`='" . process_db_input($aUser['nick']) . "', `Sex`='" . $aUser['sex'] . "', `Age`='" . $aUser['age'] . "', `Photo`='" . process_db_input($aUser['photo']) . "', `Profile`='" . process_db_input($aUser['profile']) . "', `Desc`='" . process_db_input($aUser['desc']) . "', `When`='" . $iCurrentTime . "', `Status`='" . USER_STATUS_NEW . "'");
            getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Users` WHERE `User`='" . $sId . "'");

            $rFiles = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Boards` WHERE `OwnerID`='" . $sId . "'");
            while($aFile = $rFiles->fetch()) @unlink($sFilesPath . $aFile['ID'] . $sFileExtension);
            getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Boards`, `" . MODULE_DB_PREFIX . "Users` USING `" . MODULE_DB_PREFIX . "Boards` LEFT JOIN `" . MODULE_DB_PREFIX . "Users` ON `" . MODULE_DB_PREFIX . "Boards`.`ID`=`" . MODULE_DB_PREFIX . "Users`.`Board` WHERE `" . MODULE_DB_PREFIX . "Boards`.`OwnerID`='" . $sId . "'");

            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
            $sContents .= parseXml($aXmlTemplates['user'], $sId, USER_STATUS_NEW, $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['photo'], $aUser['profile'], $aUser['desc']);
        } else $sContents = parseXml($aXmlTemplates['result'], "msgUserAuthenticationFailure", FAILED_VAL);
        break;

    /**
    * Get sounds
    */
    case 'getSounds':
        $sFileName = $sModulesPath . $sModule . "/xml/sounds.xml";
        if(file_exists($sFileName)) {
            $rHandle = fopen($sFileName, "rt");
            $sContents = fread($rHandle, filesize($sFileName));
            fclose($rHandle);
        } else $sContents = makeGroup("", "items");
        break;

    /**
    * Get rooms.
    */
    case 'getBoards':
        $sContents = makeGroup(getBoards("all", $sId), "boards");
        break;

    case 'createBoard':
        $iBoardId = doBoard('insert', $sId, 0, $sTitle, $sPassword);
        if(empty($iBoardId))$sContents = parseXml($aXmlTemplates['result'], "msgErrorCreatingBoard", FAILED_VAL);
        else 				$sContents = parseXml($aXmlTemplates['result'], $iBoardId, SUCCESS_VAL);
        break;

    case 'editBoard':
        doBoard('update', 0, $iBoardId, $sTitle, $sPassword);
        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        break;

    /**
    * Delete room from database.
    * Note. This action is used in both modes and by admin.
    */
    case 'deleteBoard':
        doBoard('delete', 0, $iBoardId);
        $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        break;

    case 'enterBoard':
        doBoard('enter', $sId, $iBoardId);
        break;

    case 'exitBoard':
        doBoard('exit', $sId, $iBoardId);
        @unlink($sFilesPath . $sId . $sFileExtension);
        break;

    case 'checkBoardPassword':
        $sId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Boards` WHERE `ID`='" . $iBoardId . "' AND `Password`='" . $sPassword . "' LIMIT 1");
        if(empty($sId)) $sContents = parseXml($aXmlTemplates['result'], "msgWrongRoomPassword", FAILED_VAL);
        else			$sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        break;

    case 'getOnlineUsers':
        //--- Check RayChatMessages table and drop autoincrement if it is possible. ---//
        $rResult = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "CurrentUsers`");
        if($rResult->rowCount() == 0) getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "CurrentUsers`");
        //--- Update user's info and return info about all online users. ---//
        $sContents = refreshUsersInfo($sId);
        break;

    case 'update':
        $sContents = "";
        //--- update user's info ---//
        $sContents .= refreshUsersInfo($sId, 'update');
        //--- check for new rooms ---//
        $sContents .= makeGroup(getBoards('update', $sId), "boards");
        $sContents .= makeGroup(getBoards('updateUsers', $sId), "boardsUsers");
        break;

    /**
     * Transmit new Scene file from specified Board.
     * param - boardId
     * param - width
     * param - height
     * param - data
     */
    case 'transmit':
        if(!function_exists("imagecreatetruecolor")) {
            $sContents = parseXml($aXmlTemplates['result'], 'msgErrorGD', FAILED_VAL);
            break;
        }

        //--- Prepare data ---//
        $bSaveMode = isset($_REQUEST['save']) && $_REQUEST['save'] == TRUE_VAL;
        $sSavedId = isset($_REQUEST['savedId']) ? (int)$_REQUEST['savedId'] : 0;
        $iWidth = isset($_REQUEST['width']) ? (int)$_REQUEST['width'] : 0;
        $iHeight = isset($_REQUEST['height']) ? (int)$_REQUEST['height'] : 0;
        $iBackColor = isset($_REQUEST['backColor']) && is_numeric($_REQUEST['backColor']) ? (int)$_REQUEST['backColor'] : 16777216;
        $sData = isset($_REQUEST['data']) ? process_db_input($_REQUEST['data'], BX_TAGS_STRIP) : "";
        $iQuality = 100;

        $aData = explode(',', $sData);
        $aImageData = array();
        for($i=0; $i<count($aData); $i++) {
            $aPixel = explode("=", $aData[$i], 2);
            $aImageData[$aPixel[0]] = base_convert($aPixel[1], 36, 10);
        }

        //--- Create Image Resource ---//
        $rImage = @imagecreatetruecolor($iWidth, $iHeight);
        for($i=0, $y=0; $y<$iHeight; $y++)
            for($x=0; $x<$iWidth; $x++, $i++)
                @imagesetpixel ($rImage, $x, $y, isset($aImageData[$i]) ? $aImageData[$i] : $iBackColor);

        //--- Save image file ---//
        $sFileName = $sFilesPath . $iBoardId . $sFileExtension;
        $bFileCreated = @imagejpeg($rImage, $sFileName, $iQuality);
        $aResult = $bFileCreated
            ? array('status' => SUCCESS_VAL, 'value' => "")
            : array('status' => FAILED_VAL, 'value' => "msgErrorFile");
        if($bFileCreated && $bSaveMode) {
            $aResult = save($sSavedId, $sFileName, $sTitle);
            if(useServer()) @unlink($sFileName);
        }

        $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], $aResult['status']);
        break;

    case 'getSaved':
        if(loginUser($sId, $sPassword) == TRUE_VAL)
            $sContents = getSavedBoardInfo($sId, $iBoardId);
        else
            $sContents = parseXml($aXmlTemplates['result'], "msgUserAuthenticationFailure", FAILED_VAL);
        break;
}
