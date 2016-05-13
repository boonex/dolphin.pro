<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : "";
//sender's ID
$sSndId = isset($_REQUEST['sender']) ? (int)$_REQUEST['sender'] : 0;
//sender's Nick
$sSndNick = isset($_REQUEST['nick']) ? process_db_input($_REQUEST['nick']) : "";
//sender's password
$sSndPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
//name of smileset
$sSmileset = isset($_REQUEST['smileset']) ? process_db_input($_REQUEST['smileset']) : "";

//recipient's ID
$sRspId = isset($_REQUEST['recipient']) ? (int)$_REQUEST['recipient'] : 0;

//user's message
$sMsg = isset($_REQUEST['message']) ? process_db_input($_REQUEST['message']) : "";
//sex parameter
$sSex = isset($_REQUEST['sex']) ? process_db_input($_REQUEST['sex']) : "Male";
//user's file name
$sFile = isset($_REQUEST['file']) ? process_db_input($_REQUEST['file']) : "";

//skin name
$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "";
//language name
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
    * Get chat's config.
    */
    case 'config':
        $sFileName = $sModulesPath . $sModule . "/xml/config.xml";
        $rHandle = fopen($sFileName, "rt");
        $sContents = fread($rHandle, filesize($sFileName)) ;
        fclose($rHandle);

        $iFileSize = (int)getSettingValue($sModule, "fileSize");
        $iMaxFileSize = min((ini_get('upload_max_filesize') + 0), (ini_get('post_max_size') + 0), $iFileSize);
        $sContents = str_replace("#fileMaxSize#", $iMaxFileSize, $sContents);
        $sContents = str_replace("#userVideo#", getUserVideoLink(), $sContents);
        $sContents = str_replace("#userMusic#", getUserMusicLink(), $sContents);
        $sContents = str_replace("#soundsUrl#", $sSoundsUrl, $sContents);
        $sContents = str_replace("#smilesetsUrl#", $sSmilesetsUrl, $sContents);
        $sContents = str_replace("#filesUrl#", $sFilesUrl, $sContents);
        $sContents = str_replace("#useServer#", useServer() ? TRUE_VAL : FALSE_VAL, $sContents);
        $sContents = str_replace("#serverUrl#", getRMSUrl($sServerApp), $sContents);
        break;

    /**
     * IM ACTIONS.
     * Authorize sender for IM dialog.
     */
    case 'senderAuthorize':
        if(loginUser($sSndId, $sSndPassword) == TRUE_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);

            //--- return sender's information ---//
            $aUser = getUserInfo($sSndId);
            $aUser['sex'] = $aUser['sex'] == 'female' ? "F" : "M";
            $sContents .= parseXml($aXmlTemplates['user'], $sSndId, $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['desc'], $aUser['photo'], $aUser['profile'], USER_STATUS_ONLINE);
        } else $sContents = parseXml($aXmlTemplates['result'], "msgUserAuthenticationFailure", FAILED_VAL);
        break;

    /**
     * Authorize recipient for IM dialog.
     */
    case 'recipientAuthorize':
        $sSearchResult = searchUser($sRspId);
        if(!empty($sSearchResult)) {
            $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);

            //--- return recipients's information ---//
            $aUser = getUserInfo($sRspId);
            $aUser['sex'] = $aUser['sex'] == 'female' ? "F" : "M";
            $sContents .= parseXml($aXmlTemplates['user'], $sRspId, $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['desc'], $aUser['photo'], $aUser['profile'], getUserOnlineStatus($sRspId, $sSndId));
        } else $sContents = parseXml($aXmlTemplates['result'], FALSE_VAL);
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
         * gets smilesets
         */
    case 'getSmilesets':
        $sConfigFile = "config.xml";
        $sContents = parseXml($aXmlTemplates['smileset'], "", "") . makeGroup("", "smilesets");
        $aSmilesets = array();
        if($rDirHandle = opendir($sSmilesetsPath))
            while(false !== ($sDir = readdir($rDirHandle)))
                if($sDir != "." && $sDir != ".." && is_dir($sSmilesetsPath . $sDir) && file_exists($sSmilesetsPath . $sDir . "/" . $sConfigFile))
                    $aSmilesets[] = $sDir;
        closedir($rDirHandle);
        if(count($aSmilesets) == 0) break;

        if(isset($_COOKIE["RayzFontsmileset"]))
            $sDefSmileset = substr($_COOKIE["RayzFontsmileset"], 0, -1);
        if(!in_array($sDefSmileset, $aSmilesets))
            $sDefSmileset = $aSmilesets[0];
        $sUserSmileset = getValue("SELECT `Smileset` FROM `" . MODULE_DB_PREFIX . "Profiles` WHERE `ID`='" . $sSndId . "'");
        if(empty($sUserSmileset) || !file_exists($sSmilesetsPath . $sUserSmileset)) $sUserSmileset = $sDefSmileset;

        $sContents = parseXml($aXmlTemplates['smileset'], $sUserSmileset . "/", $sSmilesetsUrl);
        $sData = "";
        for($i=0; $i<count($aSmilesets); $i++) {
            $sName = getSettingValue(GLOBAL_MODULE, "name", "config", false, $sDataDir . $sSmilesetsDir . $aSmilesets[$i]);
            $sData .= parseXml($aXmlTemplates['smileset'], $aSmilesets[$i] . "/", $sConfigFile, empty($sName) ? $aSmilesets[$i] : $sName);
        }
        $sContents .= makeGroup($sData, "smilesets");
        break;

    /**
    * Sets default smileset.
    */
    case 'setSmileset':
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Profiles` SET `Smileset`='" . $sSmileset . "' WHERE `ID`='" . $sSndId . "'");
        break;

    /**
    * Add pending message and return result of this operation
    */
    case 'addPend':
        $sContents .= parseXml($aXmlTemplates['result'], addPend( $sSndId, $sRspId, $sMsg ) ? TRUE_VAL : FALSE_VAL);
        break;

    /**
    * Pending messages are deleted, after obtaining.
    */
    case 'getPend':
        //--- get pending messages ---//
        $sMsgs = "";
        $rResult = getResult("SELECT `Message` FROM `" . MODULE_DB_PREFIX . "Pendings` WHERE `SenderID` = '" . $sSndId . "' AND `RecipientID` = '" . $sRspId . "' ORDER BY `ID`");
        while($aMsg = $rResult->fetch()) $sMsgs .= parseXml($aXmlTemplates['message'], $aMsg['Message']);
        $sContents .= makeGroup($sMsgs, "messages");
        //"break" shouldn't be here

    /**
     * Deleting pending messages.
     * Used by IM_invite also.
     */
    case 'deletePend':
        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Pendings` WHERE `SenderID`='" . $sSndId . "' AND `RecipientID`='" . $sRspId . "'");
        break;

    case 'uploadFile':
        if(empty($sSndId) || empty($sRspId)) break;
        if(is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
            $sFilePath = $sFilesPath . $sSndId . "to" . $sRspId . ".temp";
            @unlink($sFilePath);
            move_uploaded_file($_FILES['Filedata']['tmp_name'], $sFilePath);
            @chmod($sFilePath, 0644);
        }
        break;

    case 'initFile':
        $sFilePath = $sFilesPath . $sSndId . "to" . $sRspId . ".temp";
        $sContents = parseXml($aXmlTemplates['result'], "msgErrorUpload", FAILED_VAL);
        $sContactId = getContactId($sSndId, $sRspId);
        if(!file_exists($sFilePath) || filesize($sFilePath) == 0) break;

        if(empty($sContactId)) $sFileName = time();
        else {
            getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Messages`(`ContactID`, `Message`, `Type`, `When`) VALUES('" . $sContactId . "', '" . $sMessage . "', 'file', '" . time() . "')");
            $sFileName = getLastInsertId();
        }
        $sFileName .= ".file";
        if(!@rename($sFilePath, $sFilesPath . $sFileName)) break;

        $sContents = parseXml($aXmlTemplates['result'], $sFileName, SUCCESS_VAL);
        break;

    case 'removeFile':
        $sId = str_replace(".file", "", $sId);
        removeFile($sId);
        break;

    /**
     * >>> ACTIONS FOR INVITE <<<
     * Check for pending messages for given user
     */
    case 'updateInvite':
        $sContents = "";
        $aBlocked = getBlockedUsers($sRspId);
        $sBlocked = count($aBlocked) == 0 ? "" : " AND `SenderID` NOT IN('" . implode(",", $aBlocked) . "')";
        $rResult = getResult("SELECT `SenderID`, `Message` FROM `" . MODULE_DB_PREFIX ."Pendings` WHERE `RecipientID`='" . $sRspId . "'" . $sBlocked . " ORDER BY `ID` DESC");
        //--- if there is a message return it and some information about it's author ---//
        while($aMsg = $rResult->fetch()) {
            $aUserInfo = getUserInfo($aMsg['SenderID']);
            $sContents .= parseXml($aXmlTemplates['message'], $aMsg['SenderID'], $aUserInfo['nick'], $aUserInfo['profile'], $aMsg['Message']);
        }
        $sContents = makeGroup($sContents, "messages");
        if($rResult->rowCount())
            getResult("DELETE FROM `" . MODULE_DB_PREFIX ."Pendings` WHERE `RecipientID`='" . $sRspId . "'");
        break;


    /**
    * >>> ACTIONS LITE VERSION ONLY <<<
    * Refreshs IM users' states and insert current user's connection in connections table.
    * Is used during authorize process.
    */
    case 'refreshStatus':
        //--- checks whether user is online and if not then insert new contact for the user. ---//
        $iContactId = getContactId($sSndId, $sRspId);
        if(empty($iContactId)) getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Contacts`(`SenderID`, `RecipientID`, `When`) VALUES ('" . $sSndId . "', '" . $sRspId . "', '" . time() . "')");
        refreshIMUsers($sSndId, $sRspId);
        break;

    /**
    *	set user online status
    */
    case 'setOnline':
        $sOnline = isset($_REQUEST['online']) ? process_db_input($_REQUEST['online']) : USER_STATUS_ONLINE;
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Contacts` SET `Online`='" . $sOnline . "' WHERE `SenderID`='" . $sSndId . "' AND `RecipientID`='" . $sRspId . "' LIMIT 1");
        break;

    /**
     * Checking IM messages and user online status
     */
    case 'recipientUpdate':
        //--- check for IM changes ---//
        $iId = getContactId($sSndId, $sRspId);
        if(empty($iId)) getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Contacts`(`SenderID`, `RecipientID`, `Online`, `When`) VALUES ('" . $sSndId . "', '" . $sRspId . "', '" . USER_STATUS_ONLINE . "', '" . time() . "')");

        refreshIMUsers($sSndId, $sRspId);
        //--- checking online status of Recipient ---//
        $sContents = parseXml($aXmlTemplates['user'], getUserOnlineStatus($sRspId, $sSndId));

        $sFiles = "";
        $sQuery = "SELECT * FROM `" . MODULE_DB_PREFIX . "Contacts` AS `imc`, `" . MODULE_DB_PREFIX . "Messages` AS `imm` WHERE `imc`.`ID`=`imm`.`ContactID` AND `imm`.`Type`='file' AND `imc`.`SenderID`='" . $sRspId . "' AND `imc`.`RecipientID`='" . $sSndId . "' ORDER BY `imm`.`ID`";
        $res = getResult($sQuery);
        while($aFile = $res->fetch()) {
            $sFileName = $aFile['ID'] . ".file";
            if(!file_exists($sFilesPath . $sFileName)) continue;
            $sFiles .= parseXml($aXmlTemplates['file'], $sFileName, $aFile['Message']);
        }
        $sContents .= makeGroup($sFiles, "files");

        //--- checking for new messages ---//
        $iId = getContactId($sRspId, $sSndId);
        $sMsgs = "";
        $res = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `ContactID`='" . $iId . "' AND `Type`='text' ORDER BY `ID`");
        while($aMsg = $res->fetch()) {
            $aStyle = unserialize($aMsg['Style']);
            $sMsgs .= parseXml($aXmlTemplates['message'], $aMsg['ID'], stripslashes($aMsg['Message']), $aStyle['color'], $aStyle['bold'], $aStyle['underline'], $aStyle['italic'], $aStyle['size'], $aStyle['font'], $aStyle['smileset']);
        }
        $sContents .= makeGroup($sMsgs, "messages");

        //--- delete new messages ---//
        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `ContactID`='" . $iId . "'");
        break;

    /**
     * New message for IM
     */
    case 'newMessage':
        //--- check online status of the recipient ---//
        $iContactRid = getContactId($sRspId, $sSndId);
        if(!empty($iContactRid)) {
            $sColor = $_REQUEST['color'] ? (int)$_REQUEST['color'] : 0;
            $sBold = $_REQUEST['bold'] ? process_db_input($_REQUEST['bold']) : FALSE_VAL;
            $sUnderline = $_REQUEST['underline'] ? process_db_input($_REQUEST['underline']) : FALSE_VAL;
            $sItalic = $_REQUEST['italic'] ? process_db_input($_REQUEST['italic']) : FALSE_VAL;
            $iSize = $_REQUEST['size'] ? (int)$_REQUEST['size'] : 12;
            $sFont = $_REQUEST['font'] ? process_db_input($_REQUEST['font']) : "Arial";
            $sStyle = serialize(array('color' => $sColor, 'bold' => $sBold, 'underline' => $sUnderline, 'italic' => $sItalic, 'smileset' => $sSmileset, 'size' => $iSize, 'font' => $sFont));
            $iContactSid = getContactId($sSndId, $sRspId);
            if(!empty($iContactSid))
                $rResult = getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Messages` (`ContactID`, `Message`, `Style`, `When`) VALUES ('" . $iContactSid . "', '" . $sMsg . "', '" . $sStyle . "', '" . time() . "')");
        } else $rResult = addPend($sSndId, $sRspId, $sMsg);
        $sContents = parseXml($aXmlTemplates['result'], $rResult ? TRUE_VAL : FALSE_VAL);
        break;

    case 'help':
        $sApp = isset($_REQUEST['app']) ? process_db_input($_REQUEST['app']) : "user";
        $sContents = makeGroup("", "topics");
        $sFileName = $sModulesPath . $sModule . "/help/" . $sApp . ".xml";
        if(file_exists($sFileName)) {
            $rHandle = @fopen($sFileName, "rt");
            $sContents = @fread($rHandle, filesize($sFileName)) ;
            fclose($rHandle);
        }
        break;
}
