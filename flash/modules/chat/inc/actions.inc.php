<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? process_db_input($_REQUEST['id']) : "";
$sNick = isset($_REQUEST['nick']) ? process_db_input($_REQUEST['nick']) : "";
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
$sType = isset($_REQUEST['type']) ? process_db_input($_REQUEST['type']) : "";
$sOnline = isset($_REQUEST['online']) ? process_db_input($_REQUEST['online']) : USER_STATUS_ONLINE;

$sSmileset = isset($_REQUEST['smileset']) ? process_db_input($_REQUEST['smileset']) : "";
$sSender = $_REQUEST['sender'] ? process_db_input($_REQUEST['sender']) : "";
$sRcp = $_REQUEST['recipient'] ? (int)$_REQUEST['recipient'] : "";
$sMessage = isset($_REQUEST['message']) ? process_db_input($_REQUEST['message']) : "";

$iRoomId = isset($_REQUEST['roomId']) ? (int)$_REQUEST['roomId'] : 0;
$sRoom = isset($_REQUEST['room']) ? process_db_input($_REQUEST['room']) : "";
$sDesc = isset($_REQUEST['desc']) ? process_db_input($_REQUEST['desc']) : "";

$sParamName = isset($_REQUEST['param']) ? process_db_input($_REQUEST['param']) : "";
$sParamValue = isset($_REQUEST['value']) ? process_db_input($_REQUEST['value']) : "";

$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "";
$sLanguage = isset($_REQUEST['language']) ? process_db_input($_REQUEST['language']) : "english";

switch ($sAction) {
    case 'getPlugins':
        $sFolder = isset($_REQUEST["app"]) && $_REQUEST["app"] == "admin" ? "/pluginsAdmin/" : "/plugins/";
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
        $sContents = str_replace("#loginUrl#", $sRootURL . "member.php", $sContents);
        break;

    case 'RayzFontSet':
        $sKey = isset($_REQUEST['key']) ? $_REQUEST['key'] : "";
        $sValue = isset($_REQUEST['value']) ? $_REQUEST['value'] : "";
        if(empty($sKey) || $sValue == "") break;
        setCookie("RayzFont" . $sKey, $sValue, time() + 31536000);
        break;

    case 'RayzFontGet':
        $aSettings = array (
            8 => '<settings bold="#1#" italic="#2#" underline="#3#" color="#4#" font="#5#" size="#6#" volume="#7#" muted="#8#" />'
        );
        $sContents = parseXml($aSettings, $_COOKIE["RayzFontbold"], $_COOKIE["RayzFontitalic"], $_COOKIE["RayzFontunderline"], $_COOKIE["RayzFontcolor"], $_COOKIE["RayzFontfont"], $_COOKIE["RayzFontsize"], $_COOKIE["RayzFontvolume"], $_COOKIE["RayzFontmuted"]);
        break;

    case 'RzGetBlockingUsers':
        $bBlocking = true;
        //break shouldn't be here
    case 'RzGetBlockedUsers':
        if(!isset($bBlocking))
            $bBlocking = false;
        $aUsers = getBlockingList($sId, $bBlocking);
        $sContents = parseXml($aXmlTemplates['result'], implode(",", $aUsers));
        break;

    case 'RzSetBlocked':
        $sUser = isset($_REQUEST['user']) ? process_db_input($_REQUEST['user']) : "";
        $bBlocked = isset($_REQUEST['blocked']) ? $_REQUEST['blocked'] == TRUE_VAL : false;
        blockUser($sId, $sUser, $bBlocked);
        break;

    case 'RayzGetMemberships':
        $aMemberships = rzGetMemberships();

        $sMemberships = "";
        foreach($aMemberships as $sId => $sName)
            $sMemberships .= rzGetMembershipValues($sId, $sName);

        $sContents = rzGetMembershipSettings(true);
        $sContents .= makeGroup($sMemberships, "memberships");
        break;

    case 'RayzSetMembershipSetting':
        $sKey = isset($_REQUEST['key']) ? process_db_input($_REQUEST['key']) : "";
        $sValue = isset($_REQUEST['value']) ? process_db_input($_REQUEST['value']) : "";
        $aKeys = getArray("SELECT `keys`.`ID` AS `KeyID`, `values`.`ID` AS `ValueID` FROM `" . MODULE_DB_PREFIX . "MembershipsSettings` AS `keys` LEFT JOIN `" . MODULE_DB_PREFIX . "Memberships` AS `values` ON `keys`.`ID`=`values`.`Setting` AND `values`.`Membership`='" . $sId . "' WHERE `keys`.`Name`='" . $sKey . "' LIMIT 1");
        if(empty($aKeys['KeyID'])) {
            $sContents = parseXml($aXmlTemplates['result'], "Error saving setting.", FAILED_VAL);
            break;
        } else if(empty($aKeys['ValueID']))
            getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Memberships` (`Setting`, `Value`, `Membership`) VALUES('" . $aKeys['KeyID'] . "', '" . $sValue . "', '" . $sId . "')");
        else
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Memberships` SET `Value`='" . $sValue . "' WHERE `ID`='" . $aKeys['ValueID'] . "'");
        break;

    case 'RayzGetMembership':
        $sMembership = rzGetMembershipId($sId);
        $sContents = rzGetMembershipSettings(false);
        $sContents .= rzGetMembershipValues($sMembership);
        break;

    case 'RzGuestLogin':
        $sUserId = searchUser($sNick, "NickName");
        if(!empty($sUserId)) {
            $sContents = parseXml($aXmlTemplates['result'], "RayzGuestError", FAILED_VAL);
            break;
        }

        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Profiles` WHERE `ID`='" . $sId . "'");
        getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Profiles` SET `ID`='" . $sId . "', `Type`='" . CHAT_TYPE_FULL . "', `Smileset`='" . $sDefSmileset . "'");

        $iCurrentTime = time();
        $sSex = isset($_REQUEST['sex']) ? process_db_input($_REQUEST['sex']) : "M";
        $sAge = isset($_REQUEST['age']) ? process_db_input($_REQUEST['age']) : "25";
        $sPhoto = $sSex == "F" ? $sWomanImageUrl : $sManImageUrl;
        getResult("REPLACE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `ID`='" . $sId . "', `Nick`='" . $sNick . "', `Sex`='" . $sSex . "', `Age`='" . $sAge . "', `Desc`='" . $sDesc . "', `Photo`='" . $sPhoto . "', `Profile`='" . $sProfileUrl . "', `Start`='" . $iCurrentTime . "', `When`='" . $iCurrentTime . "', `Status`='" . USER_STATUS_NEW . "'");
        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `User`='" . $sId . "'");

        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        $sContents .= parseXml(array(2 => '<user photo="#1#" profile="#2#" />'), $sPhoto, $sProfileUrl);
        break;

    /**
    * Authorize user.
    */
    case 'userAuthorize':
        if(loginAdmin($sId, $sPassword) == TRUE_VAL) {
            $aUserInfo = getUserInfo($sId, true);
            $aUser = array('id' => $aUserInfo['id'], 'nick' => $aUserInfo['nick'], 'sex' => $aUserInfo['sex'], 'age' => $aUserInfo['age'], 'desc' => $aUserInfo['desc'], 'photo' => $aUserInfo['photo'], 'profile' => $aUserInfo['profile'], 'type' => CHAT_TYPE_ADMIN);
        } elseif(loginUser($sId, $sPassword) == TRUE_VAL && ($bBanned = doBan("check", $sId)) != TRUE) {
            $aUser = getUserInfo($sId);
            $aUser['id'] = $sId;
            $aUser['sex'] = $aUser['sex'] == 'female' ? "F" : "M";
            $aUser['type'] = isUserAdmin($sId) ? CHAT_TYPE_ADMIN : CHAT_TYPE_FULL;
        } else {
            $sContents = parseXml($aXmlTemplates['result'], $bBanned ? "msgBanned" : "msgUserAuthenticationFailure", FAILED_VAL);
            break;
        }
        $aUser = initUser($aUser);
        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        $sContents .= parseXml($aXmlTemplates['user'], $aUser['id'], USER_STATUS_NEW, $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['desc'], $aUser['photo'], $aUser['profile'], $aUser['type'], USER_STATUS_ONLINE);
        break;

    case 'banUser':
        $sBanned = isset($_REQUEST["banned"]) ? process_db_input($_REQUEST['banned']) : FALSE_VAL;
        $sUserId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX ."Profiles` WHERE `ID` = '" . $sId . "' LIMIT 1");
        getResult(empty($sUserId)
            ? "INSERT INTO `" . MODULE_DB_PREFIX . "Profiles`(`ID`, `Banned`) VALUES('" . $sId . "', '" . $sBanned . "')"
            : "UPDATE `" . MODULE_DB_PREFIX . "Profiles` SET `Banned`='" . $sBanned . "' WHERE `ID`='" . $sId . "'");
        break;

    case 'kickUser':
        getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `Status`='" . USER_STATUS_KICK . "', `When`='" . time() . "' WHERE `ID`='" . $sId . "'");
        break;

    case 'changeUserType':
        $sUserId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX ."Profiles` WHERE `ID` = '" . $sId . "' LIMIT 1");
        getResult(empty($sUserId)
            ? "INSERT INTO `" . MODULE_DB_PREFIX . "Profiles`(`ID`, `Type`) VALUES('" . $sId . "', '" . $sType . "')"
            : "UPDATE `" . MODULE_DB_PREFIX . "Profiles` SET `Type`='" . $sType . "' WHERE `ID`='" . $sId . "'");
        break;

    case 'searchUser':
        $sContents = parseXml($aXmlTemplates['result'], "No User Found.", FAILED_VAL);
        $sUserId = searchUser($sParamValue, $sParamName);
        if(empty($sUserId)) break;

        $aUser = getUserInfo($sUserId);
        $aUser['sex'] = $aUser['sex'] == "female" ? "F" : "M";
        $aProfile = getArray("SELECT * FROM `" . MODULE_DB_PREFIX ."Profiles` WHERE `ID` = '" . $sUserId . "' LIMIT 1");
        if(!is_array($aProfile) || count($aProfile) == 0) $aProfile = array("Banned" => FALSE_VAL, "Type" => CHAT_TYPE_FULL);

        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        $sContents .= parseXml($aXmlTemplates['user'], $sUserId, $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['photo'], $aUser['profile'], $aProfile['Banned'], $aProfile['Type']);
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
        $sUserSmileset = getValue("SELECT `Smileset` FROM `" . MODULE_DB_PREFIX . "Profiles` WHERE `ID`='" . $sId . "'");
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
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Profiles` SET `Smileset`='" . $sSmileset . "' WHERE `ID`='" . $sId . "'");
        break;

    /**
    * Get rooms.
    */
    case 'getRooms':
//        doRoom('deleteTemp');
        $sContents = makeGroup(getRooms("all", $sId), "rooms");
        break;

    /**
    * Creats new room.
    * Note. This action is used in both modes and by admin.
    */
    case 'createRoom':
        $iRoomId = doRoom('insert', $sId, 0, $sRoom, $sPassword, $sDesc, process_db_input($_REQUEST['temp']) == TRUE_VAL);
        if(empty($iRoomId))	$sContents = parseXml($aXmlTemplates['result'], "msgErrorCreatingRoom", FAILED_VAL);
        else 				$sContents = parseXml($aXmlTemplates['result'], $iRoomId, SUCCESS_VAL);
        break;

    case 'editRoom':
        doRoom('update', 0, $iRoomId, $sRoom, $sPassword, $sDesc);
        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        break;

    /**
    * Delete room from database.
    * Note. This action is used in both modes and by admin.
    */
    case 'deleteRoom':
        doRoom('delete', 0, $iRoomId);
        $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        break;

    case 'enterRoom':
        doRoom('enter', $sId, $iRoomId);
        break;

    case 'exitRoom':
        doRoom('exit', $sId, $iRoomId);
        break;

    case 'checkRoomPassword':
        $sId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Rooms` WHERE `ID`='" . $iRoomId . "' AND `Password`='" . $sPassword . "' LIMIT 1");
        if(empty($sId)) $sContents = parseXml($aXmlTemplates['result'], "msgWrongRoomPassword", FAILED_VAL);
        else			$sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        break;

    /**
    * ===> Next actions are needed for XML version only. <===
    * Gets information about all online users.
    * NOTE. This action is used in XML mode and by ADMIN.
    * @comment Use this function instead of admin function "getOnline".
    */
    case 'getOnlineUsers':
        //--- Check RayChatMessages table and drop autoincrement if it is possible. ---//
        $rResult = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "CurrentUsers`");
        if($rResult->rowCount() == 0) getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "CurrentUsers`");
        $rResult = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Messages`");
        if($rResult->rowCount() == 0) getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "Messages`");
        //--- Update user's info and return info about all online users. ---//
        $sContents = refreshUsersInfo($sId);
        break;

    /**
    *	set user online status
    */
    case 'setOnline':
        getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `Online`='" . $sOnline . "', `When`='" . time() . "', `Status`='" . USER_STATUS_ONLINE . "' WHERE `ID`='" . $sId . "'");
        break;

    /**
    * Check for chat changes: new users, rooms, messages.
    * Note. This action is used in XML mode and by ADMIN.
    */
    case 'update':
        $sFiles = "";
        $res = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `Type`='file' AND `Recipient`='" . $sId . "'");
        while($aFile = $res->fetch()) {
            $sFileName = $aFile['ID'] . ".file";
            if(!file_exists($sFilesPath . $sFileName)) continue;
            $sFiles .= parseXml($aXmlTemplates['file'], $aFile['Sender'], $sFileName, $aFile['Message']);
        }
        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `Type`='file' AND `Recipient`='" . $sId . "'");
        $sContents = makeGroup($sFiles, "files");

        //--- update user's info ---//
        $sContents .= refreshUsersInfo($sId, 'update');
        //--- check for new rooms ---//
        $sContents .= makeGroup(getRooms('update', $sId), "rooms");
        $sContents .= makeGroup(getRooms('updateUsers', $sId), "roomsUsers");

        //--- check for new messages ---//
        $iUpdateInterval = (int)getSettingValue($sModule, "updateInterval");
        $sMsgs = "";
        $sRooms = getValue("SELECT GROUP_CONCAT(DISTINCT `Room` SEPARATOR ',') FROM `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `User`='" . $sId . "' AND `Status`='" . ROOM_STATUS_NORMAL ."'");
        if(empty($sRooms)) $sRooms = "''";
        $sSql = "SELECT * FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `Type`='text' AND `Sender`<>'" . $sId . "' AND ((`Room` IN (" . $sRooms . ") AND `Whisper`='" . FALSE_VAL . "') OR `Recipient`='" . $sId . "') AND `When`>='" . (time() - $iUpdateInterval) . "' ORDER BY `ID`";
        $res = getResult($sSql);
        while($aMsg = $res->fetch()) {
            $aStyle = unserialize($aMsg['Style']);
            $sMsgs .= parseXml($aXmlTemplates['message'], $aMsg['ID'], stripslashes($aMsg['Message']), $aMsg['Room'], $aMsg['Sender'], $aMsg['Recipient'], $aMsg['Whisper'], $aStyle['color'], $aStyle['bold'], $aStyle['underline'], $aStyle['italic'], $aStyle['size'], $aStyle['font'], $aStyle['smileset'], $aMsg['When']);
        }
        $sContents .= makeGroup($sMsgs, "messages");
        break;

    /**
    * Add message to database.
    */
    case 'newMessage':
        if(empty($sSender))
			break;
		if(!useServer())
		{
			$sWhisper = isset($_REQUEST['whisper']) ? process_db_input($_REQUEST['whisper']) : FALSE_VAL;
			$sColor = $_REQUEST['color'] ? (int)$_REQUEST['color'] : 0;
			$sBold = $_REQUEST['bold'] ? process_db_input($_REQUEST['bold']) : FALSE_VAL;
			$sUnderline = $_REQUEST['underline'] ? process_db_input($_REQUEST['underline']) : FALSE_VAL;
			$sItalic = $_REQUEST['italic'] ? process_db_input($_REQUEST['italic']) : FALSE_VAL;
			$iSize = $_REQUEST['size'] ? (int)$_REQUEST['size'] : 12;
			$sFont = $_REQUEST['font'] ? process_db_input($_REQUEST['font']) : "Arial";
			$sStyle = serialize(array('color' => $sColor, 'bold' => $sBold, 'underline' => $sUnderline, 'italic' => $sItalic, 'smileset' => $sSmileset, 'size' => $iSize, 'font' => $sFont));
			getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Messages`(`Room`, `Sender`, `Recipient`, `Message`, `Whisper`, `Style`, `When`) VALUES('" . $iRoomId . "', '" . $sSender . "', '" . $sRcp . "', '" . $sMessage . "', '" . $sWhisper . "', '" . $sStyle . "', '" . time() . "')");
		}
		if(empty($iRoomId))
			$sSndRcp = strcmp($sSender, $sRcp) < 0 ? $sSender . "." . $sRcp : $sRcp . "." . $sSender;
		else
			$sSndRcp = "";
		getResult("INSERT INTO `" . MODULE_DB_PREFIX . "History`(`Room`, `SndRcp`, `Sender`, `Recipient`, `Message`, `When`) VALUES('" . $iRoomId . "', '" . $sSndRcp . "', '" . $sSender . "', '" . $sRcp . "', '" . $sMessage . "', '" . time() . "')");
        break;
		
	case 'getHistory':
		$iDay = (int)$_REQUEST['day'];
		$iMonth = (int)$_REQUEST['month'];
		$iYear = (int)$_REQUEST['year'];
		$iStartDate = mktime(0, 0, 0, $iMonth, $iDay, $iYear);
		$iEndDate = mktime(0, 0, 0, $iMonth, ($iDay+1), $iYear);
		$aMessages = array();
		$aUsers = array();
		$rRes = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "History` WHERE `When`>=" . $iStartDate . " AND `When`<" . $iEndDate . " ORDER BY `Room`, `Sender`, `Recipient` ASC");
		if($rRes->rowCount() == 0)
		{
			$sContents = makeGroup("", "users") . makeGroup("", "rooms") . makeGroup("", "privates");
			break;
		}
		
		//users
		$iUsersCount = $rRes->rowCount();
		for($i=0; $i<$iUsersCount; $i++)
		{
			$aMsg = $rRes->fetch();
			$aMessages[] = $aMsg;
			if(!empty($aMsg['Sender']))
				$aUsers[] = $aMsg['Sender'];
			if(!empty($aMsg['Recipient']))
				$aUsers[] = $aMsg['Recipient'];
		}		
		$sUsers = "";
		$aUsers = array_flip(array_unique($aUsers));
		foreach($aUsers as $iUserId => $sValue)
		{
			$aUser = getUserInfo($iUserId);
			$sUsers .= parseXml($aXmlTemplates['history']['user'], $iUserId, $aUser['nick']);
		}
		$sContents = makeGroup($sUsers, "users");
		
		//rooms dialogs
		$rResRooms = getResult("SELECT `history`.*, `rooms`.`Name` AS `Title` FROM `" . MODULE_DB_PREFIX . "History` AS `history` INNER JOIN `" . MODULE_DB_PREFIX . "Rooms` AS `rooms` ON `history`.`Room`=`rooms`.`ID` WHERE `history`.`Room`>0 AND `history`.`When`>=" . $iStartDate . " AND `history`.`When`<" . $iEndDate . " ORDER BY `Room`, `When` ASC");
		$sRooms = "";
		$sMsgs = "";
		$iRoom = 0;
		$iCount = 0;
		$sRoom = "";
		$iRoomsCount = $rResRooms->rowCount();
		for($i=0; $i<$iRoomsCount; $i++)
		{
			$aMsg = $rResRooms->fetch();
			if($aMsg['Room'] != $iRoom)
			{
				if(!empty($sRoom) && !empty($sMsgs))
				{
					$sRooms .= parseXml($aXmlTemplates['history']['room'], $iRoom, $sRoom, $iCount) . $sMsgs . "</room>";
					$sMsgs = "";
					$iCount = 0;
				}
				$iRoom = $aMsg['Room'];
				$sRoom = $aMsg['Title'];				
			}
			$iCount++;
			$sMsgs .= parseXml($aXmlTemplates['history']['msg'], $aMsg['ID'], $aMsg['Sender'], $aMsg['Recipient'], $aMsg['Message']);
		}
		if(!empty($sRoom) && !empty($sMsgs))
			$sRooms .= parseXml($aXmlTemplates['history']['room'], $iRoom, $sRoom, $iCount) . $sMsgs . "</room>";
		$sContents .= makeGroup($sRooms, "rooms");
		
		//private dialogs
		$rResMsgs = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "History` WHERE `Room`=0 AND `When`>=" . $iStartDate . " AND `When`<" . $iEndDate . " ORDER BY `SndRcp`, `When` ASC");
		$sPrivate = "";
		$sMsgs = "";
		$sSndRcp = "";
		$iCount = 0;
		$iMsgsCount = $rResMsgs->rowCount();
		for($i=0; $i<$iMsgsCount; $i++)
		{
			$aMsg = $rResMsgs->fetch();
			if($aMsg['SndRcp'] != $sSndRcp)
			{
				if(!empty($sMsgs))
				{
					$sPrivate .= parseXml($aXmlTemplates['history']['private'], $aMsg['Sender'], $aMsg['Recipient'], $iCount) . $sMsgs . "</private>";
					$sMsgs = "";
					$iCount = 0;
				}
				$sSndRcp = $aMsg['SndRcp'];
			}
			$iCount++;
			$sMsgs .= parseXml($aXmlTemplates['history']['msg'], $aMsg['ID'], $aMsg['Sender'], $aMsg['Message']);
		}
		if(!empty($sMsgs))
			$sPrivate .= parseXml($aXmlTemplates['history']['private'], $aMsg['Sender'], $aMsg['Recipient'], $iCount) . $sMsgs . "</private>";
		$sContents .= makeGroup($sPrivate, "privates");
		break;

    case 'uploadFile':
        if(empty($sSender)) break;
        if(is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
            $sFilePath = $sFilesPath . $sSender . ".temp";
            @unlink($sFilePath);
            move_uploaded_file($_FILES['Filedata']['tmp_name'], $sFilePath);
            @chmod($sFilePath, 0644);
        }
        break;

    case 'initFile':
        $sFilePath = $sFilesPath . $sSender . ".temp";
        $sContents = parseXml($aXmlTemplates['result'], "msgErrorUpload", FAILED_VAL);
        if(empty($sSender) || !file_exists($sFilePath) || filesize($sFilePath) == 0) break;

        getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Messages`(`Sender`, `Recipient`, `Message`, `Type`, `When`) VALUES('" . $sSender . "', '" . $sRcp . "', '" . $sMessage . "', 'file', '" . time() . "')");
        $sFileName = getLastInsertId() . ".file";
        if(!@rename($sFilePath, $sFilesPath . $sFileName)) break;

        $sContents = parseXml($aXmlTemplates['result'], $sFileName, SUCCESS_VAL);
        break;

    case 'removeFile':
        $sId = str_replace(".file", "", $sId);
        removeFile($sId);
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
