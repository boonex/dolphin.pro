<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function rzGetMembershipValues($sMembership, $sName = "")
{
    $rResult = getResult("SELECT `keys`.`ID` AS `ID`, `keys`.`Default` AS `Default`, `values`.`Value` AS `Value` FROM `" . MODULE_DB_PREFIX . "MembershipsSettings` AS `keys` LEFT JOIN `" . MODULE_DB_PREFIX . "Memberships` AS `values` ON `keys`.`ID`=`values`.`Setting` AND `values`.`Membership`='" . $sMembership . "'");
    $sContents = '<membership id="' . $sMembership . '" ';
	$iCount = $rResult->rowCount();
    for($i=0; $i<$iCount; $i++) {
        $aSetting = $rResult->fetch();
        $sValue = !isset($aSetting['Value']) || $aSetting['Value'] == "" ? $aSetting['Default'] : $aSetting['Value'];
        $sContents .= 'v' . $aSetting['ID'] . '="' . $sValue . '" ';
    }
    $sEnd = empty($sName) ? '/>' : '><![CDATA[' . $sName . ']]></membership>';
    $sContents .= $sEnd;
    return $sContents;
}

function rzGetMembershipSettings($bAdmin = false)
{
    $aSettingTmpls = array(
        3 => '<setting id="#1#" name="#2#"><![CDATA[#3#]]></setting>',
        6 => '<setting id="#1#" name="#2#" type="#3#" default="#4#" range="#5#"><![CDATA[#6#]]></setting>'
    );

    $rResult = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "MembershipsSettings`");
    $sSettings = "";
	$iCount = $rResult->rowCount();
    for($i=0; $i<$iCount; $i++) {
        $aSetting = $rResult->fetch();
        if($bAdmin) $sSettings .= parseXml($aSettingTmpls, $aSetting['ID'], $aSetting['Name'], $aSetting['Type'], $aSetting['Default'], $aSetting['Range'], $aSetting['Caption']);
        else $sSettings .= parseXml($aSettingTmpls, $aSetting['ID'], $aSetting['Name'], $aSetting['Error']);
    }
    return makeGroup($sSettings, "settings");
}

function initUser($aUser)
{
    $aProfile = getArray("SELECT * FROM `" . MODULE_DB_PREFIX . "Profiles` WHERE `ID`='" . $aUser['id'] . "'");
    if(!is_array($aProfile) || count($aProfile) == 0)
        getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Profiles` SET `ID`='" . $aUser['id'] . "', `Type`='" . $aUser['type'] . "', `Smileset`='" . $sDefSmileset . "'");
    else $aUser['type'] = $aProfile["Type"];

    if(!useServer()) {
        $iCurrentTime = time();
        $sNick = getEscapedValue($aUser['nick']);
        $sDescription = getEscapedValue($aUser['desc']);
        getResult("REPLACE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `ID`='" . $aUser['id'] . "', `Nick`='" . $sNick . "', `Sex`='" . $aUser['sex'] . "', `Age`='" . $aUser['age'] . "', `Desc`='" . $sDescription . "', `Photo`='" . $aUser['photo'] . "', `Profile`='" . $aUser['profile'] . "', `Start`='" . $iCurrentTime . "', `When`='" . $iCurrentTime . "', `Status`='" . USER_STATUS_NEW . "'");
        getResult("DELETE FROM `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `User`='" . $aUser['id'] . "'");
    }
    $rFiles = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `Recipient`='" . $aUser['id'] . "' AND `Type`='file'");
    while($aFile = $rFiles->fetch()) removeFile($aFile['ID']);
    return $aUser;
}

/**
 * Ban actions.
 * Check if this user is banned, ban this user, unban this user.
 * @param sSwitch - the name of the action which will be processed.
 * @param iId - user's identifier;
 */
function doBan($sSwitch, $sId = "0")
{
    global $sModule;
    global $aXmlTemplates;

    switch($sSwitch) {
        case 'check': //--- check if user specified by ID is banned or not.
            return getValue("SELECT `Banned` FROM `" . MODULE_DB_PREFIX . "Profiles` WHERE `ID` = '" . $sId . "' LIMIT 1") == TRUE_VAL;

        case 'ban': //--- ban the user specified by ID.
            $sBan = TRUE_VAL;
            //break shouldn't be here
        case 'unban': //--- unban the user, specified by ID.
            $sBan = FALSE_VAL;
        default:
            $sUserId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX ."Profiles` WHERE `ID` = '" . $sId . "' LIMIT 1");
            $sSql = empty($sUserId)
                ? "INSERT INTO `" . MODULE_DB_PREFIX . "Profiles`(`ID`, `Banned`, `Type`) VALUES('" . $sId . "', '" . $sBan . "', '" . CHAT_TYPE_FULL . "')"
                : "UPDATE `" . MODULE_DB_PREFIX . "Profiles` SET `Banned`='" . $sBan . "' WHERE `ID`='" . $sId . "'";
            return getResult($sSql);
    }
}

/**
 * Get information about avaliable rooms in XML format.
 * @comment - Refreshed
 */
function getRooms($sMode = 'new', $sId = "")
{
    global $aXmlTemplates;
    global $sModule;

    $iCurrentTime = time();
    $iUpdateInterval = (int)getSettingValue($sModule, "updateInterval");
    $iDeleteTime = $iUpdateInterval * 6;
    $sRooms = "";
    switch ($sMode) {
        case 'update': //--- Return new and deleted rooms.
            $rResult = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "Rooms` WHERE IF('" . $sId . "'='0', 1, `OwnerID`<>'" . $sId . "') AND (`When` >= (" . ($iCurrentTime - $iUpdateInterval) . ") OR (`Status`='" . ROOM_STATUS_DELETE . "' AND `When` >= " . ($iCurrentTime - $iDeleteTime) . ")) ORDER BY `When`");
            while($aRoom = $rResult->fetch())
                switch($aRoom['Status']) {
                    case ROOM_STATUS_DELETE:
                        $sRooms .= parseXml($aXmlTemplates['room'], $aRoom['ID'], ROOM_STATUS_DELETE);
                        break;
                    case ROOM_STATUS_NORMAL:
                    default:
                        $sRooms .= parseXml($aXmlTemplates['room'], $aRoom['ID'], ROOM_STATUS_NORMAL, $aRoom['OwnerID'], empty($aRoom['Password']) ? FALSE_VAL : TRUE_VAL, stripslashes($aRoom['Name']), stripslashes($aRoom['Desc']));
                        break;
                }
            break;

        case 'updateUsers':
            $sSql = "SELECT `r`.`ID` AS `RoomID`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . ROOM_STATUS_NORMAL . "',`ru`.`User`,'') SEPARATOR ',') AS `In`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . ROOM_STATUS_DELETE . "',`ru`.`User`,'') SEPARATOR ',') AS `Out` FROM `" . MODULE_DB_PREFIX . "Rooms` AS `r` INNER JOIN `" . MODULE_DB_PREFIX . "RoomsUsers` AS `ru` WHERE `r`.`ID`=`ru`.`Room` AND `r`.`Status`='" . ROOM_STATUS_NORMAL . "' AND `ru`.`When`>=" . ($iCurrentTime - $iUpdateInterval) . " GROUP BY `r`.`ID`";
            $rResult = getResult($sSql);
            while($aRoom = $rResult->fetch())
                $sRooms .= parseXml($aXmlTemplates['room'], $aRoom['RoomID'], $aRoom['In'], $aRoom['Out']);
            break;

        case 'all':
            $iRunTime = isset($_REQUEST['_t']) ? floor($_REQUEST['_t']/1000) : 0;
            $iCurrentTime -= $iRunTime;
            $rResult = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "RoomsUsers`");
            if($rResult->rowCount() == 0) getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "RoomsUsers`");
            $iRoomsCount = getValue("SELECT COUNT(`ID`) FROM `" . MODULE_DB_PREFIX . "Rooms` WHERE `Status`='" . ROOM_STATUS_NORMAL . "'");

            if(empty($iRoomsCount))
                getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Rooms` (`Name`, `OwnerID`, `Desc`, `When`, `Status`) VALUES ('Lobby', '0', 'Welcome to our chat!', '0', 'normal')");

            $sSql = "SELECT `r`.`ID` AS `RoomID`, `r`.*, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . ROOM_STATUS_NORMAL . "' AND `ru`.`User`<>'" . $sId . "',`ru`.`User`,'') SEPARATOR ',') AS `In`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . ROOM_STATUS_NORMAL . "' AND `ru`.`User`<>'" . $sId . "',(" . $iCurrentTime . "-`ru`.`When`),'') SEPARATOR ',') AS `InTime` FROM `" . MODULE_DB_PREFIX . "Rooms` AS `r` LEFT JOIN `" . MODULE_DB_PREFIX . "RoomsUsers` AS `ru` ON `r`.`ID`=`ru`.`Room` GROUP BY `r`.`ID` ORDER BY `r`.`ID` LIMIT " . getSettingValue($sModule, "maxRoomsNumber");
            $rResult = getResult($sSql);
            while($aRoom = $rResult->fetch())
                $sRooms .= parseXml($aXmlTemplates['room'], $aRoom['RoomID'], $aRoom['OwnerID'], empty($aRoom['Password']) ? FALSE_VAL : TRUE_VAL, stripslashes($aRoom['Name']), stripslashes($aRoom['Desc']), $aRoom['In'], $aRoom['InTime']);
            break;
    }
    return $sRooms;
}

/**
 * Actions with specified room
 */
function doRoom($sSwitch, $sUserId = "", $iRoomId = 0, $sTitle = "", $sPassword = "", $sDesc = "", $bTemp = false)
{
    $iCurrentTime = time();
    switch ($sSwitch) {
        case 'insert':
            $aCurRoom = getArray("SELECT * FROM `" . MODULE_DB_PREFIX . "Rooms` WHERE `Name`='" . $sTitle . "'");
            $sStatus = $bTemp ? ROOM_STATUS_DELETE : ROOM_STATUS_NORMAL;
            if(!empty($aCurRoom['ID']) && $sUserId == $aCurRoom['OwnerID']) {
                getResult("UPDATE `" . MODULE_DB_PREFIX . "Rooms` SET `Name`='" . $sTitle . "', `Password`='" . $sPassword . "', `Desc`='" . $sDesc . "', `OwnerID`='" . $sUserId . "', `When`='" . $iCurrentTime . "', `Status`='" . $sStatus . "' WHERE `ID`='" . $aCurRoom['ID'] . "'");
                return $aCurRoom['ID'];
            } else if(empty($aCurRoom['ID'])) {
                getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Rooms` (`ID`, `Name`, `Password`, `Desc`, `OwnerID`, `When`, `Status`) VALUES ('" . $iRoomId . "', '" . $sTitle . "', '" . $sPassword . "', '" . $sDesc . "', '" . $sUserId . "', '" . $iCurrentTime . "', '" . $sStatus . "')");
                return getLastInsertId();
            } else return 0;
            break;

        case 'update':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Rooms` SET `Name`='" . $sTitle . "', `Password`='" . $sPassword . "', `Desc`='" . $sDesc . "', `When`='" . $iCurrentTime . "', `Status`='" . ROOM_STATUS_NORMAL . "' WHERE `ID`='" . $iRoomId . "'");
            break;

        case 'delete':
            $sSql = "UPDATE `" . MODULE_DB_PREFIX . "Rooms` SET `When`='" . $iCurrentTime . "', `Status`='" . ROOM_STATUS_DELETE . "' WHERE `ID` = '" . $iRoomId . "'";
            getResult($sSql);
            break;

        case 'enter':
            $sId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `Room`='" . $iRoomId . "' AND `User`='" . $sUserId . "' LIMIT 1");
            if(empty($sId))	getResult("INSERT INTO `" . MODULE_DB_PREFIX . "RoomsUsers`(`Room`, `User`, `When`) VALUES('" . $iRoomId . "', '" . $sUserId . "', '" . $iCurrentTime . "')");
            else getResult("UPDATE `" . MODULE_DB_PREFIX . "RoomsUsers` SET `When`='" . $iCurrentTime . "', `Status`='" . ROOM_STATUS_NORMAL . "' WHERE `ID`='" . $sId . "'");
            break;

        case 'exit':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "RoomsUsers` SET `When`='" . $iCurrentTime . "', `Status`='" . ROOM_STATUS_DELETE . "' WHERE `Room`='" . $iRoomId . "' AND `User`='" . $sUserId . "' LIMIT 1");
            break;

        case 'deleteTemp':
            if(useServer())
                getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Rooms` WHERE `Status`='" . ROOM_STATUS_DELETE . "' AND `When`<" . ($iCurrentTime - 24*60*60));
            break;
    }
}

/**
 * ===> The rest of functions is for XML version only. <===
 * Update user's status
 * @comment - Refreshed
 */
function refreshUsersInfo($sId = "", $sMode = 'all')
{
    global $aXmlTemplates;
    global $sModule;

    $iUpdateInterval = (int)getSettingValue($sModule, "updateInterval");
    $iIdleTime = $iUpdateInterval * 3;
    $iDeleteTime = $iUpdateInterval * 6;

    $iCurrentTime = time();
    //--- refresh current user's track ---//
    getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `Status`='" . USER_STATUS_OLD . "', `When`='" . $iCurrentTime . "' WHERE `ID`='" . $sId . "' AND `Status`<>'" . USER_STATUS_KICK . "' AND (`Status` NOT IN('" . USER_STATUS_NEW . "', '" . USER_STATUS_TYPE . "', '" . USER_STATUS_ONLINE . "') || (" . $iCurrentTime . "-`When`)>" . $iUpdateInterval . ") LIMIT 1");

    //--- refresh other users' states ---//
    getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `When`=" . $iCurrentTime . ", `Status`='" . USER_STATUS_IDLE . "' WHERE `Status`<>'" . USER_STATUS_IDLE . "' AND `When`<=(" . ($iCurrentTime - $iIdleTime) . ")");
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `Status`='" . ROOM_STATUS_DELETE . "' AND `When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");

    $rFiles = getResult("SELECT `files`.`ID` AS `FileID` FROM `" . MODULE_DB_PREFIX . "Messages` AS `files` INNER JOIN `" . MODULE_DB_PREFIX . "CurrentUsers` AS `users` WHERE `files`.`Recipient`=`users`.`ID` AND `files`.`Type`='file' AND `users`.`Status`='" . USER_STATUS_IDLE . "' AND `users`.`When`<=" . ($iCurrentTime - $iDeleteTime));
    while($aFile = $rFiles->fetch()) removeFile($aFile['FileID']);

    //--- delete idle users, whose track was not refreshed more than delete time ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "CurrentUsers`, `" . MODULE_DB_PREFIX . "RoomsUsers` USING `" . MODULE_DB_PREFIX . "CurrentUsers`, `" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `" . MODULE_DB_PREFIX . "CurrentUsers`.`ID`=`" . MODULE_DB_PREFIX . "RoomsUsers`.`User` AND `" . MODULE_DB_PREFIX . "CurrentUsers`.`Status`='" . USER_STATUS_IDLE . "' AND `" . MODULE_DB_PREFIX . "CurrentUsers`.`When`<=" . ($iCurrentTime - $iDeleteTime));
    //--- delete old rooms ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Rooms`, `" . MODULE_DB_PREFIX . "RoomsUsers` USING `" . MODULE_DB_PREFIX . "Rooms`,`" . MODULE_DB_PREFIX . "RoomsUsers` WHERE `" . MODULE_DB_PREFIX . "Rooms`.`ID`=`" . MODULE_DB_PREFIX . "RoomsUsers`.`Room` AND `" . MODULE_DB_PREFIX . "Rooms`.`Status`='" . ROOM_STATUS_DELETE . "' AND `" . MODULE_DB_PREFIX . "Rooms`.`When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");

    //--- delete empty rooms ---//
    if(getSettingValue($sModule, "removeRooms") == TRUE_VAL) {
        $aIds = getAdminIds();
        $sIds = count($aIds)>0 ? " AND `OwnerID` NOT IN (" . implode(',', $aIds) . ")" : "";
        $rResult = getResult("SELECT DISTINCT(`Room`) FROM `" . MODULE_DB_PREFIX . "RoomsUsers`");
        $aFullRooms = array();
		$iCount = $rResult->rowCount();
        for($i=0; $i<$iCount; $i++) {
            $aFullRoom = $rResult->fetch();
            $aFullRooms[] = $aFullRoom['Room'];
        }
        $sFullRooms = count($aFullRooms)>0 ? "`ID` NOT IN(" . implode(',', $aFullRooms) . ") AND " : "";
        getResult("UPDATE `" . MODULE_DB_PREFIX . "Rooms` SET `Status`='" . ROOM_STATUS_DELETE . "', `When`=" . $iCurrentTime . " WHERE " . $sFullRooms . "`OwnerID`<>0" . $sIds);
    }

    //--- delete old messages ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `Type`='text' AND `When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");
    //--- Get information about users in the chat ---//
    switch($sMode) {
        case 'update':
            $rRes = getResult("SELECT ccu.`ID` AS `ID`, ccu.`Nick` AS `Nick`, ccu.`Sex` AS `Sex`, ccu.`Age` AS `Age`, ccu.`Desc` AS `Desc`, ccu.`Photo` AS `Photo`, ccu.`Profile` AS `Profile`, ccu.`Status` AS `Status`, ccu.`Online` AS `Online`, rp.`Type` AS `Type` FROM `" . MODULE_DB_PREFIX . "Profiles` AS rp, `" . MODULE_DB_PREFIX . "CurrentUsers` AS ccu WHERE rp.`ID`=ccu.`ID` ORDER BY ccu.`When`");
            while($aUser = $rRes->fetch()) {
                if($aUser['ID'] == $sId && !($aUser['Status'] == USER_STATUS_KICK || $aUser['Status'] == USER_STATUS_TYPE)) continue;
                switch($aUser['Status']) {
                    case USER_STATUS_NEW:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status'], $aUser['Nick'], $aUser['Sex'], $aUser['Age'], stripslashes($aUser['Desc']), $aUser['Photo'], $aUser['Profile'], $aUser['Type'], $aUser['Online']);
                        break;
                    case USER_STATUS_TYPE:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status'], $aUser['Type']);
                        break;
                    case USER_STATUS_ONLINE:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status'], $aUser['Type'], $aUser['Online']);
                        break;
                    case USER_STATUS_IDLE:
                    case USER_STATUS_KICK:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status']);
                        break;
                }
            }
            break;

        case 'all':
            $iRunTime = isset($_REQUEST['_t']) ? floor($_REQUEST['_t']/1000) : 0;
            $iCurrentTime -= $iRunTime;
            $rRes = getResult("SELECT ccu.`ID` AS `ID`, ccu.`Nick` AS `Nick`, ccu.`Sex` AS `Sex`, ccu.`Age` AS `Age`, ccu.`Desc` AS `Desc`, ccu.`Photo` AS `Photo`, ccu.`Profile` AS `Profile`, ccu.`Online` AS `Online`, rp.`Type` AS `Type`, (" . $iCurrentTime . "-`ccu`.`Start`) AS `Time` FROM `" . MODULE_DB_PREFIX . "Profiles` AS rp, `" . MODULE_DB_PREFIX . "CurrentUsers` AS ccu WHERE rp.`ID`=ccu.`ID` AND ccu.`Status` NOT IN ('" . USER_STATUS_IDLE . "', '" . USER_STATUS_KICK . "') AND rp.`Banned`='" . FALSE_VAL . "' ORDER BY ccu.`When`");
            while($aUser = $rRes->fetch())
                $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], USER_STATUS_NEW, $aUser['Nick'], $aUser['Sex'], $aUser['Age'], stripslashes($aUser['Desc']), $aUser['Photo'], $aUser['Profile'], $aUser['Type'], $aUser['Online'], $aUser['Time']);
            break;
    }
    return makeGroup($sContent, "users");
}

function removeFile($sFileId)
{
    global $sFilesPath;
    @getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `ID`='" . $sFileId . "'");
    @unlink($sFilesPath . $sFileId . ".file");
}
